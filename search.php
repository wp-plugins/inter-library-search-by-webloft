<?php

$reglitre_debug = 0; // Sett til 1 for debug

/*
// turn on for debug
ini_set('display_startup_errors',1);
ini_set('display_errors',1);
error_reporting(-1);
*/


/*
URL til testdata som funker
http://sru.bibsys.no/search/biblioholdings?version=1.2&operation=searchRetrieve&startRecord=1&maximumRecords=10&query=ibsen&recordSchema=marcxchange

Tilgjengelighet i Bibsys!
http://discovery.bibsys.no/tag/json/
http://www.bibsys.no/files/out/biblev/utlaanstatus-marc21.pdf - forklarer kodene

CURL dokumentasjon: 
http://semlabs.co.uk/journal/object-oriented-curl-class-with-multi-threading


*/

function bibnr_to_name($bibnr)
{
	include("serverliste.php");
	foreach ($bibliotek as $ettbibliotek) {
		$temp = explode("|x|", $ettbibliotek);
		if ($temp[1] == $bibnr) { // hvis nummer stemmer
			return ($temp[0]); // returner navn
		}
	}
	return false;
}


$omslagsserver = "http://bokforsider.webloft.no";

$time_start = microtime(true);

require("includes/systemer.php"); // forskjellige bib.systemers måte å søke på
require("includes/functions.php"); // funksjoner vi har bruk for

$mittbibliotek   = stripslashes(strip_tags($_REQUEST['mittbibliotek']));
if (isset($_REQUEST['enkeltposturl'])) {
	$enkeltposturl   = base64_decode(urldecode($_REQUEST['enkeltposturl']));
}
$omslagbokkilden = stripslashes(strip_tags($_REQUEST['omslagbokkilden']));
$omslagnb        = stripslashes(strip_tags($_REQUEST['omslagnb']));
$hamedbilder     = stripslashes(strip_tags($_REQUEST['hamedbilder']));
$bibsysbestand   = stripslashes(strip_tags($_REQUEST['bibsysbestand']));
$sokeord         = trim(stripslashes(strip_tags($_REQUEST['s'])));
if (isset($_REQUEST['posisjon'])) {
	$posisjon = (int) ($_REQUEST['posisjon']);
} else {
	$posisjon = 1;
}

if (isset($_REQUEST['makstreff'])) {
	$makstreff = (int) ($_REQUEST['makstreff']);
} else {
	$makstreff = 10; // default treffperside = 10
}

$qsokeord = $sokeord;

// Finne server og biblioteksystem ut fra libnr.

include("serverliste.php");
foreach ($bibliotek as $ettbibliotek) {
	$temp = explode("|x|", $ettbibliotek);
	if ($temp[1] == $mittbibliotek) {
		$mittbiblioteknavn = $temp[0];
		$minbibkode        = $temp[1];
		$minserver         = $temp[2];
		$mittsystem        = $temp[3];
		$minavdkode        = $temp[4];
	}
}

?>

<!doctype html>

<html lang="no">
<head>
  <meta charset="utf-8">

  <title>Treffliste</title>

<link href='http://fonts.googleapis.com/css?family=Muli' rel='stylesheet' type='text/css'>
<link rel="stylesheet" href="results.css">
<script type="text/javascript" src="js/hideframeonload.js"></script>

</head>

<body onLoad="hidereglitreframeLoading();">
<div id="divreglitreframeLoading" style="position: absolute; top: 40px; left: 5px;">
<img style="border: none; box-shadow: none;" src="icons/zzz.gif" alt="Laster..." />
</div>

<?php

// Jukse til søkeord 

$sokeord = str_replace(", ", ",", $sokeord); // fjerne mellomrom i invertert form


if ($mittsystem == 'bibsys') { // frasesøk i Bibsys
	$sokeord = str_replace(" ", "+", $sokeord); // Riktig dette
	if (stristr($sokeord, "\"")) {
		$sokeord = str_replace("\"", "", $sokeord); // fjerne anførsel
		$sokeord = "[" . $sokeord . "]"; //sette klamme i stedet, da kan vi gjøre frasesøk
		$sokeord = urlencode($sokeord);
	}
}

if ($mittsystem == 'bibliofil') { // frasesøk i Bibliofil
	if (stristr($sokeord, "\"")) {
		$sokeord = str_replace("\"", "", $sokeord); // fjerne anførsel
	}
	$sokeord = str_replace(" ", "+AND+", trim($sokeord)); // Dette er semi-frasesøk
	$sokeord = str_replace(" ", "+", trim($sokeord)); // kan ikke ha mellomrom i URL
}

if ($mittsystem == 'koha') { // frasesøk i Koha
	if (stristr($sokeord, "\"")) {
		$sokeord   = str_replace("\"", "", $sokeord); // fjerne anførsel
		$kohafrase = 1; // frasesøk aktivt - se lenger ned når URL defineres.
	}
	$sokeord = urlencode($sokeord);
}

// This is where it happens

// HTML template for one item

$singlehtml = '<tr>' . "\n";
$singlehtml .= '<td class="row-pendelString">' . "\n";
if ($hamedbilder == "1") { // skal vi egentlig vise bilder i det hele tatt, sånn i følge innstillingene?
	$singlehtml .= '<img class="omslag" src="omslagString" alt="tittelString" />' . "\n";
}
$singlehtml .= '<h3><a target="_blank" href="urlString">tittelString</a> (aarString)</h3>' . "\n";
$singlehtml .= '<span class="opphav">opphavString</span>' . "\n";
$singlehtml .= '<p>descriptionString</p>';
$singlehtml .= '<p>' . "\n";
$singlehtml .= 'titteloriginalString' . "\n";
$singlehtml .= 'isbnString' . "\n";
$singlehtml .= 'omfangString' . "\n";
$singlehtml .= 'deweyString' . "\n";
$singlehtml .= '</p>' . "\n";
$singlehtml .= '</td>' . "\n";
$singlehtml .= '<td class="row-pendelString" style="text-align: center;">' . "\n";
if ($mittsystem != 'koha') { // koha har ikke materialtype, da dropper vi denne 
	$singlehtml .= '<img class="materialtype" src="icons/materialtypeString.png" alt="materialtypeString" /><br>' . "\n";
	$singlehtml .= '<span class="materialtype">materialtypeString</span>';
}
$singlehtml .= 'onlineString';
$singlehtml .= 'bestandString';
$singlehtml .= '</td></tr>' . "\n\n";

// OK, skru sammen URL for søk

if ($mittsystem == 'bibliofil') {

	$url          = $minserver . "?version=1.2&operation=searchRetrieve&maximumRecords=" . $makstreff . "&query=" . $sokeord;
	$treffliste   = bibliofil_sok($url, $posisjon);
	$antallfunnet = bibliofil_antalltreff($url);
}

if ($mittsystem == 'bibsys') {
	$url          = "http://sru.bibsys.no/search/biblio?operation=searchRetrieve&version=1.2&maximumRecords=" . $makstreff . "&query=" . $sokeord . "%20and%20bs.bibkode=" . $minbibkode;
	$treffliste   = bibsys_sok($url, $minbibkode, $bibsysbestand, $posisjon);
	$antallfunnet = bibsys_antalltreff($url, $minavdkode); 
}

if ($mittsystem == 'koha') {
	$url = $minserver . "/cgi-bin/koha/opac-search.pl?idx=kw&q=" . $sokeord . "&count=" . $makstreff . "&sort_by=relevance&format=rss2";
	if ((isset($kohafrase)) && ($kohafrase == 1)) {
		$url = str_replace("idx=kw", "idx=kw%2Cphr", $url); // goto frasesøk
	}
	$treffliste   = koha_sok($url, $posisjon);
	$antallfunnet = koha_antalltreff($url);
}


// FINNE OMSLAGSBILDER

// 1. Bokkilden
// 2. NB
// 3. osv


if ($antallfunnet > 0) { // kan være tom
	
	if ($hamedbilder == "1") { // bare hvis innstillingen "ha med bilder i det hele tatt" er satt
		
		foreach ($treffliste as $enkelttreff => &$treff) {
			
			// Det enkleste er å bruke vår egen server hvis vi har ISBN
			if ((isset($treff['isbn'])) && (!isset($treff['omslag']))) { // vi har ISBN men ikke omslag
				$tempisbn = str_replace(" ", "", $treff['isbn']); // fjerne mellomrom
				$tempisbn = str_replace("-", "", $treff['isbn']); // fjerne streker
				$omslag   = $omslagsserver . "/isbn/" . $tempisbn . ".jpg";
				if (url_exists($omslag)) { // hurra, ISBN-omslag finnes - da grabber vi lenke til fulltekst også
					$treff['omslag'] = $omslag;
					$tittelsok = "http://www.nb.no/services/search/v2/search?q=*&fq=isbn:%22" . $treff['isbn'] . "%22&fq=contentClasses:(public%20OR%20bokhylla)";
					$tybring   = get_content($tittelsok);
					$firsttry  = simplexml_load_string($tybring);
					foreach ($firsttry->entry as $item) {
						$namespaces = $item->getNameSpaces(true);
						$nb         = $item->children($namespaces['nb']); // alle som er nb:ditten og nb:datten
						$treff['fulltekst'] = "http://urn.nb.no/" . $nb->urn;
					}
				}
			}
			
			// Hvis denne innstillingen er slått på og vi fortsatt ikke har omslag
			
			if (($omslagbokkilden == "1") && (!isset($treff['omslag']))) {
				
				// Finne info fra Bokkilden
				
				// Hvis vi har ISBN
				
				if (!empty($treff['isbn'])) {
					$isbnsearch      = "http://partner.bokkilden.no/SamboWeb/partner.do?format=XML&uttrekk=5&ept=3&xslId=117&enkeltsok=" . $treff['isbn'];
					$panda           = get_content($isbnsearch);
					$firsttry        = simplexml_load_string($panda);
					$treff['omslag'] = $firsttry->Produkt->BildeURL;
					$treff['omslag'] = str_replace("&width=80", "", $treff['omslag']); // knegg, knegg
					if (!isset($treff['beskrivelse'])) {
						$treff['beskrivelse'] = (string)$firsttry->Produkt->Ingress;
					}
				}
			}
			// Siste forsøk: Søke i NB via URN , hvis omslag fra NB er slått på OG vi fortsatt ikke har omslag OG vi ikke har ISBN (har allerede søkt på ISBN)
			// Men det må være bøker (annet finnes jo ikke i NB)

			if (($omslagnb == "1") && (!isset($treff['omslag'])) && (!isset($treff['isbn'])) && ($treff['type'] == "bok")) {
				
				// Vi søker på tittel og ser hvilke URN-er vi får
				
				$tittelsok = "http://www.nb.no/services/search/v2/search?q=*&fq=title:%22" . urlencode($treff['tittel']) . "%22&fq=contentClasses:(public%20OR%20bokhylla)";
				$tybring   = get_content($tittelsok);
				$firsttry  = simplexml_load_string($tybring);
				foreach ($firsttry->entry as $item) {
					$namespaces = $item->getNameSpaces(true);
					$nb         = $item->children($namespaces['nb']); // alle som er nb:ditten og nb:datten
					$omslag     = $omslagsserver . "/urn/" . substr(($nb->urn), 8) . ".jpg";
					if ((url_exists($omslag)) && ($nb->urn != '')) {
						$treff['omslag']    = $omslag;
						$treff['fulltekst'] = "http://urn.nb.no/" . $nb->urn; // grabber lenke også med det samme
					}
				}
			} // slutt på hvis omslagnb er skrudd på
			
			
		} // slutt på foreach
		
	} // slutt på sjekk om "ha med bilder"-innstilling er satt
	
} // slutt på sjekk om antallfunnet > 0

//domp ($treffliste);

//**********************************************************************************
// Hvis vi skal vise poster på egne sider må vi fikse alle permalenkene
//**********************************************************************************
// Vi må gå gjennom 'HTTP_REFERER' for å finne enkeltposturl

if (stristr($_SERVER['HTTP_REFERER'] , "enkeltposturl")) { // Vi har det i referer, overstyrer den vi hadde
	$dump = stristr ($_SERVER['HTTP_REFERER'] , "enkeltposturl="); // fra enkelposturl og ut;
	if (stristr("&" , $dump)) { // flere vars?
		$dump = stristr ($dump , "&", TRUE) ; // fram til FØRSTE "&"
	} 
	if (stristr($dump, "&")) { // & må vi rett og slett bare fjerne - kan være noen etterpå også!
		$dump = stristr ($dump , "&" , TRUE); // fram til første &
	}
	$enkeltposturl = base64_decode(urldecode(str_replace ("enkeltposturl=" , "" , $dump)));
}


if ((isset($enkeltposturl)) && ($enkeltposturl != "")) { // det finnes en url til side hvor enkeltposter skal vises
	foreach ($treffliste as $mangetreff => &$etttreff) { // for hvert treff i trefflista
		$etttreff['biblioteksystem'] = $mittsystem;
		$treffinfo             = base64_encode(serialize($etttreff));
		if(stristr($enkeltposturl , "?")) { // Har allerede query variables
			$etttreff['permalink'] = $enkeltposturl . "&enkeltpostinfo=" . $treffinfo;
		} else { // Dette er den første
			$etttreff['permalink'] = $enkeltposturl . "?enkeltpostinfo=" . $treffinfo;
		}
	}
}

// DEBUG FØRST

$iframeurl = $_SERVER['REQUEST_URI'];

if ($reglitre_debug == 1) {
	echo '<span style="color: red; font-family: tahoma;"><br>';
	echo "Søk etter : " . $sokeord . "<br>\n";
	echo "URL : " . $url . "<br>";
	echo "Antall treff : " . $antallfunnet . "<br>";
	echo "Bibliotek : " . $mittbiblioteknavn . "<br>";
	echo "System : " . $mittsystem . "<br>";
	echo "Omslag fra Bokkilden : ";
	if ($omslagbokkilden == "1") {
		echo "JA";
	} else {
		echo "NEI";
	}
	echo "<br>";
	echo "Omslag fra NB : ";
	if ($omslagnb == "1") {
		echo "JA";
	} else {
		echo "NEI";
	}
	echo "<br>";
	echo "Bestand fra Bibsys : ";
	if ($bibsysbestand == "1") {
		echo "JA";
	} else {
		echo "NEI";
	}
	echo "<br>";
	echo "URL til denne iframe : " . $iframeurl . "<br>";
	echo "</span>";
}


if ($antallfunnet > 0) { // kan være tom
	
	// SKRIVE UT
	
	$pendel       = 0;
	$treffperside = $makstreff; // mindre forvirrende! (innstilling var før maks treff å hente, ble treff per side etter hvert)
	
	$viser = "<div class=\"viser_tekst\">\n";
	
	if ($antallfunnet >= $treffperside) {
		$forrigelink = $_SERVER['REQUEST_URI'] . "&posisjon=" . ($posisjon - $treffperside);
		$nestelink   = $_SERVER['REQUEST_URI'] . "&posisjon=" . ($posisjon + $treffperside);
		
		$forrigeposisjon = ($posisjon - $treffperside);
		$nesteposisjon   = ($posisjon + $treffperside - 1);
		if ($nesteposisjon > $antallfunnet) {
			$nesteposisjon = $antallfunnet;
		}
		
		$viser = "<div style=\"float: left; display: table-cell; vertical-align: bottom;\">\n";
		$viser .= "Viser treff " . $posisjon . "-" . $nesteposisjon . " av " . $antallfunnet . " ved søk etter '" . $qsokeord . "'";
		$viser .= "</div>\n";
		$viser .= "<div class=\"paginering\">\n";
		
		// Hva skal det stå på knappen?
		if (($nesteposisjon + $treffperside) > $antallfunnet) {
			$antalligjen = $antallfunnet - $nesteposisjon;
		} else {
			$antalligjen = $treffperside;
		}
		
		if ($forrigeposisjon >= 1) {
			//		$viser .= "<input type=\"button\" onClick=\"hidereglitreframeLoading();location.href='" . $forrigelink . "'\" value='Forrige " . $treffperside . "'>\n";
			$viser .= "<input type=\"button\" onClick=\"history.go(-1);\" value='&laquo;&nbsp;Forrige " . $treffperside . "'>\n";
			
		}
		if ($nesteposisjon < $antallfunnet) {
			$viser .= "<input type=\"button\" onClick=\"showreglitreframeLoading();location.href='" . $nestelink . "'\" value='Neste " . $antalligjen . "&nbsp;&raquo;'>\n";
		}
		$viser .= "</div>\n";
	} else {
		$viser = "<div style=\"float: left;\">\n";
		$viser .= "Viser treff 1-" . $antallfunnet . " ved søk etter '" . $qsokeord . "'<br>";
		$viser .= "</div>";
	}

	echo "<div id=\"divreglitreframeFrameHolder\" style=\"display:block\">";
	echo "<div class=\"reglitre_results\">\n";
	echo "<div class=\"reglitre_results_header\">" . $viser . "</div>";
	
	echo "<table>\n";

	foreach ($treffliste as $enkelttreff => &$treff) {

		$pendel = (1 - $pendel);
		
		// HER BEGYNNER REPLACE - HER STÅR DET SINGLEHTML I FØRSTE LINJE
		
		if (!empty($treff['omslag'])) {
			$htmlout = @str_replace('omslagString', $treff['omslag'], $singlehtml);
		} else {
			$htmlout = @str_replace('omslagString', 'icons/ikke_digital.png', $singlehtml);
		}
		$htmlout = @str_replace('tittelString', $treff['tittelinfo'], $htmlout);
		if ((isset($treff['utgittaar'])) && ($treff['utgittaar'] != '')) {
			$htmlout = @str_replace('aarString', $treff['utgittaar'], $htmlout);
		} else {
			$htmlout = @str_replace(' (aarString)', '', $htmlout);
		}
		$htmlout = @str_replace('urlString', $treff['permalink'], $htmlout);
		$htmlout = @str_replace('opphavString', $treff['opphav'], $htmlout);
		$htmlout = @str_replace('materialtypeString', $treff['type'], $htmlout);
		
		if (isset($treff['fulltekst'])) {
			// $htmlout = @str_replace('onlineString', "<br><br><a target=\"_blank\" href=\"" . $treff['fulltekst'] . "\"><img src=\"icons/online.png\" alt=\"Les online!\" /></a>", $htmlout);
			$htmlout = @str_replace('onlineString', "<br><br><a class=\"onlinelink\" target=\"_blank\" href=\"" . $treff['fulltekst'] . "\">Online!</a><br><br>", $htmlout);
		}

		if ((isset($treff['isbn'])) && (trim($treff['isbn']) != "")) {
			$altmedisbn = trim($treff['isbn']);
			if ((isset($treff['heftetbundet'])) && (trim($treff['heftetbundet']) != "")) {
				$altmedisbn .= " (" . $treff['heftetbundet'] . ")";
			}
			$htmlout = @str_replace('isbnString', "<strong>ISBN: </strong>" . $altmedisbn . "<br>\n", $htmlout);
		}

		if ((isset($treff['omfang'])) && (trim($treff['omfang']) != "")) {
			$htmlout = @str_replace('omfangString', "<strong>Omfang: </strong>" . $treff['omfang'] . "<br>\n", $htmlout);
		}

		if ((isset($treff['originaltittel'])) && (trim($treff['originaltittel']) != "")) {
			$htmlout = @str_replace('titteloriginalString', "<strong>Originaltittel: </strong>" . $treff['originaltittel'] . "<br>\n", $htmlout);
		}

		// Bestand Bibsys...
		// Men innstillingen må være aktivert!	
		
		if ($mittsystem == "bibsys") {		
			if ($bibsysbestand == 1) { 		// Hvis Bibsys-bibliotek og bestand er på
				if ((isset($treff['bestand']) && (is_array($treff['bestand'])))) {
					$tilgjengelig  = 0;
					$utlant        = 0;
					$utilgjengelig = 0;
					$begrenset     = 0;
					foreach ($treff['bestand'] as $bestand) {
						switch ($bestand->circulationStatus) {
							case "0":
								if (($bestand->useRestriction == "2") || ($bestand->useRestriction == "3") || ($bestand->useRestriction == "4") || ($bestand->useRestriction == "6")) {
									$begrenset++;
								} else {
									$tilgjengelig++;
								}
								break;
							case "4":
								$utlant++;
								break;
							default:
									$utilgjengelig++;
								break;
						}
					}
					
					$bestandhtml = "<br>\n";
					if ($tilgjengelig > 0) {
						$bestandhtml .= "<div class=\"tilgang_groenn tilgang_boks\">\n";
						$bestandhtml .= "Tilgjengelig&nbsp;:&nbsp;" . $tilgjengelig . "\n";
						$bestandhtml .= "</div>\n";
					}
					if (($begrenset + $utlant) > 0) {
						$bestandhtml .= "<div class=\"tilgang_orange tilgang_boks\">\n";
						$bestandhtml .= "Begrenset/utlånt&nbsp;:&nbsp;" . ($begrenset + $utlant) . "&nbsp;\n";
						$bestandhtml .= "</div>\n";
					}
					if ($utilgjengelig > 0) {
						$bestandhtml .= "<div class=\"tilgang_roed tilgang_boks\">\n";
						$bestandhtml .= "Utilgjengelig&nbsp;:&nbsp;" . $utilgjengelig . "\n";
						$bestandhtml .= "</div>\n";
					}
				} else { // vi har ikke bestandsinfo fordi bestand er ikke en array
					$bestandhtml = "<br>\n";
					$bestandhtml .= "<div class=\"tilgang_roed tilgang_boks\">\n";
					$bestandhtml .= "Utilgjengelig\n";
					$bestandhtml .= "</div>\n";
				}
			}	
		
		}
		
		// BESTAND I BIBLIOFIL
		
		// Finner vi alltid i 850 - men hvis ikke er det utilgjengelig
		
		/*
		i 850 finner vi:
		
		$a	Institution/location	Eiende bibliotek/avdeling
		$b	Sublocation/collection	Filial- avdelings- eller samlingskode
		$c	Shelving location	Hyllesignatur
		$f	Use restrictions	(Not in NORMARC)
		$h	Circulation status	(Not in NORMARC)
		$x	Date of circulation status	(Not in NORMARC)
		$y	Loan expiry date	(Not in NORMARC)
		
		*/
		
		if ($mittsystem == 'bibliofil') {
			$tilgjengelig  = 0;
			$utlant        = 0;
			$utilgjengelig = 0;
			$begrenset     = 0;
			$bestandhtml   = '';

//domp ($treff['bestand']);			
			if (is_array($treff['bestand'])) { // Bare hvis array
				foreach ($treff['bestand'] as $enkelteks) {
					@$status      = $enkelteks["h"];
					@$begrensning = $enkelteks	["f"];
					switch ($status) {
						case "0":
							if (($begrensning == "2") || ($begrensning == "3") || ($begrensning == "4") || ($begrensning == "6")) {
								$begrenset++;
							} else {
								$tilgjengelig++;
							}
							break;
						case "4":
							$utlant++;
							break;
						default:
							$utilgjengelig++;
							break;
					}
				}	
			}
			$bestandhtml = "<br>\n";
			if ($tilgjengelig > 0) {
				$bestandhtml .= "<div class=\"tilgang_groenn tilgang_boks\">\n";
				$bestandhtml .= "Tilgjengelig&nbsp;:&nbsp;" . $tilgjengelig . "\n";
				$bestandhtml .= "</div>\n";
			}
			if (($begrenset + $utlant) > 0) {
				$bestandhtml .= "<div class=\"tilgang_orange tilgang_boks\">\n";
				$bestandhtml .= "Begrenset/utlånt&nbsp;:&nbsp;" . ($begrenset + $utlant) . "&nbsp;\n";
				$bestandhtml .= "</div>\n";
			}
			if ($utilgjengelig > 0) {
				$bestandhtml .= "<div class=\"tilgang_roed tilgang_boks\">\n";
				$bestandhtml .= "Utilgjengelig&nbsp;:&nbsp;" . $utilgjengelig . "\n";
				$bestandhtml .= "</div>\n";
			}
		

		}
		
		// Så bytter vi ut hvis vi har noe
		
		if ((isset($bestandhtml)) && ($bestandhtml != '')) {
			$htmlout = @str_replace('bestandString', $bestandhtml, $htmlout);
		}
		
		$htmlout = @str_replace('pendelString', $pendel, $htmlout);
		$htmlout = @str_replace('descriptionString', trunc($treff['beskrivelse'], 40), $htmlout);

		// RYDD OPP I UBRUKTE STRENGER - FJERN DEM!!

		$htmlout = str_replace ("pendelString" , "" , $htmlout);
		$htmlout = str_replace ("omslagString" , "" , $htmlout);
		$htmlout = str_replace ("tittelString" , "" , $htmlout);
		$htmlout = str_replace ("urlString" , "" , $htmlout);
		$htmlout = str_replace ("aarString" , "" , $htmlout);
		$htmlout = str_replace ("opphavString" , "" , $htmlout);
		$htmlout = str_replace ("descriptionString" , "" , $htmlout);
		$htmlout = str_replace ("isbnString" , "" , $htmlout);
		$htmlout = str_replace ("omfangString" , "" , $htmlout);
		$htmlout = str_replace ("deweyString" , "" , $htmlout);
		$htmlout = str_replace ("materialtypeString" , "" , $htmlout);
		$htmlout = str_replace ("onlineString" , "" , $htmlout);
		$htmlout = str_replace ("bestandString" , "" , $htmlout);
		$htmlout = str_replace ("titteloriginalString" , "" , $htmlout);

		echo $htmlout;
	
	} // slutt på hvert treff
	echo "</table>\n";
	echo "<div class=\"reglitre_results_header\">" . $viser . "</div>";
	echo "</div>\n"; // slutt på reglitre_results
	
} else { // trefflisten var tom
	echo "<div class=\"reglitre_results\">\n";
	echo "Ingen treff!";
	echo "</div>";
}
echo "</div>\n"; // slutt på holder for spinner
?>

</body>
</html>
