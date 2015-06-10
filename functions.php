<?php

//***********************************************************
function bibnr_to_name($bibnr)
//***********************************************************
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

//***********************************************************
function url_exists($url) {
//***********************************************************
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_NOBODY, true);
    $result = curl_exec($curl);

    $ret = false;

    //if request did not fail
    if ($result !== false) {
        //if request was ok, check response code
        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($statusCode == 200) {
            $ret = true;
        }
    }

    curl_close($curl);

    return $ret;
}

if (!function_exists('domp')) {
	function domp ($whattodomp) {
		echo "<pre>";
		print_r ($whattodomp);
		echo "</pre>";
	}
}

if (!function_exists('rop')) {
	function rop ($whattorop) {
		echo "<h1>*" . $whattorop . "*</h1>";
	}
}

// Tar ut første X ord av en streng

if (!function_exists('trunc')) {
	//***********************************************************
	function trunc($phrase, $max_words) {
	//***********************************************************
	   $phrase_array = explode(' ',$phrase);
	   if(count($phrase_array) > $max_words && $max_words > 0)
	      $phrase = implode(' ',array_slice($phrase_array, 0, $max_words)).'...';
	   return $phrase;
	}
}

// Failsafe function to read file
if (!function_exists('get_content')) {
	//***********************************************************
	function get_content($url) { // Les inn en fil
	//***********************************************************
		$ch = curl_init();
	
		curl_setopt ($ch, CURLOPT_URL, $url);
		curl_setopt ($ch, CURLOPT_HEADER, 0);
	
		ob_start();
	
		curl_exec ($ch);
		curl_close ($ch);
		$string = ob_get_contents();
	
		ob_end_clean();
	
		return $string;
	}
}

//***********************************************************
function bestandsinfo ($status , $restriction) {
//***********************************************************
	switch ($status) {
		case "1": 	return "Ukjent status";
		case "2": 	return "I bestilling";
		case "3": 	return "Ukjent status, utilgjengelig";
		case "4": 	return "Utlånt";
		case "5": 	return "Utlånt";
		case "6": 	return "Under behandling";
		case "7": 	return "Innkalt";
		case "8": 	return "På vent";
		case "9": 	return "Venter på klargjøring";
		case "10": 	return "På vei mellom to bibliotek";
		case "11": 	return "Hevdet innlevert eller aldri lånt";
		case "12": 	return "Tapt";
		case "13": 	return "Savnet - vi leter";
		case "14": 	return "Ukjent status";
		case "15": 	return "Til innbinding";
		case "16": 	return "Til reparasjon";
		case "17": 	return "Venter på overføring";
		case "18": 	return "Purring sendt";
		case "19": 	return "Trukket tilbake";
		case "20": 	return "Ukjent status";
		case "21": 	return "Ukjent status";
		case "22": 	return "Skadet";
		case "23": 	return "Ikke i omløp";
		case "24": 	return "Annen status";
		case "0":
			switch ($restriction) {
				case "1": 	return "[Ikke til utlån]";
				case "2": 	return "Ledig [Til bruk i biblioteket]";
				case "3": 	return "Ledig [Dagslån]";
				case "4": 	return "Ledig [Til bruk på lesesal el.l.]";
				case "5": 	return "Ledig [Kan ikke fornyes]";
				case "6": 	return "Ledig [Begrenset lånetid]";
				case "8": 	return "Ledig [Utvidet lånetid]";
				case "9": 	return "Ledig";
				case "10": 	return "Ledig";
				default: 	return "Ledig";
			}
		default:	return "Ukjent status";
	}
}

//***********************************************************
function hent_enkeltpost ($bibkode , $bibtype , $postid) {
//***********************************************************

require_once("systemer.php");

include("serverliste.php");
foreach ($bibliotek as $ettbibliotek) {
	$temp = explode("|x|", $ettbibliotek);
	if ($temp[1] == $bibkode) {
		$mittbiblioteknavn = $temp[0];
		$minbibkode        = $temp[1];
		$minserver         = $temp[2];
		$mittsystem        = $temp[3];
		$minavdkode        = $temp[4];
	}
}

// Skal returnere $treff[] på samme måte, identisk som andre

// url til søk etter dokid i Bibsys:
//http://sru.bibsys.no/search/biblioholdings?operation=searchRetrieve&version=1.2&maximumRecords=10&query=bs.objektid=%22132220687%22

if (strtolower($bibtype) == "bibsys") {
	$url = 'http://sru.bibsys.no/search/biblioholdings?operation=searchRetrieve&version=1.2&maximumRecords=1&query=bs.objektid=%22' . $postid . '%22';
	$treff = bibsys_sok($url, $bibkode, 1, 1); // søkeurl, bibkode, hamedbestand , recPosition
	$treff[0]['biblioteksystem'] = "bibsys";
}

if (strtolower($bibtype) == "bibliofil") {
	$url = $minserver . '?version=1.2&operation=searchRetrieve&maximumRecords=10&query=dc.identifier=/bib.identifierAuthority=local%20' . $postid;
	$treff = bibliofil_sok($url, "1");
	$treff[0]['biblioteksystem'] = "bibliofil";
}

if (strtolower($bibtype) == "tidemann") {
	$url = $minserver . '?version=1.2&operation=searchRetrieve&maximumRecords=10&recordSchema=marcxchange&query=rec.identifier=' . $postid;
	$treff = tidemann_sok($url, "1");
	$treff[0]['biblioteksystem'] = "tidemann";
}

//		Denne URL-en søker etter post med dokid i 001:
// https://www.ringsaker.bib.no/cgi-bin/sru?version=1.2&operation=searchRetrieve&maximumRecords=10&query=dc.identifier=/bib.identifierAuthority=local%

return $treff[0];
} // end function

//***********************************************************
function hente_omslag ($treff) { // henter omslag for ett enkelt treff
//***********************************************************

$omslagsserver = "http://bokforsider.webloft.no";

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

			// Hvis vi fortsatt ikke har omslag

			if (!isset($treff['omslag'])) {

				// Finne info fra Bokkilden hvis vi har ISBN

				if (!empty($treff['isbn'])) {
					$isbnsearch = "http://partner.bokkilden.no/SamboWeb/partner.do?format=XML&uttrekk=5&ept=3&xslId=117&enkeltsok=" . $treff['isbn'];
					$panda = get_content($isbnsearch);
					$firsttry = simplexml_load_string($panda);
					$treff['omslag'] = $firsttry->Produkt->BildeURL;
					$treff['omslag'] = str_replace("&width=80", "", $treff['omslag']); // knegg, knegg
					if (!isset($treff['beskrivelse'])) {
						$treff['beskrivelse'] = (string)$firsttry->Produkt->Ingress;
					}
				}
			}
			// Siste forsøk: Søke i NB via URN , hvis vi fortsatt ikke har omslag OG vi ikke har ISBN (har allerede søkt på ISBN)
			// Men det må være bøker (annet finnes jo ikke i NB)

			if ((!isset($treff['omslag'])) && (!isset($treff['isbn'])) && ($treff['type'] == "bok")) {

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
			}

return ($treff);
} // end function


//***********************************************************
function krydre_some ($treff) { // tar et enkelt treff, legger til Twitter og Facebookinfo
//***********************************************************

// Facebook først

// params: 0: Tittel 1: Beskrivelse 2: enkeltpostlenke 3: bilde 4: Forfatter 5: ISBN
// adskilt med |x|

$params = $treff['tittelinfo'];
$params .= "|x|";
if ((isset($treff['beskrivelse'])) && (trim($treff['beskrivelse']) != "")) {
	$params .= $treff['beskrivelse'];
} else {
	$params .= $treff['omfang'] . "  ";
	$utgitt = '';
	if ((isset($treff['utgitthvem'])) && (trim($treff['utgitthvem']) != "")) {
		$utgitt = $treff['utgitthvem'];
	} else {
		$utgitt = "[s.n.]";
	}
	if ((isset($treff['utgitthvor'])) && (trim($treff['utgitthvor']) != "")) {
		$utgitt .= ", " . $treff['utgitthvor'];
	} else {
		$utgitt .= ", [s.l.]";
	}
	if ((isset($treff['utgittaar'])) && (trim($treff['utgittaar']) != "")) {
		$utgitt .= ", " . $treff['utgittaar'];
	}

	if ($utgitt != "") {
		$params .= '<strong>Utgitt : </strong>' . $utgitt . "<br>";
		}
}
$params .= "|x|";
$params .= "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
$params .= "|x|";
@$params .= $treff['omslag'];
$params .= "|x|";
@$params .= $treff['opphav'];
$params .= "|x|";
@$params .= $treff['isbn'];

$twitterdescription = substr($treff['tittelinfo'], 0, 80);
if (strlen($treff['tittelinfo'] > 80)) {
	$twitterdescription .= "[...]";
}

@$twitterdescription .= " (" . substr($treff['opphav'], 0, 40);
if (@strlen($treff['opphav']) > 40) {
	$twitterdescription .= "[...]";
}

if (!empty($treff['utgittaar'])) {
	$twitterdescription .= " - " . $treff['utgittaar'];
	}

$twitterdescription .= ")";

$twitterdescription = html_entity_decode($twitterdescription);
$params = html_entity_decode($params);
$params = base64_encode(urlencode($params));

$treff['twitter'] = $twitterdescription;
$treff['facebook'] = $params;

return ($treff);


/*

$gotourn = $fbdescription . "|x|" . "Delt via Bibliotekarens beste venn: http://www.bibvenn.no/nbsok" . "|x|" . $lenke . "|x|" . $fbsharethumb[$x];
$gotourn = base64_encode(urlencode($gotourn));

$niceandlang = "http://www.bibvenn.no/nbsok/gotourn.php?params=" . $gotourn;
$niceandshort = make_bitly_url($niceandlang,'sundaune','R_096021159a86478688c3b34a32de31c3','json');



<a target="_self" href="javascript:fbShare('<?php echo $niceandshort;?>', 700, 350)">
<img style="width: 50px; height: 21px;" src="/maler/g/litenface.png" alt="Facebook-deling" />
</a>

*/







} // end function





//***********************************************************
function twitter_ikon () { // lite Twitter-ikon
//***********************************************************

$litentwitt = plugins_url ("icons/twitter.png" , __FILE__);

return ($litentwitt);

} // end function


//***********************************************************
function facebook_ikon () { // lite Facebook-ikon
//***********************************************************

$litenfb = plugins_url ("icons/fb.png" , __FILE__);
return ($litenfb);

} // end function


//***********************************************************
function wptuts_add_color_picker( $hook ) { // color picker i settings
//***********************************************************


 
    if( is_admin() ) { 
     
        // Add the color picker css file       
        wp_enqueue_style( 'wp-color-picker' ); 
    }
}
