<div class="ils-search-form">
    <form onSubmit="showreglitreLoading();" id="webloftform" target="reglitre_treff_frame" action="<?= ILS_URL . '/search.php' ?>" method="GET">
        S&oslash;keord:&nbsp;
        <input type="text" value="<?= $hamedsok ?>" id="search" name="wl_ils_s" accept-charset="utf-8" />&nbsp;<input type="submit" value="S&oslash;k">
        <input type="hidden" name="mittbibliotek" value="<?= $brukbibliotek ?>" />
        <input type="hidden" name="omslagbokkilden" value="<?= $omslagbokkilden ?>" />
        <input type="hidden" name="omslagnb" value="<?= $omslagnb ?>" />
        <input type="hidden" name="treffbokhylla" value="<?= $treffbokhylla ?>" />
        <input type="hidden" name="hamedbilder" value="<?= $hamedbilder ?>" />
        <input type="hidden" name="makstreff" value="<?= $makstreff ?>" />
        <input type="hidden" name="bibsysbestand" value="<?= $bibsysbestand ?>" />
        <input type="hidden" name="dobokhylla" value="0" />
        <?php if (trim($enkeltpost) != ""): ?>
            <input type="hidden" name="enkeltposturl" value="<?= base64_encode(get_permalink($enkeltpost)) ?>" />
        <?php endif; ?>
    </form>
</div>

<div id="divreglitreLoading" style="text-align: center; margin-top: 20px;">
    <img style="border: none; box-shadow: none;" src="<?= ILS_URL . '/icons/spinner.gif' ?>" alt="Laster..." />
</div>

<div id="divreglitreFrameHolder" style="display:none;">
    <iframe<?= $framekode ?> name="reglitre_treff_frame" onLoad="hidereglitreLoading();" id="ils_results_frame" frameborder="0" width="100%">
        //Nettleser st&oslash;tter ikke iframes. Kan ikke bruke katalogs&oslash;k.
    </iframe>
</div>

<script type="text/javascript" src="<?= ILS_URL . '/js/resizeiframe.js' ?>"></script>
