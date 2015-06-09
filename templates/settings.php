<?php

$mittbibliotek = '';
$valgt = get_option('wl_ils_option_mittbibliotek' , '2020000');
$omslagbokkilden = get_option('wl_ils_option_omslagbokkilden' , '0');
$omslagnb = get_option('wl_ils_option_omslagnb' , '0');
$hamedbilder = get_option('wl_ils_option_hamedbilder' , '0');
$makstreff = get_option('wl_ils_option_makstreff' , '0');
$bibsysbestand = get_option('wl_ils_option_bibsysbestand' , '0');
$enkeltpost = get_option('wl_ils_option_enkeltpost' , '');
$treffbokhylla = get_option('wl_ils_option_treffbokhylla' , '');
$viseavansertlenke = get_option('wl_ils_option_viseavansertlenke' , '');

include(dirname(__FILE__).'/../serverliste.php');

?>
<div class="wrap">

    <h1>ILS Search by Webloft - innstillinger</h1>

    <form method="post" action="options.php">

        <?php settings_fields('wl_ils_options'); ?>

        <label for="wl_ils_option_mittbibliotek">Angi katalog det skal s&oslash;kes i:</label>
        &nbsp;&nbsp;
        <select name="wl_ils_option_mittbibliotek">
            <?php
                foreach ($bibliotek as $ettbibliotek) {
                    $hepp = '';
                    $temp = explode ("|x|" , $ettbibliotek);
                    if ($temp[1] == $valgt) {
                        $hepp = " selected";
                    }
                    echo "<option value=\"" . $temp[1] . "\"" . $hepp . ">" . $temp[0] . "</option>\n";
                }
            ?>
        </select>

        <br><br>

        <i>
            Denne innstillingen kan overstyres ved &aring; angi et annet bibliotek i shortcode/kortkode,
            f.eks. slik: <strong>[wl-ils mittbibliotek=2021900]</strong> for B&aelig;rum folkebibliotek.
            Du finner en oppdatert liste over bibliotek-koder ved &aring; <a target="_blank" href="<?= ILS_URL . '/templates/bibkoder.php' ?>">klikke her</a>.
        </i>

        <h2>Innstillinger for alle bibliotek</h2>

        <h3>Visning</h3>

        <p>
            <label for="wl_ils_option_makstreff">Vis hvor mange treff per side?&nbsp;</label>
            <select name="wl_ils_option_makstreff">
                <option value="10"<?php if ($makstreff == '10') { echo " selected"; } ?>>10</option>
                <option value="25"<?php if ($makstreff == '25') { echo " selected"; } ?>>25</option>
                <option value="50"<?php if ($makstreff == '50') { echo " selected"; } ?>>50</option>
                <option value="100"<?php if ($makstreff == '100') { echo " selected"; } ?>>100</option>
            </select>
        <br>
            <label for="wl_ils_option_enkeltpost">Side for visning av enkeltposter (la st&aring; uendret for &aring; g&aring; til biblioteksystemets egen visning): </label>
            <select name="wl_ils_option_enkeltpost">
                <option value="">Ingen - g&aring; til biblioteksystemet</option>

<?php
// Hent alle innlegg (og sider samtidig?)
// Lagre id og tittel der hvor [enkeltpost] forekommer

$args = array(
'post_type' => array('post' , 'page'),
'posts_per_page' => '-1'
    );

if ($post_query = get_posts($args)) {

    $hits = '';
    foreach ($post_query as $post) {
        if ( stripos($post->post_content, '[wl-ils-enkeltpost') !== false ) { // kortkoden finnes!!
            $hits[] = $post->ID . "|x|" . $post->post_title;
        }
    }

    if (is_array($hits)) { // vi har minst ett treff
        foreach ($hits as $hit) {
            $digg = '';
            list ($id , $tittel) = explode ("|x|" , $hit);
            if ($id == $enkeltpost) { $digg = " selected"; }
                echo "<option value=\"" . $id . "\"" . $digg . ">" . $tittel . "</option>\n";
            }
        }
    }
?>
            </select>
		<br>
            <label for="wl_ils_option_viseavansertlenke">Vise lenke til avansert s&oslash;k? (Lar brukeren g&aring; til biblioteksystemet for &aring; gj&oslash;re mer avanserte s&oslash;k)</label>&nbsp;
            <input name="wl_ils_option_viseavansertlenke" type="checkbox" value="1" <?php if ($viseavansertlenke == "1") { echo "checked";} ?> />
        </p>

        <h3>Omslagsbilder</h3>

        <p>
            <label for="wl_ils_option_omslagbokkilden">Pr&oslash;ve &aring; finne omslag hos Bokkilden?</label>
            <input name="wl_ils_option_omslagbokkilden" type="checkbox" value="1" <?php if ($omslagbokkilden == "1") { echo "checked";} ?> />
            <br>
            <label for="wl_ils_option_omslagnb">Pr&oslash;ve &aring; finne omslag hos Nasjonalbiblioteket?</label>
            <input name="wl_ils_option_omslagnb" type="checkbox" value="1" <?php checked( '1', $omslagnb ); ?> />
            <br>
            <label for="wl_ils_option_hamedbilder">Vise omslagsbilder i det hele tatt (fjern hake for &aring; droppe alle omslagsbilder)?</label>
            <input name="wl_ils_option_hamedbilder" type="checkbox" value="1" <?php checked( '1', $hamedbilder ); ?> />
            <br>
        </p>

		<h3>Berike treffliste</h3>

		<p>
            <label for="wl_ils_option_treffbokhylla">Vise knapp for &aring; hoppe mellom treff i Bokhylla og i katalogen?</label>
            <input name="wl_ils_option_treffbokhylla" type="checkbox" value="1" <?php if ($treffbokhylla == "1") { echo "checked";} ?> />
        </p>

        <h2>Innstillinger for Bibsys-bibliotek</h2>

        <p>
            <label for="wl_ils_option_bibsysbestand">Hente bestandsinformasjon fra Bibsys (kan gj&oslash;re s&oslash;ket tregt!)?</label>
            <input name="wl_ils_option_bibsysbestand" type="checkbox" value="1" <?php checked( '1', $bibsysbestand ); ?> />
            <br>
        </p>


        <p class="submit">
            <input type="submit" class="button-primary" value="Oppdat&eacute;r" />
        </p>

    </form>
</div>
