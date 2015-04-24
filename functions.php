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

//***********************************************************
function rop ($whattorop) {
//***********************************************************
	echo "<h1>*" . $whattorop . "*</h1>";
}

//***********************************************************
function domp ($whattodomp) {
//***********************************************************
	echo "<pre>";
	print_r ($whattodomp);
	echo "</pre>";
}


// Tar ut første X ord av en streng
//***********************************************************
function trunc($phrase, $max_words) {
//***********************************************************
   $phrase_array = explode(' ',$phrase);
   if(count($phrase_array) > $max_words && $max_words > 0)
      $phrase = implode(' ',array_slice($phrase_array, 0, $max_words)).'...';
   return $phrase;
}


// Failsafe function to read file
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

//***********************************************************
function ilsdot($color) {
//***********************************************************

if ($color == "green") {

return ("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAwAAAAMCAIAAADZF8uwAAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH3wMeDCwqM0oFOgAAAR5JREFUGNNj/PvvLwMDAwMDw5c/X/e+PHjryz0OZnZDAV0bEQsGGGCBUN03pyzbvPzzrQ///zMwMDAwszGJOki1OdTbi1oxMDAw/v33N/9C5e4Vu77e/8SACoQtxTsjW1zFHZiOvz29Z/VuTBUMDAxvj7+s29P6+/8f5j8hbLd33fj/9z8DNvDj1XdNU01mFjPOb/c///+HXRETCxOXHj/Tny+////HrgICPv/+zMSrKsDIzIRHkSafGlOQmi8zJzMuFRwSXJ4SLkypSrGidpKMzIxYVIhzWgXZqvAoMv799/f2l7txG9JfHXz+9+tviDQjIyO7GKdWqP46q4XQwGRgYPj858uUO7M3ntzy+dZHZg5mIXOxHK3UEBk/iB4AvKVtzhOFGqoAAAAASUVORK5CYII=");

}

if ($color == "red") {

return ("data:image/png;base64,
iVBORw0KGgoAAAANSUhEUgAAAAwAAAAMCAIAAADZF8uwAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH3wMZDBIrmF8WaAAAARhJREFUGNN1kc8rw3EYx1/PZ7+OsqSIKFJabKZGDjtI+XFYyUFxEn+Cg5P4H+YgR5c5OUmcNaLIhOOssIsj24rv8zh8hzRel/fl9fT0fh4xM3wqNfLXFJ+JhIj1MNzPF0E/bGef7B7FJ/yRcJDRQVlfYWQAwMx0c1s7p5UhJa4klIQSVxnW5IKdXJmZ2OW9za5SegQHwg8eBJhIyWHWWe6I8gvIbwMIgMfFHacFR76AWYPhI3x4dnbjqNT4LtiIGa9VR3c7zv2nANLb4cikiYRA/zKE1mbSSSfzk6RiBMPgUb+SgYLSEmVxhq42MTMeyra8wfkttXdMQXBCtIm5cdlaA6T+lreq7R6QO6ZUJhwi0SdLGabG/MWfrKtsySWCQDcAAAAASUVORK5CYII=");

}

if ($color == "orange") {

return ("data:image/png;base64,
iVBORw0KGgoAAAANSUhEUgAAAAwAAAAMCAIAAADZF8uwAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH3wMZDBMQME/ODQAAAQFJREFUGNN90D9LQmEUgPHnvF7UgjAisAzEQQirwe3SUm2BU9AStEWfI6JvEDQINTRFH6Av0D8kApGoxhKyFBoyuhHd631Pw8Uh6vosZzi/sxxRGxLle7QutfsoTprxEjmXfk40tF6V613em6IW0ERKci6LO0zNA6I25Gxb61X/45XfpSZmWd4jv2C0U6dx8FcA3507TrewPSO3R+q1iat9xdOFoXVuNdZgezzXDMHXIAT4n0YzBccMRGNFI9Mr4iRjxXCW/JJhZo1J18g/IDk0wtw6owVRG/L2wMkGL7Ug6EVrI5h0RkqrVPb7zwR8T28O5f6YbpNEUrNlKW9SrEQ3P9hXXdA95EgIAAAAAElFTkSuQmCC");

}

}

//***********************************************************
function bestandsinfo ($status , $restriction) {
//***********************************************************
	switch ($status) {
		case "1":
			return "Ukjent status";
			break;
		case "2":
			return "I bestilling";
			break;
		case "3":
			return "Ukjent status, utilgjengelig";
			break;
		case "4":
			return "Utlånt";
			break;
		case "5":
			return "Utlånt";
			break;
		case "6":
			return "Under behandling";
			break;
		case "7":
			return "Innkalt";
			break;
		case "8":
			return "På vent";
			break;
		case "9":
			return "Venter på klargjøring";
			break;
		case "10":
			return "På vei mellom to bibliotek";
			break;
		case "11":
			return "Hevdet innlevert eller aldri lånt";
			break;
		case "12":
			return "Tapt";
			break;
		case "13":
			return "Savnet - vi leter";
			break;
		case "14":
			return "Ukjent status";
			break;
		case "15":
			return "Til innbinding";
			break;
		case "16":
			return "Til reparasjon";
			break;
		case "17":
			return "Venter på overføring";
			break;
		case "18":
			return "Purring sendt";
			break;
		case "19":
			return "Trukket tilbake";
			break;
		case "20":
			return "Ukjent status";
			break;
		case "21":
			return "Ukjent status";
			break;
		case "22":
			return "Skadet";
			break;
		case "23":
			return "Ikke i omløp";
			break;
		case "24":
			return "Annen status";
			break;
		case "0":
			switch ($restriction) {
				case "1":
					return "[Ikke til utlån]";
					break;
				case "2":
					return "Ledig [Til bruk i biblioteket]";
					break;
				case "3":
					return "Ledig [Dagslån]";
				case "4":
					return "Ledig [Til bruk på lesesal el.l.]";
					break;
				case "5":
					return "Ledig [Kan ikke fornyes]";
					break;				
				case "6":
					return "Ledig [Begrenset lånetid]";
					break;				
				case "8":
					return "Ledig [Utvidet lånetid]";
					break;
				case "9":
				case "10":
					return "Ledig";
					break;
				default:
					return "Ledig";
			}
		default:
			return "Ukjent status";
			break;
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

$params = utf8_decode($treff['tittelinfo']);
$params .= "|x|";
if ((isset($treff['beskrivelse'])) && (trim($treff['beskrivelse']) != "")) {		
	$params .= utf8_decode($treff['beskrivelse']);
} else {
	$params .= utf8_decode($treff['omfang']) . "  ";
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
		$params .= utf8_decode('<strong>Utgitt : </strong>' . $utgitt . "<br>");
		}
}
$params .= "|x|";
$params .= "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
$params .= "|x|";
$params .= $treff['omslag'];
$params .= "|x|";
$params .= utf8_decode($treff['opphav']);
$params .= "|x|";
$params .= $treff['isbn'];

$twitterdescription = utf8_decode(substr($treff['tittelinfo'], 0, 80));
if (strlen($treff['tittelinfo'] > 80)) {
	$twitterdescription .= "[...]";
}

$twitterdescription .= " (" . utf8_decode(substr($treff['opphav'], 0, 40));
if (strlen($treff['opphav']) > 40) {
	$twitterdescription .= "[...]";
}

if (!empty($treff['utgittaar'])) {
	$twitterdescription .= " - " . $treff['utgittaar'];
	}

$twitterdescription .= ")";

$twitterdescription = utf8_encode(html_entity_decode($twitterdescription));
$params = utf8_encode(html_entity_decode($params));
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

return ("data:image/png;base64,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");

} // end function


//***********************************************************
function facebook_ikon () { // lite Facebook-ikon
//***********************************************************

return ("data:image/png;base64,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");

} // end function 

?>
