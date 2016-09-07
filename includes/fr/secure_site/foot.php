		<div class="zero"></div>
		<div id="foot">
			<div class="menu">
				<a href="<?php echo URL ?>nous.html"><?php echo FOOT_OUR_COMPANY ?></a>
				<a href="<?php echo URL ?>recevoirCatalogues.html"><?php echo FOOT_OUR_CATALOGS ?></a>
				<a href="<?php echo URL ?>plan.html"><?php echo FOOT_SITEMAP ?></a>
				<a href="<?php echo URL ?>devenirAnnonceur.html"><?php echo FOOT_BECOME_PARTNAIR ?></a>
				<a href="<?php echo URL ?>contact.html"><?php echo FOOT_CONTACT ?></a>
			</div>
			<div class="kn">
				<div class="partners">
					<img src="<?php echo SECURE_RESSOURCES_URL ?>visa.gif" alt="visa" border="0" />
					<img src="<?php echo SECURE_RESSOURCES_URL ?>mastercard.gif" alt="mastercard" border="0" />
					<a id="logo-paypal" href="http://www.paypal.fr/presentation"><img src="<?php echo SECURE_RESSOURCES_URL ?>paypal.gif" alt="PayPal" border="0" /></a>
					<a id="logo-fia-net" href="http://www.fia-net.com"><img src="<?php echo SECURE_RESSOURCES_URL ?>fia-net.gif" alt="fia-net" border="0" /></a>
				</div>
				<div class="fevad"><a href="http://www.fevad.com/"><img src="<?php echo SECURE_RESSOURCES_URL ?>fevad.gif" alt="fevad" border="0" /></a></div>
				<div class="copy">
					Copyright &copy; 2002-2008 MD2I - <a href="<?php echo URL ?>infoslegales.html"><?php echo FOOT_LEGAL_INFORMATION ?></a><br />
					<?php echo FOOT_PARTNAIR ?> : <a href="http://www.keyyo.fr" alt="keyyo.fr, la téléphonie IP des petites entreprises" title="keyyo.fr, la téléphonie IP des petites entreprises">Téléphonie IP pour entreprise Keyyo</a> <a href="http://www.xboxfrance.com/tests.html"><?php echo FOOT_VIDEO_GAMES ?></a> <a href="http://www.onedirect.fr/">www.onedirect.fr</a>
				</div>
			</div>
			<div class="zero"></div>
		</div>
	</div>
</div>
<script type="text/javascript">
document.getElementById('logo-fia-net').onclick = function () {
	var win = window.open('http://www.fia-net.com/annuaire/certificat.php?Key=5302&lang=FRA','certificat','status=1,width=620,height=382');
	return false;
};
document.getElementById('logo-paypal').onclick = function () {
	var win = window.open('http://www.paypal.fr/presentation','presentation_paypal','status=1,width=620,height=382');
	return false;
};
</script>
<?php	if (SHOW_TAGS) { ?>
<div id="xiti-logo">
	<script type="text/javascript">
	xtnv = document;        //parent.document or top.document or document        
	xtsd = "https://logs";
	xtsite = "157945";
	xtn2 = "1";        // level 2 site
	xtpage = "";        //page name (with the use of :: to create chapters)
	xtdi = "";        //implication degree
	</script>
	<script type="text/javascript" src="<?php echo SECURE_RESSOURCES_URL ?>xtroi.js"></script>
	<noscript>
		<img width="1" height="1" alt="" src="https://logs.xiti.com/hit.xiti?s=157945&s2=&p=&di=&" />
	</noscript>
</div>
</center>
<?php		if (defined("CRITEO")) { ?>
<!-- Criteo Loader Widget -->
<script type="text/javascript" src="<?php echo SECURE_RESSOURCES_URL ?>criteo_ld.js"></script>
<?php		} ?>
<?php		if (!defined("__COMMAND_PAID__")) { ?>
<!-- Google Analytics -->
<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
var pageTracker = _gat._getTracker("UA-4217476-2");
pageTracker._initData();
pageTracker._trackPageview();
</script>
<?php		}
		} ?>
</body>
</html>
