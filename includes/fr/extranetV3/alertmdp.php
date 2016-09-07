<?php
if(strlen($user->pass) == 6 && WHERE != 'Coordonn&eacute;es')
{
?>
<div class="centre">
	<div class="bloc">
		<div class="bloc-titre"><?php echo ALERT_PASS_BECAREFUL ?></div>
		<div class="bloc-texte" style="color: #FF0000">
			<?php echo ALERT_PASS_DESC ?> <a href="infos.html?<?php print(session_name() . '=' . session_id()) ?>#password"><?php echo ALERT_PASS_EDIT ?></a>
		</div>
		<div class="miseAZero"></div>
	</div>
</div>
<?php
}
?>
