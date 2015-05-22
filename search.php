<?php

$reglitre_debug = 0; // Sett til 1 for debug


// turn on for debug
ini_set('display_startup_errors',1);
ini_set('display_errors',1);
error_reporting(-1);


/*
URL til testdata som funker
http://sru.bibsys.no/search/biblioholdings?version=1.2&operation=searchRetrieve&startRecord=1&maximumRecords=10&query=ibsen&recordSchema=marcxchange

Tilgjengelighet i Bibsys!
http://discovery.bibsys.no/tag/json/
http://www.bibsys.no/files/out/biblev/utlaanstatus-marc21.pdf - forklarer kodene

CURL dokumentasjon:
http://semlabs.co.uk/journal/object-oriented-curl-class-with-multi-threading


*/


$omslagsserver = "http://bokforsider.webloft.no";

$time_start = microtime(true);

require_once("systemer.php"); // forskjellige bib.systemers måte å søke på
require_once("functions.php"); // funksjoner vi har bruk for

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

if ($mittsystem == 'tidemann') { // frasesøk i Tidemann
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


// OK, skru sammen URL for søk

if ($mittsystem == 'tidemann') {

	$url          = $minserver . "?version=1.2&operation=searchRetrieve&maximumRecords=" . $makstreff . "&recordSchema=marcxchange&query=" . $sokeord;
	$treffliste   = tidemann_sok($url, $posisjon);
	$antallfunnet = tidemann_antalltreff($url);
}

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


//**********************************************************************************
// Hvis vi skal vise poster på egne sider må vi fikse alle permalenkene
//**********************************************************************************
// Vi må gå gjennom 'HTTP_REFERER' for å finne enkeltposturl

// OBS! Koha kan ikke bruke enkel, ny måte for vi har ikke unik post-ID som kan søkes opp

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
	if (is_array($treffliste) && count($treffliste)) {
		foreach ($treffliste as $mangetreff => &$etttreff) { // for hvert treff i trefflista
			$etttreff['biblioteksystem'] = $mittsystem;
			if ($mittsystem == "koha") { // Hvis koha - pøs all treffinfo inn i URL
				$treffinfo             = base64_encode(serialize($etttreff));
			} else { // men hvis ikke sender vi postID, bibtype, avdelingskode
				$enkelinfo['bibsystem'] = $mittsystem;
				$enkelinfo['postid'] = $etttreff['identifier'];
				$enkelinfo['bibkode'] = $minbibkode;
				$treffinfo             = base64_encode(serialize($enkelinfo));
			}

			if(stristr($enkeltposturl , "?")) { // Har allerede query variables
				$etttreff['permalink'] = $enkeltposturl . "&system=" . $mittsystem . "&enkeltpostinfo=" . $treffinfo;
			} else { // Dette er den første
				$etttreff['permalink'] = $enkeltposturl . "?system=" . $mittsystem . "&enkeltpostinfo=" . $treffinfo;
			}
		}
	}
}

$results = array();

if ($antallfunnet > 0) { // kan være tom

	$treffperside = $makstreff; // mindre forvirrende! (innstilling var før maks treff å hente, ble treff per side etter hvert)

	foreach ($treffliste as $enkelttreff => &$treff) {

		// Verdier for hvert treff, som skal lagres i $results og sendes videre til results.php-malen
		$data = array(
			'omslag' => (empty($treff['omslag']) ? 'icons/ikke_digital.png' : $treff['omslag']),
			'tittel' => $treff['tittelinfo'],
			'aar' => ((isset($treff['utgittaar'])) && ($treff['utgittaar'] != '') ? $treff['utgittaar'] : false),
			'url' => $treff['permalink'],
			'opphav' => (isset($treff['opphav']) ? $treff['opphav'] : ''),
			'materialtype' => $treff['type'],
			// Set empty default values to avoid 'undefined index' errors
			'isbn' => '',
			'dewey' => '',
			'omfang' => '',
			'titteloriginal' => '',
			'fulltekst' => ((isset($treff['fulltekst']) && ($treff['fulltekst'] != '')) ? $treff['fulltekst'] : false),
			'description' => (isset($treff['beskrivelse']) ? trunc($treff['beskrivelse'], 40) : ''),
			);

		if ((isset($treff['isbn'])) && (trim($treff['isbn']) != "")) {
			$altmedisbn = trim($treff['isbn']);
			if ((isset($treff['heftetbundet'])) && (trim($treff['heftetbundet']) != "")) {
				$altmedisbn .= " (" . $treff['heftetbundet'] . ")";
			}
			$data['isbn'] = "<strong>ISBN: </strong>" . $altmedisbn . "<br>\n";
		}

		if ((isset($treff['omfang'])) && (trim($treff['omfang']) != "")) {
			$data['omfang'] = "<strong>Omfang: </strong>" . $treff['omfang'] . "<br>\n";
		}

		if ((isset($treff['originaltittel'])) && (trim($treff['originaltittel']) != "")) {
			$data['titteloriginal'] = "<strong>Originaltittel: </strong>" . $treff['originaltittel'] . "<br>\n";
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
				}

			$totaleks = (int)$tilgjengelig + (int)$begrenset + (int)$utlant + (int)$utilgjengelig;

			$bestandhtml = "<br>\n";
			if ($tilgjengelig > 0) {
				$data['status'] = 'ledig';
				$bestandhtml .= "<div class=\"tilgang_boks\">";
				$bestandhtml .= "Ledig&nbsp;:&nbsp;" . $tilgjengelig . "<br>\n";
				$bestandhtml .= "<div class=\"green dot\"></div>";
				$bestandhtml .= "</div>\n";
			} elseif (($tilgjengelig == 0) && (($begrenset + $utlant) > 0)) {
				$data['status'] = 'utlant';
				$bestandhtml .= "<div class=\"tilgang_boks\">";
				$bestandhtml .= "Utlånt el.l.&nbsp;:&nbsp;" . ($begrenset + $utlant) . "<br>\n";
				$bestandhtml .= "<div class=\"orange dot\"></div>";
				$bestandhtml .= "</div>\n";
			} elseif ($utilgjengelig > 0) { // utilgjengelig
				$data['status'] = 'ikke-ledig';
				$bestandhtml .= "<div class=\"tilgang_boks\">";
				$bestandhtml .= "Ikke ledig&nbsp;:&nbsp;" . $utilgjengelig . "<br>\n";
				$bestandhtml .= "<div class=\"red dot\"></div>";
				$bestandhtml .= "</div>\n";
			} else {
				$data['status'] = 'uklar';
				$bestandhtml .= "<div class=\"tilgang_boks\">";
				$bestandhtml .= "Uklar bestand!<br>\n";
				$bestandhtml .= "<div class=\"orange dot\"></div>";
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

			$totaleks = (int)$tilgjengelig + (int)$begrenset + (int)$utlant + (int)$utilgjengelig;


			if ($tilgjengelig > 0) {
				$data['status'] = 'ledig';
				$bestandhtml .= "<div class=\"tilgang_boks\">";
				$bestandhtml .= "Ledig&nbsp;:&nbsp;" . $tilgjengelig . "<br>\n";
				$bestandhtml .= "<div class=\"green dot\"></div>";
				$bestandhtml .= "</div>\n";
			} elseif (($tilgjengelig == 0) && (($begrenset + $utlant) > 0)) {
				$data['status'] = 'utlant';
				$bestandhtml .= "<div class=\"tilgang_boks\">";
				$bestandhtml .= "Utlånt el.l.&nbsp;:&nbsp;" . ($begrenset + $utlant) . "<br>\n";
				$bestandhtml .= "<div class=\"orange dot\"></div>";
				$bestandhtml .= "</div>\n";
			} elseif ($utilgjengelig > 0) { // utilgjengelig
				$data['status'] = 'ikke-ledig';
				$bestandhtml .= "<div class=\"tilgang_boks\">";
				$bestandhtml .= "Ikke ledig&nbsp;:&nbsp;" . $utilgjengelig . "<br>\n";
				$bestandhtml .= "<div class=\"red dot\"></div>";
				$bestandhtml .= "</div>\n";
			} else {
				$data['status'] = 'uklar';
				$bestandhtml .= "<div class=\"tilgang_boks\">";
				$bestandhtml .= "Uklar bestand!<br>\n";
				$bestandhtml .= "<div class=\"orange dot\"></div>";
				$bestandhtml .= "</div>\n";
			}
		}

		// Så bytter vi ut hvis vi har noe

		if ((isset($bestandhtml)) && ($bestandhtml != '')) {
			$data['bestand'] = $bestandhtml;
		}

		if ((isset($treff['pdfutdrag'])) && ($treff['pdfutdrag'] != "")) {
			$utdraghtml = '[<a target="_blank" href="' . $treff['pdfutdrag'] . '"><strong>Les utdrag</strong></a>]' . "\n";
			$data['utdrag'] = $utdraghtml;
		} else {
			$data['utdrag'] = '';
		}


		// RYDD OPP I UBRUKTE STRENGER - FJERN DEM!!

		$results[] = $data;

	}
}
else
{
	// trefflisten var tom
	$results = array();
}

include dirname(__FILE__).'/templates/results.php';
