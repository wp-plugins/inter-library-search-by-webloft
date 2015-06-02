<?php

$system = (isset($treff['biblioteksystem']) ? $treff['biblioteksystem'] : '');

if ($system == 'koha') {
    $utgitt = '';
    if (isset($treff['utgitthvem'])) { $utgitt = $treff['utgitthvem']; }
    if (isset($treff['utgitthvor'])) { $utgitt .= ", " . $treff['utgitthvor']; }
    if (isset($treff['utgittaar'])) { $utgitt .= ", " . $treff['utgittaar']; }
} else {
    $utgitt = (((isset($treff['utgitthvem'])) && (trim($treff['utgitthvem']) != "")) ? $treff['utgitthvem'] : "[s.n.]") . ', ' .
                (((isset($treff['utgitthvor'])) && (trim($treff['utgitthvor']) != "")) ? $treff['utgitthvor'] : '[s.l.]')
                . (((isset($treff['utgittaar'])) && (trim($treff['utgittaar']) != "")) ? ', ' . $treff['utgittaar'] : '');
}


if ($system != 'koha') {
    $ledige = 0;
    $uklar = false;
    if (is_array($treff['bestand'])) {
        foreach ($treff['bestand'] as $bestand) { // Noen ledige?
            if ($system == 'bibsys') {
                if ($bestand->circulationStatus == "0") {
                    $ledige++;
                }
            } else {
                if ($bestand['h'] == "0") {
                    $ledige++;
                }
            }
        }
    } else {
        $uklar = true;
    }
}

switch ($system) {
    case 'koha':        $bestilleurl = str_replace ("show" , "acquire" , $treff['permalink']); break; /* oh, you clever AGAIN!! */
    case 'bibsys':      $bestilleurl = str_replace ("show" , "acquire" , $treff['permalink']); break; /* oh, you clever AGAIN!! */
    case 'tidemann':    $bestilleurl = str_replace ("websok?" , "mappami?jumpmode=reservering&" , $treff['permalink']); break; /* oh, you clever */
    case 'bibliofil':   $bestilleurl = str_replace ("websok?" , "mappami?jumpmode=reservering&" , $treff['permalink']); break; /* oh, you clever */
    default:            $bestilleurl = false;
}



?>
<div class="ils-single-result">
    <div class="bildecontainer">

<?php if ((isset($treff['omslag'])) && ($treff['omslag'] != "")): ?>
        <img src="<?= $treff['omslag'] ?>" alt="<?= $treff['tittelinfo'] ?>" />
<?php else: ?>
        <img src="<?= ILS_URL . '/icons/ikke_digital.jpg' ?>" alt="<?= $treff['tittelinfo'] ?>" />
<?php endif; ?>

<?php if ($system != 'koha'): ?>
        <div class="social">
            <a target="_blank" title="Del på Twitter" href="https://twitter.com/intent/tweet?url=<?= urlencode(ILS_URL . '/templates/gotourn.php') ?>?params=<?= $treff['facebook'] ?>&via=bibvenn&text=<?= urlencode($treff['twitter']) ?>">
                <img style="width: 20px; height: 20px;" src="<?= twitter_ikon() ?>" alt="Del på Twitter" />
            </a>
            <a target="_self" title="Del på Facebook" href="javascript:fbShare('<?= ILS_URL . '/templates/gotourn.php' ?>?params=<?= $treff['facebook'] ?>', 700, 350)">
                <img style="width: 50px; height: 21px;" src="<?= facebook_ikon() ?>" alt="Facebook-deling" />
            </a>
        </div>
<?php endif; ?>

    </div>

    <div class="infocontainer">
        <h2><?= str_replace(": :" , ":", $treff['tittelinfo']) ?></h2>
        <p>
            <?php if ((isset ($treff['forfatter'])) && ($treff['forfatter'] != "")): ?>
                <strong>Forfatter : </strong><?= $treff['forfatter'] ?><br>
            <?php endif; ?>

            <?php if (($system != 'koha') && (isset($treff['ansvarsangivelse'])) && ($treff['ansvarsangivelse'] != "")): ?>
                <strong>Opphav : </strong><?= $treff['ansvarsangivelse'] ?><br>
            <?php endif; ?>

            <?php if (!empty($utgitt)): ?>
                <strong>Utgitt :</strong> <?= $utgitt ?><br>
            <?php endif; ?>

            <?php if ((isset($treff['isbn'])) && ($treff['isbn'] != "")): ?>
                <strong>ISBN :</strong> <?= $treff['isbn'] ?><br>
            <?php endif; ?>

            <?php if (in_array($system, array('koha','bibsys')) && isset($treff['beskrivelse']) && ($treff['beskrivelse'] != "")): ?>
                <?= $treff['beskrivelse'] ?><br>
            <?php endif; ?>

            <br style="clear: both;">

            <?php if ($system != 'koha' // Vi har ikke bestandsinfo i Koha
                        && isset($treff['bestand'])
                        // Bare hvis hake for bestand i Bibsys er valgt!
                        && ($system != 'bibsys' || ("1" == get_option('wl_ils_option_bibsysbestand' , '0')))
                        ):  ?>

                <?php if ($ledige > 0): ?>
                    <div class="green dot" title="Ledig!"></div>&nbsp;Ledig<br><br>
                <?php elseif ($uklar && ($system != 'bibsys')): /* Ikke vis Uklar for bibsys */ ?>
                    <div class="red dot" title="Uklar bestand"></div>&nbsp;Uklar bestand - kontakt biblioteket!<br><br>
                <?php else: ?>
                    <div class="red dot" title="Ingen ledige!"></div>&nbsp;Ingen ledige ...<br><br>
                <?php endif; ?>

            <?php endif; ?>



            <?php if (isset($treff['fulltekst']) || $bestilleurl): ?>
                <div class="buttons">
                    <?php if (isset($treff['fulltekst'])): /* finnes den på nett? */ ?>
                        <button class="link-online" onclick="location.href='<?= $treff['fulltekst'] ?>'">Les på nett</button>
                    <?php endif; ?>
                    <?php if ($bestilleurl): ?>
                        <button class="link-order" onclick="location.href='<?= $bestilleurl ?>'">Bestille/reservere</button>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php $bestilleurl = false; $uklar = false; /* må rydde opp */ ?>

        </p>
    </div><!-- /.infocontainer -->

    <?php if ($system == 'koha'):  ?>

        <?php if ((isset($treff['bestand'])) && (is_array($treff['bestand']))): /* bare hvis vi har bestandinfo */ ?>
            <div class="bestandcontainer">
                <h3>Eksemplarer:</h3>
                <p>
                    <?php foreach ($treff['bestand'] as $bestand): ?>
                        <?= $bestand->institution
                                . (isset($bestand->collection) ? "&nbsp;/&nbsp;{$bestand->collection}" : '')
                                . (isset($bestand->callnumber) ? "&nbsp;/&nbsp;{$bestand->callnumber}" : '') ?>

                        :
                        <?php
                            echo bestandsinfo ($bestand->circulationStatus , $bestand->useRestriction); // status, restriction
                            if (($bestand->circulationStatus == "4") || ($bestand->circulationStatus == "5")) { // UTLÅNT
                                setlocale (LC_TIME , "nb_NO"); // norsk dato
                                echo " til " . strftime("%e. %B %G" , strtotime($bestand['y']));
                            }
                        ?>
                        <br>
                    <?php endforeach; ?>
                </p>
            </div><!-- /.bestandcontainer -->
        <?php endif; ?>


    <?php else: /* EKSPERIMENTELL TAB-LØSNING */ ?>

        <div class="tabs">

            <ul>
                <li class="active"><a href="#tab1">Eksemplarer</a></li>
                <li><a href="#tab2">Beskrivelse</a></li>
                <li><a href="#tab3">Flere opplysninger</a></li>
            </ul>

            <div class="tab-content">


                <div id="tab1" class="tab active">
                    <?php if (isset($treff['bestand']) && is_array($treff['bestand'])): ?>
                        <?php foreach ($treff['bestand'] as $bestand): ?>

                            <?php if ($system == 'tidemann' || $system == 'bibliofil'): ?>

                                <?= $bestand['bibnavn'] ?>
                                <?php if (isset($bestand['b'])): ?>
                                    / <?= $bestand['b'] ?>
                                <?php endif; ?>
                                <?php if (isset($bestand['c'])): ?>
                                    / <?= $bestand['c'] ?>
                                <?php endif; ?>
                                <?php
                                    if ((!isset($bestand['h'])) || (!isset($bestand['f']))) { // sett til ukjent hvis ikke satt
                                        $bestand['h'] = "1";
                                        $bestand['f'] = "-1";
                                    }
                                ?>
                                : <strong><?= bestandsinfo($bestand['h'], $bestand['f']) /* status, restriction */ ?></strong>
                                <?php if (($bestand['h'] == "4") || ($bestand['h'] == "5")): /* UTLÅNT */ ?>
                                    <?php setlocale (LC_TIME , "nb_NO"); // norsk dato ?>
                                    til <?= strftime("%e. %B %G" , strtotime($bestand['y'])) ?>
                                <?php endif; ?>

                            <?php elseif ($system == 'bibsys'): ?>

                                <?php if ($bestand->collection == "NB/DIG nbdigi"): ?>
                                    Boken er tilgjengelig digitalt. Klikk p&aring; knappen "Les online" for &aring; lese den!
                                <?php else: ?>
                                    <?= $bestand->institution ?>
                                    <?php if (isset($bestand->collection)): ?>
                                        / <?= $bestand->collection ?>
                                    <?php endif; ?>
                                    <?php if (isset($bestand->callnumber)): ?>
                                        / <?= $bestand->callnumber ?>
                                    <?php endif; ?>
                                    : <strong><?= bestandsinfo($bestand->circulationStatus, $bestand->useRestriction) ?></strong>
                                    <?php if (($bestand->circulationStatus == "4") || ($bestand->circulationStatus == "5")): /* UTLÅNT */ ?>
                                        <?php setlocale (LC_TIME , "nb_NO"); /* norsk dato */ ?>
                                        til <?= strftime("%e. %B %G" , strtotime($bestand['y'])) ?>
                                    <?php endif; ?>

                                <?php endif; ?>

                            <?php endif; ?>
                            <br>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div><!-- /#tab1 -->



                <div id="tab2" class="tab">
                    <?php if (isset($treff['beskrivelse']) && trim($treff['beskrivelse']) != ''): ?>
                        <p>
                            <?= $treff['beskrivelse'] ?>
                        </p>
                    <?php endif; ?>
                    <p>
                        <?php if (isset($treff['omfang']) && trim($treff['omfang']) != ''): ?>
                            <strong>Omfang:</strong> <?= $treff['omfang'] ?><br>
                        <?php endif; ?>

                        <?php if ($system == 'bibsys'): ?>
                            <?php if (isset($treff['medarbeidere'])): ?>
                                <strong>Medvirkende:</strong> <?= (is_array($treff['medarbeidere']) ? implode (". ", $treff['medarbeidere']) : $treff['medarbeidere']) ?><br>
                            <?php endif; ?>
                            <?php if (isset($treff['generellnote'])): ?>
                                <strong>Generell note:</strong> <?= (is_array($treff['generellnote']) ? implode (". ", $treff['generellnote']) : $treff['generellnote']) ?><br>
                            <?php endif; ?>
                        <?php endif; ?>
                    </p>
                </div><!-- /#tab2 -->



                <div id="tab3" class="tab">
                    <p>
                        <?php if (isset($treff['originaltittel'])): ?>
                            <strong>Originaltittel:</strong> <?= $treff['originaltittel'] ?><br>
                        <?php endif; ?>
                        <?php if (is_array($treff['dewey'])): ?>
                            <strong>Dewey : </strong><?= implode (" / " , $treff['dewey']) ?><br>
                        <?php endif; ?>

                        <?php if ($system == 'tidemann' || $system == 'bibliofil'): ?>
                            <?php if (isset($treff['generellnote'])): ?>
                                <strong>Generell note:</strong> <?= (is_array($treff['generellnote']) ? implode (". ", $treff['generellnote']) : $treff['generellnote']) ?><br>
                            <?php endif; ?>
                            <?php if (isset($treff['innholdsnote'])): ?>
                                <strong>Innholdsnote:</strong> <?= (is_array($treff['innholdsnote']) ? implode (". ", $treff['innholdsnote']) : $treff['innholdsnote']) ?><br>
                            <?php endif; ?>
                            <?php if (isset($treff['medarbeidere'])): ?>
                                <strong>Medvirkende:</strong> <?= (is_array($treff['medarbeidere']) ? implode (". ", $treff['medarbeidere']) : $treff['medarbeidere']) ?><br>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php if (isset($treff['titler'])): ?>
                            <strong>Tittelinformasjon:</strong> <?= (is_array($treff['titler']) ? implode (" ; ", $treff['titler']) : $treff['titler']) ?><br>
                        <?php endif; ?>
                        <?php if (isset($treff['emneord'])): ?>
                            <strong>Emneord:</strong> <?= (is_array($treff['emneord']) ? implode (" ; ", $treff['emneord']) : $treff['emneord']) ?><br>
                        <?php endif; ?>
                    </p>
                </div><!-- /#tab3 -->

            </div><!-- .tab-content -->
        </div><!-- .tabs -->

    <?php endif; ?>

    <br style="clear: both;">
</div>
