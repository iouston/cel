<?php
/* Copyright (C) 2004-2007 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2013 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005      Simon Tosser         <simon@kornog-computing.com>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@capnetworks.com>
 * Copyright (C) 2010	   Pierre Morin         <pierre.morin@auguria.net>
 * Copyright (C) 2010	   Juanjo Menent        <jmenent@2byte.es>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 * or see http://www.gnu.org/
 */

/**
 *	\file       htdocs/document.php
 *  \brief      Wrapper to download data files
 *  \remarks    Call of this wrapper is made with URL:
 * 				document.php?modulepart=repfichierconcerne&file=pathrelatifdufichier
 */

define("NOCSRFCHECK",1);
define("NOLOGIN",1);


$res=@include("../main.inc.php");					// For root directory
if (! $res) $res=@include("../../main.inc.php");	// For "custom" directory

require_once(DOL_DOCUMENT_ROOT."/core/lib/company.lib.php");
require_once(DOL_DOCUMENT_ROOT."/core/lib/security.lib.php");
require_once(DOL_DOCUMENT_ROOT."/core/lib/date.lib.php");
require_once(DOL_DOCUMENT_ROOT."/core/lib/functions.lib.php");
require_once(DOL_DOCUMENT_ROOT."/core/lib/functions2.lib.php");
require_once(DOL_DOCUMENT_ROOT."/core/lib/files.lib.php");
require_once(DOL_DOCUMENT_ROOT."/comm/propal/class/propal.class.php");

$encoding = '';

$id = GETPOST('id', 'int');

$object = new Propal($db);
$object->fetch($id);

//prise en compte du module version devis par iouston
if($conf->global->MAIN_MODULE_VERSIONSDEVIS==1){
  $version ='_v'.$object->array_options['options_version_actuelle'];  
}

$original_file = $conf->propal->dir_output.'/'.dol_sanitizeFileName($object->ref).'/'.dol_sanitizeFileName($object->ref).$version.'.pdf';
/*
 * Action
 */

// None


/*
 * View
 */

// Define mime type
$type = 'application/octet-stream';
if (GETPOST('type','alpha')) $type=GETPOST('type','alpha');
else $type=dol_mimetype($original_file);

// Define attachment (attachment=true to force choice popup 'open'/'save as')
$attachment = true;
if (isset($_GET["attachment"])) $attachment = GETPOST("attachment")?true:false;
if (! empty($conf->global->MAIN_DISABLE_FORCE_SAVEAS)) $attachment=false;

// Security: Delete string ../ into $original_file
$original_file = str_replace("../","/", $original_file);

clearstatcache();

$filename = basename($original_file);

// Output file on browser
dol_syslog("document.php download $original_file $filename content-type=$type");
$original_file_osencoded=dol_osencode($original_file);	// New file name encoded in OS encoding charset

// This test if file exists should be useless. We keep it to find bug more easily
if (! file_exists($original_file_osencoded))
{
	dol_print_error(0,$langs->trans("ErrorFileDoesNotExists",$original_file));
	exit;
}

// Permissions are ok and file found, so we return it
header('Content-Description: File Transfer');
if ($encoding)   header('Content-Encoding: '.$encoding);
if ($type)       header('Content-Type: '.$type.(preg_match('/text/',$type)?'; charset="'.$conf->file->character_set_client:''));
// Add MIME Content-Disposition from RFC 2183 (inline=automatically displayed, atachment=need user action to open)
if ($attachment) header('Content-Disposition: attachment; filename="'.$filename.'"');
else header('Content-Disposition: inline; filename="'.$filename.'"');
header('Content-Length: ' . dol_filesize($original_file));
// Ajout directives pour resoudre bug IE
header('Cache-Control: Public, must-revalidate');
header('Pragma: public');

//ob_clean();
//flush();
    
readfile($original_file_osencoded);

if (is_object($db)) $db->close();
