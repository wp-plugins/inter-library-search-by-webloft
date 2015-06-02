<!DOCTYPE html>
<html lang="no">

    <head>
        <meta charset="utf-8">
        <title>Treffliste</title>
        <link href="//fonts.googleapis.com/css?family=Muli" rel="stylesheet" type="text/css">
        <link rel="stylesheet" href="css/wl-ils.css">
        <script type="text/javascript" src="js/wl-ils.js"></script>
    </head>

    <body onLoad="hidereglitreframeLoading();">

        <div id="divreglitreframeLoading" style="text-align: center; margin-top: 20px;">
            <img style="border: none; box-shadow: none;" src="icons/zzz.png" alt="Laster..." />
        </div>

<?php if ($reglitre_debug == 1): ?>
        <span style="color: red; font-family: tahoma;"><br>
            Søk etter : <?= $sokeord ?><br>
            URL : <?= $url ?><br>
            Antall treff : <?= $antallfunnet ?><br>
            Bibliotek : <?= $mittbiblioteknavn ?><br>
            System : <?= $mittsystem ?><br>
            Omslag fra Bokkilden : <?= ($omslagbokkilden == "1" ? 'JA' : 'NEI') ?><br>
            Omslag fra NB : <?= ($omslagnb == "1" ? 'JA' : 'NEI' ) ?><br>
            Bestand fra Bibsys : <?= ($bibsysbestand == "1" ? 'JA' : 'NEI' ) ?><br>
            URL til denne iframe : <?= $_SERVER['REQUEST_URI'] ?><br>
			Hente treff fra Bokhylla : <?= ($treffbokhylla == "1" ? 'JA' : 'NEI' ) ?><br>
        </span>
<?php endif; ?>


<?php if (count($results)): ?>

        <div id="divreglitreframeFrameHolder" style="display:block;">
            
                <?php include 'results-pager.php'; ?>

		<?php
		if ($treffbokhylla == 1) { // Bare hvis dette er valgt i options
			$dobokhylla = (int) $_REQUEST['dobokhylla'];
			if ($dobokhylla == 1) {
				$switchlink = str_replace ("dobokhylla=1" , "dobokhylla=0", $_SERVER['REQUEST_URI']);
				$switchlink .= "&posisjon=1";
				echo "<button class=\"bigfat\" onclick=\"showreglitreframeLoading();location.href='" . $switchlink . "'\">G&aring; tilbake til katalogen</button>";
			} else {
				$switchlink = str_replace ("dobokhylla=0" , "dobokhylla=1", $_SERVER['REQUEST_URI']);
				$switchlink .= "&posisjon=1";
				echo "<button class=\"bigfat\" onclick=\"showreglitreframeLoading();location.href='" . $switchlink . "'\">S&oslash;k i Bokhylla i stedet!</button>";
			}
		}	
		?>

                <ul class="ils-results">

<?php foreach ($results as $result): ?>
                    <li>
<?php if ($hamedbilder == "1"): /* skal vi egentlig vise bilder i det hele tatt, sånn i følge innstillingene? */ ?>
                        <div class="omslag">
                            <a target="_blank" href="<?= $result['url'] ?>">
                                <img src="<?= $result['omslag'] ?>" alt="<?= $result['tittel'] ?>" />
                            </a>
                        </div>
<?php endif; ?>
<?php if (($mittsystem != 'koha') && ($mittsystem != 'tidemann')): /* koha og tidemann har ikke materialtype, da dropper vi denne */ ?>
                        <div class="material">
                            <img class="materialtype" src="icons/<?= $result['materialtype'] ?>.png" alt="<?= $result['materialtype'] ?>" /><br>
                            <span class="materialtype"><?= $result['materialtype'] ?></span>
                            <span class="online-bestand">
                                <?php if ($result['fulltekst']): ?>
                                    <br><br>
                                    <a class="read-online" title="Les online!" target="_blank" href="<?= $result['fulltekst'] ?>">
                                        Online
                                    </a>
                                <?php endif; ?>
                                <?php 
								if (isset($result['bestand'])) {
									echo $result['bestand'];
								}
								?>
                            </span>
                        </div>
<?php endif; ?>
                        <h3>
                            <a target="_blank" href="<?= $result['url'] ?>">
                                <?= $result['tittel'] ?>
                                <?php if ($result['aar'] !== false): ?>
                                    (<?= $result['aar'] ?>)
                                <?php endif; ?>
                            </a>
                        </h3>
						<div class="ansvar"><?= $result['opphav'] ?></div>	
						<p>
						<?php
						$tekstbody[] = trim($result['description']);
						$tekstbody[] = trim($result['utdrag']);
						$tekstbody[] = trim($result['titteloriginal']);
						$tekstbody[] = trim($result['isbn']);
						$tekstbody[] = trim($result['omfang']);
						$tekstbody[] = trim($result['dewey']);
						foreach ($tekstbody as $onepiece) {
							if (trim($onepiece) != '') {
								echo $onepiece . "<br>";
							}
						}
						unset ($tekstbody);
						?>
                        </p>

                        <div class="status">
							<?php if ($result['status'] == 'ledig'): ?>
                            	Ledig <div class="green dot"></div>
							<?php elseif ($result['status'] == 'ledig'): ?>
                            	Utlånt e.l. <div class="orange dot"></div>
							<?php elseif ($result['status'] == 'bokhylla'): ?>
                            	Online i Bokhylla <div class="green dot"></div>
							<?php elseif ($result['status'] == 'ikke-ledig'): ?>
                            	Ikke ledig <div class="red dot"></div>
							<?php else: ?>
                            	Uklar bestand <div class="orange dot"></div>
							<?php endif; ?>

                        </div>

                        <div style="clear:both;"></div>
                    </li>
<?php endforeach; ?>

                </ul>

                <div class="reglitre_results_header">
                    <?php include 'results-pager.php'; ?>
                </div>
            </div>

<?php else: ?>

        Ingen treff!

<?php endif; ?>

    </body>
</html>


