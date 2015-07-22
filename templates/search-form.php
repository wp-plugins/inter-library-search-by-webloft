<?php
	// er $trefflisteside satt i shortcode? I såfall må vi lage ny target for form

if ($trefflisteside > 0) {
	$resultatperma = get_permalink($trefflisteside);
	if ($resultatperma != "") { // klarte å finne permalink?
	$sjokk = explode ("?" , $resultatperma); // alle query strings i $sjokk[1] HVIS DET ER NOEN
	$formaction = $sjokk[0];
	$formtarget = "_top";
	$spinnkode = "";	
	}
} else {
	$formaction = ILS_URL . '/search.php';
	$formtarget = "reglitre_treff_frame";
	$spinnkode = " onSubmit=\"showreglitreLoading();\"";
}
?>


<div class="ils-search-form-<?= $sokesize ?>">
    <form<?= $spinnkode ?> id="webloftform" target="<?= $formtarget ?>" action="<?= $formaction ?>" method="GET">
        S&oslash;keord:&nbsp;
        <input type="text" value="<?= $hamedsok ?>" id="search" name="webloftsok_query" accept-charset="utf-8" />&nbsp;<input type="submit" value="S&oslash;k">
        <input type="hidden" name="mittbibliotek" value="<?= $brukbibliotek ?>" />
        <input type="hidden" name="omslagbokkilden" value="<?= $omslagbokkilden ?>" />
        <input type="hidden" name="omslagnb" value="<?= $omslagnb ?>" />
        <input type="hidden" name="treffbokhylla" value="<?= $treffbokhylla ?>" />
        <input type="hidden" name="hamedbilder" value="<?= $hamedbilder ?>" />
        <input type="hidden" name="makstreff" value="<?= $makstreff ?>" />
        <input type="hidden" name="bibsysbestand" value="<?= $bibsysbestand ?>" />
        <input type="hidden" name="dobokhylla" value="0" />
        <input type="hidden" name="viseavansertlenke" value="<?= $viseavansertlenke ?>" />
        <input type="hidden" name="sokesize" value="<?= $sokesize ?>" />

<?php
if (isset($sjokk[1])) { // Fantes det parametre på den trefflistesiden?
	$parameters = explode ("&" , $sjokk[1]); // array med parametre
	if (is_array($parameters)) {
		foreach ($parameters as $parameter) {
			$ettparameter = explode ("=" , $parameter);
			echo "<input type=\"hidden\" name=\"" . $ettparameter[0] . "\" value=\"" . $ettparameter[1] . "\" />";
		}
	}
}
?>


        <?php if (trim($enkeltpost) != ""): ?>
            <input type="hidden" name="enkeltposturl" value="<?= base64_encode(get_permalink($enkeltpost)) ?>" />
        <?php endif; ?>
    </form>
</div>

<div id="divreglitreLoading" style="text-align: center; margin-top: 20px;">
    <img style="border: none; box-shadow: none;" src="<?= ILS_URL . '/icons/spinner.gif' ?>" alt="Laster..." />
</div>

<div id="divreglitreFrameHolder" style="display:none;">
    <iframe<?= $framekode ?> name="reglitre_treff_frame" onLoad="hidereglitreLoading();" id="ils_results_frame" frameborder="0" width="100%" style="padding: 0; border: 0;">
        //Nettleser st&oslash;tter ikke iframes. Kan ikke bruke katalogs&oslash;k.
    </iframe>
</div>

<script type="text/javascript" src="<?= ILS_URL . '/js/resizeiframe.js' ?>"></script>
