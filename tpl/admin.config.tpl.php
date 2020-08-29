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

/**	    \file       htdocs/cel/tpl/admin.config.tpl.php
 *		\ingroup    ndfp
 *		\brief      Admin setup view
 */
 
llxHeader('', $langs->trans('CELAdmin'), '', '', 0, 0, array('/cel/js/functions.js.php', '/cel/js/jquery.form.js'));

echo ($message ? dol_htmloutput_mesg($message, '', ($error ? 'error' : 'ok'), 0) : '');

print_fiche_titre($langs->trans('CELAdmin'), $linkback, 'setup');

?>
<script type="text/javascript">
<!--
$(document).ready(function () {
        $("#generate_token").click(function() {
        	$.get( "<?php echo DOL_URL_ROOT; ?>/core/ajax/security.php", {
        		action: 'getrandompassword',
        		generic: true
			},
			function(token) {
				$("#security_token").val(token);
			});
        });
});
-->
</script>
<br />
<?php if (!empty($filename)) { ?>
	<?php print_titre($langs->trans("ReadCGVFile")); ?>
	<table border="0">
	<tr>
		<td><a href="<?php echo $link; ?>"><?php echo img_object($filename, 'pdf@cel'); ?></a></td>
		<td><a href="<?php echo $link; ?>"><?php echo $filename; ?></a></td>
	</tr>
	</table>
	<br />
<?php } ?>

<?php print_titre($langs->trans("SelectCGVFile")); ?>
<form  id="upform" name="upform" action="<?php echo $_SERVER['PHP_SELF']; ?>?action=update"  enctype="multipart/form-data" method="post">
<input type="hidden" name="action" value="update" />

<div id="progressbar"></div>
<input type="file" id="file" name="file" />&nbsp;<input type="submit" class="butAction" name="update" id="update" value="<?php echo $langs->trans('Ok'); ?>" />&nbsp;<input type="submit" class="butActionDelete" name="cancel" id="cancel" value="<?php echo $langs->trans('Cancel'); ?>" />	
<br /><br />
</form>

<?php print_titre($langs->trans("CELOptions")); ?>
<form name="celsetup" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post">
<input type="hidden" name="action" value="save" />
<input type="hidden" name="token" value="<?php echo $_SESSION['newtoken']; ?>" />
<table class="noborder" width="100%">

<tr class="liste_titre">
<td><?php echo $langs->trans("Configuration"); ?></td>
<td><?php echo $langs->trans("Value"); ?></td>
<td><?php echo $langs->trans("Infos"); ?></td>
</tr>

<tr class="impair">
    <td class="fieldrequired"><?php echo $langs->trans("SecurityToken"); ?></td>
    <td><input size="32" type="text" id="security_token" name="security_token" value="<?php echo $security_token; ?>" /> <?php echo img_picto($langs->trans('Generate'), 'refresh', 'id="generate_token" class="linkobject"'); ?></td>
    <td><?php echo $form->textwithpicto('', $htmltooltips['SecurityToken'], 1, 0); ?></td>    
</tr>

<tr class="pair">
    <td class="fieldrequired"><?php echo $langs->trans("DeliveryReceiptEmail"); ?></td>
    <td><?php echo $form->selectyesno("delivery_receipt_email", $delivery_receipt_email, 1); ?></td>
    <td><?php echo $form->textwithpicto('', $htmltooltips['DeliveryReceiptEmail'], 1, 0); ?></td>    
</tr>

<tr class="impair">
    <td class="fieldrequired"><?php echo $langs->trans("CcEmail"); ?></td>
    <td><?php echo $form->selectyesno("cc_email", $cc_email, 1); ?></td>
    <td><?php echo $form->textwithpicto('', $htmltooltips['CcEmail'], 1, 0); ?></td>    
</tr>

<tr class="pair">
    <td class="fieldrequired"><?php echo $langs->trans("CcEmails"); ?></td>
    <td><input size="32" type="text" id="cc_emails" name="cc_emails" value="<?php echo $cc_emails; ?>" /></td>
    <td><?php echo $form->textwithpicto('', $htmltooltips['CcEmails'], 1, 0); ?></td>    
</tr>

<tr class="liste_titre">
    <td><?php echo $langs->trans("IntegrationParameters"); ?></td>
    <td><?php echo $langs->trans("Value"); ?></td>
    <td><?php echo $langs->trans("Infos"); ?></td>   
</tr>

<tr class="impair">
    <td class="fieldrequired"><?php echo $langs->trans("UpdatePropalStatut"); ?></td>
    <td><?php echo $form->selectyesno("update_propal_statut", $update_propal_statut, 1); ?></td>
    <td><?php echo $form->textwithpicto('', $htmltooltips['UpdatePropalStatut'], 1, 0); ?></td>    
</tr>

<tr class="pair">
    <td class="fieldrequired"><?php echo $langs->trans("TosAutoSend"); ?></td>
    <td><?php echo $form->selectyesno("tos_auto_send", $tos_auto_send, 1); ?></td>
    <td><?php echo $form->textwithpicto('', $htmltooltips['TosAutoSend'], 1, 0); ?></td>    
</tr>

</table>

<br />
<center>
<input type="submit" name="save" class="button" value="<?php echo $langs->trans("Save"); ?>" />
</center>

</form>



<?php llxFooter(''); ?>
