<?php
/* Copyright (C) 2012      Mikael Carlavan        <contact@mika-carl.fr>
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

require_once(DOL_DOCUMENT_ROOT.'/core/class/CMailFile.class.php');
require_once(DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php');
require_once(DOL_DOCUMENT_ROOT."/core/lib/functions.lib.php");
require_once(DOL_DOCUMENT_ROOT."/compta/facture/class/facture.class.php");
require_once(DOL_DOCUMENT_ROOT."/commande/class/commande.class.php");

dol_include_once('/cel/class/cel.class.php');
/**
 *      \class      InterfaceCGVEnLigneWorkflow
 *      \brief      Class of triggers for cgvenligne module
 */
class InterfaceCELWorkflow
{
    var $db;

    /**
     *   Constructor
     *   @param      DB      Database handler
     */
    function InterfaceCELWorkflow($DB)
    {
        $this->db = $DB;

        $this->name = preg_replace('/^Interface/i','',get_class($this));
        $this->family = "cel";
        $this->description = "Triggers of this module allows to manage cgvenligne workflow";
        $this->version = 'dolibarr';            // 'development', 'experimental', 'dolibarr' or version
        $this->picto = 'cel@cel';
    }


    /**
     *   \brief      Renvoi nom du lot de triggers
     *   \return     string      Nom du lot de triggers
     */
    function getName()
    {
        return $this->name;
    }

    /**
     *   \brief      Renvoi descriptif du lot de triggers
     *   \return     string      Descriptif du lot de triggers
     */
    function getDesc()
    {
        return $this->description;
    }

    /**
     *   \brief      Renvoi version du lot de triggers
     *   \return     string      Version du lot de triggers
     */
    function getVersion()
    {
        global $langs;
        $langs->load("admin");

        if ($this->version == 'development') return $langs->trans("Development");
        elseif ($this->version == 'experimental') return $langs->trans("Experimental");
        elseif ($this->version == 'dolibarr') return DOL_VERSION;
        elseif ($this->version) return $this->version;
        else return $langs->trans("Unknown");
    }

    /**
     *      \brief      Fonction appelee lors du declenchement d'un evenement Dolibarr.
     *                  D'autres fonctions run_trigger peuvent etre presentes dans includes/triggers
     *      \param      action      Code de l'evenement
     *      \param      object      Objet concerne
     *      \param      user        Objet user
     *      \param      lang        Objet lang
     *      \param      conf        Objet conf
     *      \return     int         <0 if fatal error, 0 si nothing done, >0 if ok
     */
	function run_trigger($action, $object, $user, $langs, $conf)
    {
	    $triggered = ($action == 'PROPAL_SENTBYMAIL' || $action == 'PROPAL_VALIDATE') ? true : false;
        
        if ($triggered)
        {
            $langs->load("cel@cel");
        	dol_syslog("CEL: Trigger '".$this->name."' for action '".$action."' launched by ".__FILE__." ref=".$object->ref);

            $ref = $object->ref;
            
            
            $item = new Propal($this->db);
            
            $result = $item->fetch($object->id);
            if ($result < 0)
            {
                dol_syslog('CEL: Propal with specified reference does not exist, email containing terms of sale link has not been sent');
            	return $result;
            }
            else
            {
            	$result = $item->fetch_thirdparty();
            } 

            
            // Create URL
            $token = $conf->global->CEL_SECURITY_TOKEN ? $conf->global->CEL_SECURITY_TOKEN : '';
            $now = dol_now();
            $token = dol_hash($token.$ref.$now, 3); // MD5
            
            $cel = new CEL($this->db);
            $result = $cel->fetch('', $token);
            if ($result == 0)
            {
            	$cel->ref = $ref;
				$cel->key = $token;
				$cel->fk_object = $item->id;
				$cel->create($user);
            }
            else
            {
                $token = $cel->token;
            }
            
            $celLink = dol_buildpath('/cel/cel.php', 2).'?key='.$token;
            
			$extrafields = $item->array_options;
			$extrafields['options_cel_link'] = $celLink;
			$item->array_options = $extrafields;
			$result = $item->insertExtraFields();
			
			// Update extrafields
			if ($action == 'PROPAL_VALIDATE')
			{
				return 1;
			}            
			
            // Return if autosend is desactivated
			if (empty($conf->global->CEL_TOS_AUTO_SEND))
			{
				return 1;
			} 
			           
            $substit = array(
                '__OBJREF__' => $ref,
                '__CELURL__' => $celLink,
                '__SOCNAM__' => $conf->global->MAIN_INFO_SOCIETE_NOM,
                '__SOCMAI__' => $conf->global->MAIN_INFO_SOCIETE_MAIL,
                '__CLINAM__' => $item->client->name,                
            );
            
            
            if (trim($_POST['sendto']))
            {
                // Recipient is provided into free text
                $sendto = trim($_POST['sendto']);
                $sendtoid = 0;
            }
            else 
            {
                $sendtoid = $object->sendtoid;
            
                if ($sendtoid){
                    $sendto = $item->thirdparty->contact_get_property($sendtoid, 'email');
                }else{
                    $sendto = $item->thirdparty->email;
                }           
            }                        

            $from = $conf->global->MAIN_INFO_SOCIETE_MAIL;
            
            $message = $langs->transnoentities('CELEmailBody');
            $subject = $langs->transnoentities('CELEmailSubject');
            
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
            
            if (!$result)
            {
                $result = $mail->sendfile();
                if ($result){
                    dol_syslog('CEL: Email containing terms of sale link has been correctly sent');
                }else{
                    dol_syslog('CEL: Error sending email containing terms of sale link');
                }
                return $result;
            }
            else
            {
                dol_syslog('CEL: Error in creating email containing terms of sale link');
                return $result;
            }
     
        }

		return 0;
    }

}
?>
