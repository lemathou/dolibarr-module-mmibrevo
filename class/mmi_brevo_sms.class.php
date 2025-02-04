<?php

require_once DOL_DOCUMENT_ROOT . '/comm/action/class/actioncomm.class.php';

dol_include_once('/mmicommon/class/mmi_common.class.php');

class mmi_brevo_sms
{
	public $db;

	public static $api_url;
	public static $api_key;
	public static $organisationPrefixShow = false;
	public static $organisationPrefix;

	public $socid;
	public $member_id;
	public $contact_id;
	public $fk_adherent; // @deprecated ?

	public $fk_element;
	public $elementtype;

	public $actiontypecode = 'AC_SMS';

	public $type = "transactional";
	public $unicodeEnabled = false;
	public $expe; //$sender;
	public $dest; //$recipient;
	public $message; //$content;

	public $result;
	public $error;
	public $errors;

	public static function __init()
	{
		static::$api_url = getDolGlobalString('MMI_BREVO_API_URL');
		static::$api_key = getDolGlobalString('MMI_BREVO_API_KEY');
		static::$organisationPrefixShow = getDolGlobalInt('MMI_BREVO_API_ORGANISATION_PREFIX_SHOW');
		static::$organisationPrefix = getDolGlobalString('MMI_BREVO_API_ORGANISATION_PREFIX');
	}

	public function __construct($db2)
	{
		global $db;

		$this->db = $db;

		$this->expe = getDolGlobalString('MMI_BREVO_API_FROM');
	}

	public function send($user, $infos=[])
	{
		//var_dump($this); die();
		if (!empty($user->array_options['options_email_sender_name']))
			$this->organisationPrefix = $user->array_options['options_email_sender_name'];

		$this->dest = mmi_common::tel_international($this->dest);
		
		$data = [
			"type"=>$this->type,
			"unicodeEnabled"=>$this->unicodeEnabled,
			"sender"=>$this->expe,
			"recipient"=>$this->dest,
			"content"=>$this->message,
			"organisationPrefix"=>static::$organisationPrefixShow ?static::$organisationPrefix :'',
		];
		//var_dump($data);
		$payload = json_encode($data);
	
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: tapplication/json'));
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('api-key: '.static::$api_key));
		curl_setopt($ch, CURLOPT_URL, static::$api_url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result_raw = curl_exec($ch);
		curl_close($ch);

		$this->result = is_string($result_raw) && !empty($result_raw) ?json_decode($result_raw, true) :[];
		//var_dump($result_raw, $this->result);

		if (! is_array($this->result) || empty($this->result)) {
			if (is_string($result_raw)) {
				$this->error = $result_raw;
				$this->errors = $result_raw;
			}
			else {
				$this->error = 'Unknown error';
				$this->errors = 'Unknown error';
			}

			return 0;
		}
		elseif (empty($this->result['messageId'])) {
			if (!empty($this->result['message'])) {
				$this->error = $this->result['message'];
				$this->errors = $this->result['message'];
			}
			elseif (!empty($this->result['code'])) {
				$this->error = $this->result['code'];
				$this->errors = $this->result['code'];
			}
			
			return 0;
		}
		else {
			global $conf;
			if (! isset($conf->global->MAIN_AGENDA_ACTIONAUTO_SENTBYSMS)) {
				$conf->global->MAIN_AGENDA_ACTIONAUTO_SENTBYSMS = 1;	// Make trigger on
			}

			if (getDolGlobalInt('MAIN_AGENDA_ACTIONAUTO_SENTBYSMS')) {
				$this->notification($user, $infos);
			}

			return $this->result['messageId'];
		}
	}

	public function SMSSend()
	{
		global $user;

		return $this->send($user);
	}

	public function notification($user, $infos=[])
	{
		global $langs, $conf;

		// Initialisation of datas of object to call trigger
		$langs->load("agenda");
		$langs->load("other");
		
		// Insert ActionComm
		// Used code from ovh module, added $object->fk_element part
		$now = dol_now();
		$actioncomm = new ActionComm($this->db);

		$actioncomm->elementtype = 'sms@mmibrevo';
		$actioncomm->code = 'AC_SMS';
		$actioncomm->type_code = $this->actiontypecode;
		$actioncomm->label = $langs->trans("SMSSentTo", $this->dest);
		$actioncomm->note_private = $langs->trans("SMSSentTo", $this->dest)."<br />\n".$this->message;
		$actioncomm->datep = $now;
		$actioncomm->socid = $this->socid;
		$actioncomm->contact_id = $this->contact_id;
		$actioncomm->userownerid = $user->id;
		$actioncomm->percentage = -1;
		if (!empty($this->fk_element)) {
			$actioncomm->fk_element		= $this->fk_element;
			$actioncomm->elementtype	= $this->elementtype;
		}

		$actioncomm->create($user);

		return 1;
	}


	/**
	 * Return list of possible SMS senders
	 *
	 * @return array|int	                    <0 if KO, array with list of available senders if OK
	 */
	function SmsSenderList()
	{
		global $conf;

		$senderlist=array();
			$senderlist[0] = new stdClass();
			$senderlist[0]->number = 'MMI';
		return $senderlist;
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

mmi_brevo_sms::__init();
