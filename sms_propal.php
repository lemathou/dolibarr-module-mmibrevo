<?php

require_once 'env.inc.php';
require_once 'main_load.inc.php';

require __DIR__ . '/class/mmi_brevo.class.php';

require_once DOL_DOCUMENT_ROOT.'/comm/propal/class/propal.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/propal.lib.php';
if (isModEnabled('project')) {
	require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';
}

// Load translation files required by the page
$langs->loadLangs(array('propal', 'compta', 'bills', 'companies'));
$langs->load("sms");

$id = GETPOST('id', 'int');
$ref = GETPOST('ref', 'alpha');
$action = GETPOST('action', 'aZ09');

$now = dol_now();

$object = new Propal($db);
if ($id > 0 || !empty($ref)) {
	$object->fetch($id, $ref);
}

if ($object->id > 0) {
	$object->fetch_thirdparty();
	$thirdparty = $object->thirdparty;
	if (! empty($thirdparty->id)) {
		$socid = $thirdparty->id;
	}
	else {
		accessforbidden();
	}
}
else {
	accessforbidden();
}

// Protection if external user
if ($user->socid > 0) {
	accessforbidden();
}

// Initialize technical object to manage hooks of page. Note that conf->hooks_modules contains array of hook context
$hookmanager->initHooks(array('propalsms','sms'));

restrictedArea($user, 'propal', $object->id, 'propal');

$usercancreate = $user->hasRight("propal", "creer");



/*******************************************************************
 * ACTIONS
 ********************************************************************/

/* Envoi d'un SMS */
if ($action == 'send' && ! $_POST['cancel']) {
	$error=0;

	$smsfrom='';
	if (! empty($_POST["fromsms"])) $smsfrom=GETPOST("fromsms");
	if (empty($smsfrom)) $smsfrom=GETPOST("fromname");
	$sendto     = GETPOST("sendto");
	$receiver   = GETPOST('receiver');
	$body       = GETPOST('message');
	$deliveryreceipt= GETPOST("deliveryreceipt");
	$deferred   = GETPOST('deferred');
	$priority   = GETPOST('priority');
	$class      = GETPOST('class');
	$errors_to  = GETPOST("errorstosms");

	if ($receiver == 'thirdparty') $sendto=$thirdparty->phone;
	if ((empty($sendto) || ! str_replace('+', '', $sendto)) && (! empty($receiver) && $receiver != '-1')) {
		$sendto=$thirdparty->contact_get_property($receiver, 'mobile');
	}

	// Test param
	if (empty($body)) {
		setEventMessage($langs->trans("ErrorFieldRequired", $langs->transnoentities("Message")), 'errors');
		$action='test';
		$error++;
	}
	if (empty($smsfrom) || ! str_replace('+', '', $smsfrom)) {
		setEventMessage($langs->trans("ErrorFieldRequired", $langs->transnoentities("SmsFrom")), 'errors');
		$action='test';
		$error++;
	}
	if ((empty($sendto) || ! str_replace('+', '', $sendto)) && (empty($receiver) || $receiver == '-1')) {
		setEventMessage($langs->trans("ErrorFieldRequired", $langs->transnoentities("SmsTo")), 'errors');
		$action='test';
		$error++;
	}

	if (! $error) {
		// Make substitutions into message
		$substitutionarrayfortest=array();
		complete_substitutions_array($substitutionarrayfortest, $langs);
		$body=make_substitutions($body, $substitutionarrayfortest);

		$brevo = new mmi_brevo($db);
		$result = $brevo->sms_send($user, $sendto, $body, ['socid'=>$socid, 'fk_element'=>$object->id, 'elementtype'=>'propal']);

		if ($result > 0) {
			setEventMessages($langs->trans("SmsSuccessfulySent", $smsfrom, $sendto), null);
		} else {
			setEventMessages($langs->trans("ResultKo").' (sms from'.$smsfrom.' to '.$sendto.')<br>'.$smsfile->error, null, 'errors');
		}

		$action='';
	}
}
 
/*
 * View
 */

$form = new Form($db);

$title = $object->ref." - ".$langs->trans('SMS');
$help_url = 'EN:Commercial_Proposals|FR:Proposition_commerciale|ES:Presupuestos';

llxHeader('', $title, $help_url);

$head = propal_prepare_head($object);
print dol_get_fiche_head($head, 'tabMMIBrevoSMS', $langs->trans('Proposal'), -1, 'propal');

$cssclass = 'titlefield';
//if ($action == 'editnote_public') $cssclass='titlefieldcreate';
//if ($action == 'editnote_private') $cssclass='titlefieldcreate';


// Proposal card

$linkback = '<a href="'.DOL_URL_ROOT.'/comm/propal/list.php?restore_lastsearch_values=1'.(!empty($socid) ? '&socid='.$socid : '').'">'.$langs->trans("BackToList").'</a>';


$morehtmlref = '<div class="refidno">';
// Ref customer
$morehtmlref .= $form->editfieldkey("RefCustomer", 'ref_client', $object->ref_client, $object, 0, 'string', '', 0, 1);
$morehtmlref .= $form->editfieldval("RefCustomer", 'ref_client', $object->ref_client, $object, 0, 'string', '', null, null, '', 1);
// Thirdparty
$morehtmlref .= '<br>'.$object->thirdparty->getNomUrl(1);
// Project
if (isModEnabled('project')) {
	$langs->load("projects");
	$morehtmlref .= '<br>';
	if (0) {
		$morehtmlref .= img_picto($langs->trans("Project"), 'project', 'class="pictofixedwidth"');
		if ($action != 'classify') {
			$morehtmlref .= '<a class="editfielda" href="'.$_SERVER['PHP_SELF'].'?action=classify&token='.newToken().'&id='.$object->id.'">'.img_edit($langs->transnoentitiesnoconv('SetProject')).'</a> ';
		}
		$morehtmlref .= $form->form_project($_SERVER['PHP_SELF'].'?id='.$object->id, $object->socid, $object->fk_project, ($action == 'classify' ? 'projectid' : 'none'), 0, 0, 0, 1, '', 'maxwidth300');
	} else {
		if (!empty($object->fk_project)) {
			$proj = new Project($db);
			$proj->fetch($object->fk_project);
			$morehtmlref .= $proj->getNomUrl(1);
			if ($proj->title) {
				$morehtmlref .= '<span class="opacitymedium"> - '.dol_escape_htmltag($proj->title).'</span>';
			}
		}
	}
}
$morehtmlref .= '</div>';

dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'ref', $morehtmlref);

print '<div class="fichecenter">';
print '<div class="underbanner clearboth"></div>';

require_once 'sms_form.inc.php';

print '</div>';

print dol_get_fiche_end();

// End of page
llxFooter();
$db->close();
