<?php
$this->content .= <<< EOF
	<div class="mail_footer">
		<a style="float: left;" title="page d'accueil" href="{$constant['URL']}"><img id=logo alt="logo techni contact" src="{$constant['URL']}ressources/logo.gif" nosend="1"/></a>
		M.D.2i  SAS 253 rue Galliéni – 92774 BOULOGNE BILLANCOURT cedex<br />
		Téléphone : (33) 01.55.60.29.29 Télécopie : (33) 01 83 62 36 12 –<br />
		<a href="{$constant['URL']}">{$constant['URL']}</a> – e-mail : <a href="mailto:info@techni-contact.com">info@techni-contact.com</a><br />
		SAS au capital de 160 000 Euros – R.C. NANTERRE B 392 772 497
	</div>
</body>
</html>
EOF;

?>