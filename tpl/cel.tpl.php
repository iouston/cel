<?php
/* Copyright (C) 2012      Mikael Carlavan        <contact@mika-carl.fr>
* Copyright (C) 2022 Julien Marchand <julien.marchand@iouston.com>
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
 *     	\file       htdocs/public/cel/tpl/payment_form.php
 *		\ingroup    cel
 */
  
if (empty($conf->cel->enabled)) 
    exit;


header('Content-type: text/html; charset=utf-8');
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <meta name="robots" content="noindex,nofollow" />
    <title><?php echo $langs->trans('CELFormTitle'); ?></title>
    <link rel="stylesheet" type="text/css" href="<?php echo DOL_URL_ROOT.$conf->css.'?lang='.$langs->defaultlang; ?>" />
    <style type="text/css">
        body{
            width : 70%; 
            margin: auto;
            text-align : center;
        }
        
        #logo{
            width : 100%;
            margin : 30px 0px 30px 0px;
        }       

        #propal-content{
            width : 100%;
            text-align : left;
        }
        
        #propal-table{
            width : 100%;
            text-align : left;
            border : 1px solid #000;            
        }

        #propal-table tr{
            width : 100%;
        }        
        
        .propal-row-left{
            width : 40%;
            text-align : left;

        }
        
        .propal-row-right{
            width : 60%;
            text-align : right;
        } 
        
        #validate-button{
            text-align : center;  
        }      
        .red{color:red; font-weight: bold;}           
    </style>
 
	<script type="text/javascript" src="<?php echo DOL_URL_ROOT.'/includes/jquery/js/jquery.min.js'; ?>"></script>

   
    <script type="text/javascript">
    $(document).ready(function() {
    	$("#valid-button").click(function(e){
			$("#action").val('valid');
		});	
	
		$("#cancel-button").click(function(e){
			$("#action").val('cancel');
		});
		
		$("#validate-checkbox").on('change', function(e){
			if ($(this).prop('checked'))
			{
				$("#valid-button").prop('disabled', false);
			}
			else
			{
				$("#valid-button").prop('disabled', true);
			}
		});
		
		//$("#valid-button").prop('disabled', true);
	});
	</script>
</head>

<body>
    <div id="logo">
        <?php if (!empty($urlLogo)) { ?>    
            <img id="soc-logo" title="<?php echo $societyName; ?>" src="<?php echo $urlLogo; ?>" />
        <?php } ?>        
    </div>
       
    <div id="propal-content">
        <h1><?php echo $langs->transnoentities('CELFormWelcomeTitle'); ?></h1><br />
        
        <p><?php echo $langs->transnoentities('CELFormWelcomeText'); ?></p><br /> 
        <p><?php echo $langs->transnoentities('CELFormDescText', $conf->global->MAIN_INFO_SOCIETE_MAIL); ?></p>
        <br /><br />
        
		<?php if ($propal) { ?>
			<iframe src="<?php echo $propal; ?>" width="100%" height="500px">
			</iframe>
         <?php } ?>
         
		<br /><br />
		
		<?php /*if ($cgv) { ?>
			<iframe src="<?php echo $cgv; ?>" width="100%" height="300px">
			</iframe>
         <?php } */?>
         
         <br /><br />
         
         <div id="validate-button">
         	
			 <form id="propalform" action="<?php echo dol_buildpath('/cel/cel.php', 2); ?>" method="post">
				<input type="hidden" name="key" value="<?php echo $key; ?>" />
				<input type="hidden" id="action" name="action" value="valid" />
                <input type="hidden" name="ipsignatory" value="<?php echo $_SERVER['REMOTE_ADDR']; ?>" />
				<input id="firstname" name="firstname" type="text" value="" required="required" placeholder="Prénom"><span class="red">*</span><br>
                <input id="lastname" name="lastname" type="text" value="" required="required" placeholder="Nom"><span class="red">*</span><br>
                <input id="job" name="job" type="text" value="" required="required" placeholder="Profession"><span class="red">*</span><br><br>
                <input type="checkbox" value="1" id="validate-checkbox" /> <strong><?php echo $langs->trans('PropalValidateTerms'); ?></strong><br /><br />
                <input id="valid-button" type="submit" class="butAction" value="<?php echo $langs->trans('PropalValidate'); ?>" disabled="disabled"/>&nbsp;<input id="cancel-button" type="submit" class="butActionDelete" value="<?php echo $langs->trans('PropalCancel'); ?>" />		
			 </form>
         </div>
    </div>
    <br /><br />
    
</body>
</html>
