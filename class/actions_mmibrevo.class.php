<?php
/* Copyright (C) 2025 SuperAdmin <webmaster@dercya.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    mmibrevo/class/actions_mmibrevo.class.php
 * \ingroup mmibrevo
 * \brief   Example hook overload.
 *
 * Put detailed description here.
 */

require_once DOL_DOCUMENT_ROOT.'/core/class/commonhookactions.class.php';

/**
 * Class ActionsMMIBrevo
 */
class ActionsMMIBrevo extends CommonHookActions
{
	/**
	 * @var DoliDB Database handler.
	 */
	public $db;

	/**
	 * @var string Error code (or message)
	 */
	public $error = '';

	/**
	 * @var array Errors
	 */
	public $errors = array();


	/**
	 * @var array Hook results. Propagated to $hookmanager->resArray for later reuse
	 */
	public $results = array();

	/**
	 * @var string String displayed by executeHook() immediately after return
	 */
	public $resprints;

	/**
	 * @var int		Priority of hook (50 is used if value is not defined)
	 */
	public $priority;


	/**
	 * Constructor
	 *
	 *  @param		DoliDB		$db      Database handler
	 */
	public function __construct($db)
	{
		$this->db = $db;
	}


	/**
	 * Overloading the addMoreActionsButtons function : replacing the parent's function with the one below
	 *
	 * @param array $parameters Hook metadatas (context, etc...)
	 * @param CommonObject $object The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param string $action Current action (if set). Generally create or edit or null
	 * @param HookManager $hookmanager Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function addMoreActionsButtons($parameters, &$object, &$action, $hookmanager)
	{

		global $user, $langs;

		$currentcontext = explode(':', $parameters['context']);
		if ($user->hasRight('mmibrevo','send')) {
			$langs->load("sms");
			if (in_array('propalcard', $currentcontext)) {
				print dolGetButtonAction('', $langs->trans('SendSms'), 'default', dol_buildpath('mmibrevo/sms_propal.php', 2) . '?id=' . $object->id);
			}
			if (in_array('ordercard', $currentcontext)) {
				print dolGetButtonAction('', $langs->trans('SendSms'), 'default', dol_buildpath('mmibrevo/sms_order.php', 2) . '?id=' . $object->id);
			}
			if (in_array('invoicecard', $currentcontext)) {
				print dolGetButtonAction('', $langs->trans('SendSms'), 'default', dol_buildpath('mmibrevo/sms_invoice.php', 2) . '?id=' . $object->id);
			}
		}
		return 0;
	}

	/* Add here any other hooked methods... */
}
