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
		$sms->dest = $to;
		$sms->message = $message;
		if(isset($infos['type'])) $sms->type = $infos['type'];
		if(isset($infos['unicodeEnabled'])) $sms->unicodeEnabled = $infos['unicodeEnabled'];
		if(isset($infos['sender'])) $sms->expe = $infos['sender'];
		if(isset($infos['organisationPrefix'])) $sms->organisationPrefix = $infos['organisationPrefix'];
		if(isset($infos['socid'])) $sms->socid = $infos['socid'];
		if(isset($infos['elementtype'])) $sms->elementtype = $infos['elementtype'];
		if(isset($infos['fk_element'])) $sms->fk_element = $infos['fk_element'];

		$result = $sms->send($user, $infos);
		
		return $result;
	}
}

mmi_brevo::__init();
