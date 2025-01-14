<?php

// uncomment for test !
die();

// Load Dolibarr environment
require_once 'env.inc.php';
require_once 'main_load.inc.php';

dol_include_once('/mmibrevo/class/mmi_brevo.class.php');


$to = '';
$message = '';
$infos = [];

$brevo = new mmi_brevo($db);
$brevo->sms_send($user, $to, $message, ['socid' => 0, 'elementtype' => 'commande', 'fk_element' => 0]);
