<?php

dol_include_once('/mmicommon/class/mmi_common.class.php');

class mmi_brevo_sms
{
	public $db;

	public $api_url;
	public $api_key;

	public $socid;
	public $member_id;
	public $contact_id;
	public $fk_adherent; // @deprecated ?

	public $fk_element;
	public $elementtype;

	public $actiontypecode = 'AC_SMS';

	public $type = "transactional";
	public $unicodeEnabled = false;
	public $sender;
	public $recipient;
	public $content;
	public $organisationPrefixShow = false;
	public $organisationPrefix;

	public function __construct($db)
	{
		$this->db = $db;

		$this->api_url = getDolGlobalString('MMI_BREVO_API_URL');
		$this->api_key = getDolGlobalString('MMI_BREVO_API_KEY');
		$this->sender = getDolGlobalString('MMI_BREVO_API_FROM');
		$this->organisationPrefixShow = getDolGlobalInt('MMI_BREVO_API_ORGANISATION_PREFIX_SHOW');
		$this->organisationPrefix = getDolGlobalString('MMI_BREVO_API_ORGANISATION_PREFIX');
	}

	public function send($user, $infos=[])
	{
		if (!empty($user->array_options['options_email_sender_name']))
			$this->organisationPrefix = $user->array_options['options_email_sender_name'];

		$this->recipient = mmi_common::tel_international($this->recipient);
		
		$data = [
			"type"=>$this->type,
			"unicodeEnabled"=>$this->unicodeEnabled,
			"sender"=>$this->sender,
			"recipient"=>$this->recipient,
			"content"=>$this->content,
			"organisationPrefix"=>$this->organisationPrefixShow ?$this->organisationPrefix :'',
		];
		//var_dump($data);
		$payload = json_encode($data);
	
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: tapplication/json'));
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('api-key: '.$this->api_key));
		curl_setopt($ch, CURLOPT_URL, $this->api_url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		curl_close($ch);

		$sent = is_string($result) && !empty($result) ?json_decode($result, true) :[];

		//var_dump($result);
		if (!empty($sent['smsCount'])) {
			global $conf;
			if (! isset($conf->global->MAIN_AGENDA_ACTIONAUTO_SENTBYSMS)) {
				$conf->global->MAIN_AGENDA_ACTIONAUTO_SENTBYSMS = 1;	// Make trigger on
			}

			if (getDolGlobalInt('MAIN_AGENDA_ACTIONAUTO_SENTBYSMS')) {
				$this->notification($user, $infos);
			}
		}
	
		return $sent;
	}


	public function notification($user, $infos=[])
	{
		global $langs, $conf;

		$object = new stdClass();
		$trigger_name = 'SENTBYSMS';
		if ($this->member_id > 0) {
			$trigger_name = 'MEMBER_SENTBYSMS';
			//$conf->global->MAIN_AGENDA_ACTIONAUTO_MEMBER_SENTBYSMS should be set from agenda setup
		} elseif ($this->socid > 0) {
			$trigger_name = 'COMPANY_SENTBYSMS';
			//$conf->global->MAIN_AGENDA_ACTIONAUTO_COMPANY_SENTBYSMS should be set from agenda setup
		}

		// Force automatic event to ON for the generic trigger name

		// Initialisation of datas of object to call trigger
		if (is_object($object)) {
			$langs->load("agenda");
			$langs->load("other");

			$actiontypecode= !empty($infos['actiontypecode']) ?:'AC_SMS'; // Event insert into agenda automatically

			$object->socid			= $this->socid;	   		// To link to a company
			$object->contact_id     = $this->contact_id;
			$object->fk_adherent    = $this->member_id;
			//$object->sendtoid		= $sendtoid;	   // To link to contacts/addresses. This is an array.

			$object->actiontypecode	= $actiontypecode; // Type of event ('AC_OTH', 'AC_OTH_AUTO', 'AC_XXX'...)
			$object->actionmsg2		= $langs->trans("SMSSentTo", $this->recipient);
			if (getDolGlobalInt('MMICRM_SMS_CLEAN_UNCIODE'))
				$object->actionmsg		= $langs->trans("SMSSentTo", $this->recipient)."\n".mb_convert_encoding($this->content, 'UTF-8');
			else
				$object->actionmsg		= $langs->trans("SMSSentTo", $this->recipient)."\n".$this->content;
			//$object->trackid        = $trackid;
			if (!empty($this->fk_element)) {
				$object->fk_element		= $this->fk_element;
				$object->elementtype	= $this->elementtype;
			}
			//$object->attachedfiles	= null;

			// Call of triggers
			if (! empty($trigger_name)) {
				include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
				$interface=new Interfaces($this->db);
				$result=$interface->run_triggers($trigger_name, $object, $user, $langs, $conf);
				if ($result < 0) {
					setEventMessages($interface->error, $interface->errors, 'errors');
				}
			}
		}

		return 1;
	}

	/**
	 * Return Credit
	 *
	 * @return	array
	 */
	function CreditLeft()
	{
	}


	/**
	 * Return History
	 *
	 * @return	array
	 */
	function SmsHistory()
	{
	}

	
}