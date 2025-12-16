<?php

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formmail.class.php';

print_fiche_titre($langs->trans("Sms"), '', 'phone.png@mmibrevo');

$idmodel = GETPOSTINT('idmodel');
$formmail = new FormMail($db);
if ($object->element == 'invoice') {
	$elementtype = 'invoice';
} elseif ($object->element == 'propal') {
	$elementtype = 'propal';
} elseif ($object->element == 'commande') {
	$elementtype = 'order';
} else {
	$elementtype = $object->element;
}
//var_dump($object->element, $elementtype);
$result = $formmail->fetchAllEMailTemplate($elementtype.'_send', $user, $langs);
//var_dump($formmail->lines_model); die();

echo "<form method=\"GET\" name=\"smsform_model\" action=\"".$_SERVER["PHP_SELF"]."\">\n";
echo '<input name="id" value="'.$object->id.'" type="hidden" />';
echo '<table>';
echo '<tr><td class="titlefield">'.$langs->trans("SelectMailModel");
echo "</td><td>";
echo '<select name="idmodel"><option value="">-- Choisir --</option>';
$model_body = '';
foreach ($formmail->lines_model as $line) {
	if ($line->topic != 'SMS')
		continue;
	echo'<option value="' . $line->id . '"'.($line->id==$idmodel ?' selected' :'').'>' . $line->label . '</option>';
	if ($line->id==$idmodel) {
		$model_body = strip_tags($line->content);
	}
}
var_dump($model_body);
echo '</select>';
echo '<input type="submit" class="button reposition smallpaddingimp" value="Appliquer" name="modelselected" id="modelselected" onclick="return if ($(\'idmodel\').val());" />';
echo "</td><tr>";
echo '<tr><td colspan="2"><span style="color: gray;">'.$langs->trans("InfoSMSModel").'</span></td></tr>';
echo '</table>';
echo '</form>';

print "<form method=\"POST\" name=\"smsform\" enctype=\"multipart/form-data\" action=\"".$_SERVER["PHP_SELF"].'?id='.$object->id."\">\n";
if ((float) DOL_VERSION >= 11.0) {
	print '<input type="hidden" name="token" value="'.newToken().'">';
} else {
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
}
if ($idmodel) {
	print '<input type="hidden" name="idmodel" value="'.$idmodel.'" />';
}

// Cree l'objet formulaire mail
include_once DOL_DOCUMENT_ROOT.'/core/class/html.formsms.class.php';
$formsms = new FormSms($db);
$formsms->fromtype = 'user';
$formsms->fromid   = $user->id;
$formsms->fromname = $user->getFullName($langs);
$formsms->fromsms = $user->user_mobile;
$formsms->param['objecttype'] = $object->element;
$formsms->param['objectid'] = $object->id;
$formsms->param['models'] = 'body';
$formsms->withsubstit=1;
$formsms->withfrom=1;
$formsms->withtosocid=$socid;
$formsms->withfromreadonly=0;
$formsms->withto=empty($_POST["sendto"])?1:$_POST["sendto"];
$formsms->withbody=$model_body;
$formsms->withcancel=0;
// Tableau des substitutions
$arrayoffamiliestoexclude = NULL;
$formsms->substit = getCommonSubstitutionArray($langs, 0, $arrayoffamiliestoexclude, $object);
// Tableau des parametres complementaires du post
$formsms->param['action']='send';
$formsms->param['id']=$thirdparty->id;
$formsms->param['returnurl']=$_SERVER["PHP_SELF"].'?id='.$object->id;

$formsms->show_form('', 0);

dol_fiche_end();

print '<div class="center">';
print '<input class="button" type="submit" name="sendmail" value="'.dol_escape_htmltag($langs->trans("SendSms")).'">';
if ($formsms->withcancel) {
	print '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
	print '<input class="button" type="submit" name="cancel" value="'.dol_escape_htmltag($langs->trans("Cancel")).'">';
}
print '</div>';

print "</form>\n";