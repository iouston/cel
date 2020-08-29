<?php
/* Copyright (C) 2012      Mikael Carlavan        <mcarlavan@qis-network.com>
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

/**
 *     	\file       htdocs/public/cel/cel.php
 *		\ingroup    cel
 *		\brief      File to offer a validation form for a propal
 */

define("NOLOGIN",1);		// This means this output page does not require to be logged.
define("NOCSRFCHECK",1);	// We accept to go on this page from external web site.

$res=@include("../main.inc.php");					// For root directory
if (! $res) $res=@include("../../main.inc.php");	// For "custom" directory

require_once(DOL_DOCUMENT_ROOT."/core/lib/company.lib.php");
require_once(DOL_DOCUMENT_ROOT."/core/lib/security.lib.php");
require_once(DOL_DOCUMENT_ROOT."/core/lib/date.lib.php");
require_once(DOL_DOCUMENT_ROOT."/core/lib/functions.lib.php");
require_once(DOL_DOCUMENT_ROOT."/core/lib/functions2.lib.php");
require_once(DOL_DOCUMENT_ROOT."/core/lib/files.lib.php");
require_once(DOL_DOCUMENT_ROOT.'/core/class/CMailFile.class.php');

require_once(DOL_DOCUMENT_ROOT."/compta/facture/class/facture.class.php");
require_once(DOL_DOCUMENT_ROOT."/commande/class/commande.class.php");

dol_include_once('/cel/class/cel.class.php');

// Security check
if (empty($conf->cel->enabled)) 
    accessforbidden('',1,1,1);

$langs->load("main");
$langs->load("other");
$langs->load("dict");
$langs->load("bills");
$langs->load("companies");
$langs->load("errors");
$langs->load("cel@cel");

$key    = GETPOST("key", 'alpha');
$action    = GETPOST("action", 'alpha');

$error = false;
$message = false;


$cel = new CEL($db);
$result = $cel->fetch('', $key);

if ($result <= 0)
{
	$error = true;
	$message = $langs->trans('NoValidationObject', $conf->global->MAIN_INFO_SOCIETE_MAIL);
}
else
{
	$item = new Propal($db);

	$result = $item->fetch($cel->fk_object);
	$result = $item->fetch_thirdparty($item->socid);
}

if ($item->statut != 1)
{
	$error = true;
	$message = $langs->trans('PropalDraftObject', $conf->global->MAIN_INFO_SOCIETE_MAIL);
}

// Get societe info
$societyName = $mysoc->name;
$creditorName = $societyName;

$currency = $conf->currency;
	
// Define logo and logosmall
$urlLogo = '';
if (!empty($mysoc->logo_small) && is_readable($conf->mycompany->dir_output.'/logos/thumbs/'.$mysoc->logo_small))
{
	$urlLogo = DOL_URL_ROOT.'/viewimage.php?modulepart=companylogo&amp;file='.urlencode('thumbs/'.$mysoc->logo_small);
}
elseif (! empty($mysoc->logo) && is_readable($conf->mycompany->dir_output.'/logos/'.$mysoc->logo))
{
	$urlLogo = DOL_URL_ROOT.'/viewimage.php?modulepart=companylogo&amp;file='.urlencode($mysoc->logo);
}
				
if (!$error)
{		
	if ($action == 'valid')
	{   
		// For foreign key
		$user->id = 1;
		$item->cloture($user, 2, $item->note);
		$user->id = 0;

		/*
		 * View
		*/
		$substit = array(
			'__OBJREF__' => $item->ref,
			'__AMOOBJ__' => price($item->total_ttc),
			'__SOCNAM__' => $conf->global->MAIN_INFO_SOCIETE_NOM,
			'__SOCMAI__' => $conf->global->MAIN_INFO_SOCIETE_MAIL,
			'__CLINAM__' => $item->thirdparty->name,                
		);
		
		$sendto = $item->thirdparty->email;                        

		$from = $conf->global->MAIN_INFO_SOCIETE_MAIL;
		
		$message = $langs->transnoentities('CELEmailBodyValidation');
		$subject = $langs->transnoentities('CELEmailSubjectValidation');
		
		$subject = make_substitutions($subject, $substit);           
		$message = make_substitutions($message, $substit);
		
		$message = str_replace('\n',"<br />", $message);
		
		$deliveryreceipt = $conf->global->CEL_DELIVERY_RECEIPT_EMAIL;
		$addr_cc = ($conf->global->CEL_CC_EMAIL ? $conf->global->MAIN_INFO_SOCIETE_MAIL: "");

		if (!empty($conf->global->CEL_CC_EMAILS)){
			$addr_cc.= (empty($addr_cc) ? $conf->global->CEL_CC_EMAILS : ','.$conf->global->CEL_CC_EMAILS);
		}

		$mail = new CMailFile($subject, $sendto, $from, $message, array(), array(), array(), $addr_cc, "", $deliveryreceipt, 1);
		$result = $mail->error;
		
		$result = $mail->sendfile();
		if ($result)
		{
			$message = $langs->trans('PropalValidated');
		}
		else
		{
			$message = $langs->trans('PropalValidatedError');
		}
		
		require_once('tpl/message.tpl.php');
    }
    elseif ($action == 'cancel')
	{   
		// For foreign key
		$user->id = 1;
		$item->cloture($user, 3, $item->note);
		$user->id = 0;

		/*
		 * View
		*/
		$substit = array(
			'__OBJREF__' => $item->ref,
			'__AMOOBJ__' => price($item->total_ttc),
			'__SOCNAM__' => $conf->global->MAIN_INFO_SOCIETE_NOM,
			'__SOCMAI__' => $conf->global->MAIN_INFO_SOCIETE_MAIL,
			'__CLINAM__' => $item->thirdparty->name,                
		);
		
		$sendto = $item->thirdparty->email;                        

		$from = $conf->global->MAIN_INFO_SOCIETE_MAIL;
		
		$message = $langs->transnoentities('CELEmailBodyRejection');
		$subject = $langs->transnoentities('CELEmailSubjectRejection');
		
		$subject = make_substitutions($subject, $substit);           
		$message = make_substitutions($message, $substit);
		
		$message = str_replace('\n',"<br />", $message);
		
		$deliveryreceipt = $conf->global->CEL_DELIVERY_RECEIPT_EMAIL;
		$addr_cc = ($conf->global->CEL_CC_EMAIL ? $conf->global->MAIN_INFO_SOCIETE_MAIL: "");

		if (!empty($conf->global->CEL_CC_EMAILS)){
			$addr_cc.= (empty($addr_cc) ? $conf->global->CEL_CC_EMAILS : ','.$conf->global->CEL_CC_EMAILS);
		}

		$mail = new CMailFile($subject, $sendto, $from, $message, array(), array(), array(), $addr_cc, "", $deliveryreceipt, 1);
		$result = $mail->error;
		
		$result = $mail->sendfile();
		if ($result)
		{
			$message = $langs->trans('PropalRefused', $conf->global->MAIN_INFO_SOCIETE_MAIL);
		}
		else
		{
			$message = $langs->trans('PropalRefusedError', $conf->global->MAIN_INFO_SOCIETE_MAIL);
		}
		
		require_once('tpl/message.tpl.php');
    }  
    else
    {		
		$customerEmail = $item->thirdparty->email;
		$customerName = $item->thirdparty->name;     
		$customerId = $item->thirdparty->id;
		$customerAddress = $item->thirdparty->address;
		$customerZip = $item->thirdparty->zip;
		$customerCity = $item->thirdparty->town;
		$customerCountry = $item->thirdparty->country_code;
		$customerPhone = $item->thirdparty->phone;

	
		$cgv = dol_buildpath('/cel/cgv.php?attachment=0', 2);
		$propal = dol_buildpath('/cel/propal.php?id='.$item->id.'&attachment=0', 2);
		
		/*
		 * View
		 */
	 
		require_once('tpl/cel.tpl.php');      
    }   
}else{
    
    /*
     * View
	*/    
    require_once('tpl/message.tpl.php');    
}

$db->close();

?>