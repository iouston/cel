<?php
/* Copyright (C) 2012      Mikael Carlavan        <contact@mika-carl.fr>
 *                                                http://www.mikael-carlavan.fr
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */



$res=@include("../../main.inc.php");				// For root directory
if (! $res) $res=@include("../../../main.inc.php");	// For "custom" directory

require_once(DOL_DOCUMENT_ROOT."/core/lib/admin.lib.php");
require_once(DOL_DOCUMENT_ROOT."/core/class/html.form.class.php");
require_once(DOL_DOCUMENT_ROOT."/core/lib/functions2.lib.php");
require_once(DOL_DOCUMENT_ROOT."/core/lib/files.lib.php");
require_once(DOL_DOCUMENT_ROOT."/core/class/html.form.class.php");

$langs->load('cel@cel');
$langs->load('main');
$langs->load('admin');
$langs->load("other");
$langs->load("errors");


if (!$user->admin)
{
   accessforbidden();
}

//Init error
$error = false;
$message = false;


$security_token = $conf->global->CEL_SECURITY_TOKEN ? $conf->global->CEL_SECURITY_TOKEN : '';
$delivery_receipt_email = $conf->global->CEL_DELIVERY_RECEIPT_EMAIL ? $conf->global->CEL_DELIVERY_RECEIPT_EMAIL : 0;
$cc_email = $conf->global->CEL_CC_EMAIL ? $conf->global->CEL_CC_EMAIL : '';
$cc_emails = $conf->global->CEL_CC_EMAILS ? $conf->global->CEL_CC_EMAILS : '';
$update_propal_statut = $conf->global->CEL_UPDATE_PROPAL_STATUT ? $conf->global->CEL_UPDATE_PROPAL_STATUT : 0;
$tos_auto_send = $conf->global->CEL_TOS_AUTO_SEND ? $conf->global->CEL_TOS_AUTO_SEND : 0;

$action = GETPOST("action");


if ($action == 'update')
{
	$varfiles = 'file';
	
	// Upload dir
	$upload_dir = $conf->cel->dir_output;
	$result = 0;
	
	if (! empty($_FILES[$varfiles])) // For view $_FILES[$varfiles]['error']
	{
	
		$finame = $_FILES[$varfiles]['name'];
		$ext = substr($finame, (strrpos($finame, '.') + 1));
		$ext = strtolower($ext);
		
		if ($ext != 'pdf')
		{
			dol_syslog("CEL::config wrong extension", LOG_DEBUG);
			$error = true;
		}
		
		if (!$error)
		{					
			$res = dol_delete_dir_recursive($upload_dir); // Empty dir
			$res = dol_mkdir($upload_dir);
			if ($res >= 0)
			{									
				$resupload = dol_move_uploaded_file($_FILES[$varfiles]['tmp_name'], $upload_dir . "/" . $finame, 1, 0, $_FILES[$varfiles]['error'], 0, $varfiles);
			}
			else
			{
				dol_syslog("CEL::config create directory=".$res, LOG_DEBUG);
			}
		}
	}

}
else
{
	if ($action == 'updated')
	{
		$message = $langs->trans('CGVUploaded');
	}
	
	// Sauvegarde parametres
	if ($action == 'save')
	{
		$db->begin();
	

		$security_token = trim(GETPOST("security_token"));
		$tos_auto_send = trim(GETPOST("tos_auto_send"));
		$delivery_receipt_email = trim(GETPOST("delivery_receipt_email"));
		$cc_email = trim(GETPOST("cc_email"));
		$cc_emails = trim(GETPOST("cc_emails"));
		$update_propal_statut = trim(GETPOST("update_propal_statut"));
		

		dolibarr_set_const($db, 'CEL_SECURITY_TOKEN', $security_token, 'chaine', 0, '', $conf->entity);
		dolibarr_set_const($db, 'CEL_DELIVERY_RECEIPT_EMAIL', $delivery_receipt_email, 'chaine', 0, '', $conf->entity);
		dolibarr_set_const($db, 'CEL_CC_EMAIL', $cc_email, 'chaine', 0, '', $conf->entity);
		dolibarr_set_const($db, 'CEL_CC_EMAILS', $cc_emails, 'chaine', 0, '', $conf->entity);
		dolibarr_set_const($db, 'CEL_UPDATE_PROPAL_STATUT', $update_propal_statut, 'chaine', 0, '', $conf->entity);
		dolibarr_set_const($db, 'CEL_TOS_AUTO_SEND', $tos_auto_send, 'chaine', 0, '', $conf->entity);		
		
		$db->commit();
		
		$message = $langs->trans("SetupSaved");
		$error = false;
	}

	$upload_dir = $conf->cel->dir_output;
	$filearray = dol_dir_list($upload_dir, 'files', 0, '', '\.meta$', '', SORT_ASC,1);

	$file = array_pop($filearray);
	
	if (is_array($file))
	{
		$filename = $file['name'];
		$link = DOL_URL_ROOT."/document.php?modulepart=cel&file=".$file['name'];
		$size = (intval($file['size'])/(1024*1024));
	}
	else
	{
		$filename = '';
		$link = '';
		$size = 0;
	}
		
	$linkback = '<a href="'.DOL_URL_ROOT.'/admin/modules.php">'.$langs->trans("BackToModuleList").'</a>';

	$htmltooltips = array(
		'SecurityToken' => $langs->trans("SecurityTokenTooltip"),
		'DeliveryReceiptEmail' => $langs->trans("DeliveryReceiptEmailTooltip"), 
		'CcEmail' => $langs->trans("CcEmailTooltip"), 
		'CcEmails' => $langs->trans("CcEmailsTooltip"), 
		'UpdatePropalStatut' => $langs->trans("UpdatePropalStatutTooltip"), 
		'TosAutoSend' => $langs->trans("TosAutoSendTooltip"),
						
	);

	$form = new Form($db);

	require_once("../tpl/admin.config.tpl.php");

}



$db->close();

?>
