<?php

dol_include_once("/mmicommon/class/mmi_generic.class.php");
require_once __DIR__.'/mmi_brevo_sms.class.php';

class mmi_brevo extends MMI_Generic_1_0
{
	public function sms_send($user, $to, $message, $infos=[])
	{
		global $conf;

		$db = $this->db;

		$sms = new mmi_brevo_sms($db);
		$sms->recipient = $to;
		$sms->content = $message;
		if(isset($infos['type'])) $sms->type = $infos['type'];
		if(isset($infos['unicodeEnabled'])) $sms->unicodeEnabled = $infos['unicodeEnabled'];
		if(isset($infos['sender'])) $sms->sender = $infos['sender'];
		if(isset($infos['organisationPrefix'])) $sms->organisationPrefix = $infos['organisationPrefix'];
		if(isset($infos['socid'])) $sms->socid = $infos['socid'];

		$sent = $sms->send($user, $infos);
		$result = !empty($sent['smsCount']) ?1 :-1;

		return $result;
	}
}

mmi_brevo::__init();
