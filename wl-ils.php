<?php
/*
Plugin Name: ILS Search by Webloft
Plugin URI: http://www.webekspertene.no/
Description: Interlibrary search for your Wordpress site! NORWEGIAN: Setter inn s&oslash;kefelt som lar deg s&oslash;ke i mange forskjellige bibliotekssystemer.
Version: 2.0
Author: H&aring;kon Sundaune / Webekspertene
Author URI: http://www.webekspertene.no/
*/


// FIRST COMES THE SHORTCODE... EH, CODE!

function wl_ils_func($atts){

wp_enqueue_script('wl_ils-iframe-script', plugins_url( 'js/iframeheight.js', __FILE__ ), array('jquery') );
wp_enqueue_script('wl_ils-onload-script', plugins_url( 'js/hideonload.js', __FILE__ ), array('jquery') );

wp_enqueue_style( 'wl_ils', plugins_url( '/wl-ils.css', __FILE__ ), false, '1.0', 'all' );

extract(shortcode_atts(array(
	'mittbibliotek' => '2020000'	
   ), $atts));

$enkeltpost = get_option('wl_ils_option_enkeltpost' , '');
$bibsysbestand = get_option('wl_ils_option_bibsysbestand' , '0');
$enkeltbibsysbestand = get_option('wl_ils_option_bibsysbestand' , '0');
$standardbibliotek = get_option('wl_ils_option_mittbibliotek' , '0');
$omslagbokkilden = get_option('wl_ils_option_omslagbokkilden' , '0');
$omslagnb = get_option('wl_ils_option_omslagnb' , '0');
$hamedbilder = get_option('wl_ils_option_hamedbilder' , '1');
$makstreff = get_option('wl_ils_option_makstreff' , '25');
if (isset($_REQUEST['webloftsok_query'])) {
	$hamedsok = stripslashes(strip_tags($_REQUEST['webloftsok_query']));
} else { 
	$hamedsok = '';
}

if ($standardbibliotek == "0") { // Hjemmebibliotek ikke satt i Wordpress
	$brukbibliotek = $mittbibliotek;
} else { // Vi har en standard
	$brukbibliotek = $standardbibliotek;
}

// lage URL i tilfelle det er lenket direkte til søkeside

$frameurl = plugins_url('search.php' , __FILE__) . "?mittbibliotek=" . $brukbibliotek . "&omslagbokkilden=" . $omslagbokkilden . "&bibsysbestand=" . $bibsysbestand . "&omslagnb=" . $omslagnb . "&hamedbilder=" . $hamedbilder . "&makstreff=" . $makstreff . "&s=" . $hamedsok;

if ($hamedsok != '') {
	$framekode = " src=\"" . $frameurl . "\"";
} else {
	$framekode = '';
}

// Gjemmer iframe og viser spinner mens lasting

$out = "<div class=\"reglitre_skjema\">\n";
$out .= "<form onSubmit=\"showreglitreLoading();\" id=\"webloftform\" target=\"reglitre_treff_frame\" action=\"" . plugins_url('search.php' , __FILE__) . "\" method=\"GET\">\n";
$out .= "S&oslash;keord:&nbsp";
$out .= "<input type=\"text\" value=\"" . $hamedsok . "\" id=\"search\" name=\"s\" accept-charset=\"utf-8\" />";
$out .= "&nbsp;<input type=\"submit\" value=\"S&oslash;k\">\n";
$out .= "<input type=\"hidden\" name=\"mittbibliotek\" value=\"" . $brukbibliotek . "\" />\n";
$out .= "<input type=\"hidden\" name=\"omslagbokkilden\" value=\"" . $omslagbokkilden . "\" />\n";
$out .= "<input type=\"hidden\" name=\"omslagnb\" value=\"" . $omslagnb . "\" />\n";
$out .= "<input type=\"hidden\" name=\"hamedbilder\" value=\"" . $hamedbilder . "\" />\n";
$out .= "<input type=\"hidden\" name=\"makstreff\" value=\"" . $makstreff . "\" />\n";
$out .= "<input type=\"hidden\" name=\"bibsysbestand\" value=\"" . $bibsysbestand . "\" />\n";
if (trim($enkeltpost) != "") {
	$out .= "<input type=\"hidden\" name=\"enkeltposturl\" value=\"" . base64_encode(get_permalink($enkeltpost)) . "\" />\n";
}
$out .= "</form>";
$out .= "</div>";

$out .= '<div id="divreglitreLoading" style="text-align: center; margin-top: 20px;">';
$out .= '<img style="border: none; box-shadow: none;" src="' . plugins_url( 'icons/spinner.gif', __FILE__ ) . '" alt="Laster..." />';
$out .= '</div>';

$out .= '<div id="divreglitreFrameHolder" style="display:none">';
$out .= "<iframe" . $framekode . " name=\"reglitre_treff_frame\" onLoad=\"hidereglitreLoading();\" id=\"reglitre_treff_frame_id\" frameborder=\"0\" width=\"100%\">\n";
//$out .= "Nettleser st&oslash;tter ikke iframes. Kan ikke bruke katalogs&oslash;k.\n";
$out .= "</iframe>\n";

$out .= "<script type=\"text/javascript\" src=\"" . plugins_url( 'js/resizeiframe.js', __FILE__ ) . "\"></script>";

// DOKUMENTASJON: https://github.com/Sly777/Iframe-Height-Jquery-Plugin

return $out;

};
 
//*******************************************************************************************
// Andre kortkode: Viser enkeltpost på siden hvor kortkoden [enkeltpost] står
//*******************************************************************************************

function enkeltpost_func($atts) {

	wp_enqueue_style( 'wl_ils-enkeltpost', plugins_url( '/enkeltpost.css', __FILE__ ), false, '1.0', 'all' );
	wp_enqueue_script('wl_ils-tabs-script', plugins_url( 'js/tabs.js', __FILE__ ), array('jquery') );
	wp_enqueue_script('wl_ils-fbshare-script', plugins_url( 'js/fbShare.js', __FILE__ ), array('jquery') );

	require_once dirname(__FILE__) . '/functions.php';
	require_once dirname(__FILE__) . '/systemer.php';
	
	$info = stristr($_SERVER['REQUEST_URI'] , "enkeltpostinfo="); // fra og med "enkeltpost="
	if (stristr($info , "&")) { 
		$info = stristr($info , "&" , TRUE); // men bare fram til "&" hvis det finnes
	}
	$info = str_replace ("&" , "" , $info); // fjern &
	$info = str_replace ("enkeltpostinfo=" , "" , $info); // fjern det andre

	$system = stristr($_SERVER['REQUEST_URI'] , "system="); // fra og med "system="
	if (stristr($system , "&")) { 
		$system = stristr($system , "&" , TRUE); // men bare fram til "&" hvis det finnes
	}
	$system = str_replace ("&" , "" , $system); // fjern &
	$system = str_replace ("system=" , "" , $system); // fjern det andre
	$system = strtolower($system);

	if (isset($info)) {


//************** VISER ENKELPOST ***************

	if ($system == "koha") { // hvis Koha har vi fått all info i query string
		$treff = unserialize(base64_decode($info));
	} else { // Ikke koha, vi må gjøre oppslag
		$enkeltpostinfo = unserialize(base64_decode($info));
		$treff = hent_enkeltpost ($enkeltpostinfo['bibkode'], $system, $enkeltpostinfo['postid']);
		$treff = hente_omslag ($treff); // legger til omslag
		$treff = krydre_some ($treff); // legger til Twitt og face
	}	

	$postout = '<div class="enkeltpostvisning">' . "\n";

	// ************* BIBLIOFIL-VISNING ******************

	if ($treff['biblioteksystem'] == "bibliofil") {
		$postout .= '<div class="bildecontainer">' . "\n";
		if ((isset($treff['omslag'])) && ($treff['omslag'] != "")) { // omslag finnes
			$postout .= '<img src="' . $treff['omslag'] . '" alt="' . $treff['tittelinfo'] . '" />' . "\n";
		} else {
			$postout .= '<img src="' . plugins_url( 'icons/ikke_digital.jpg', __FILE__ ) . '" alt="' . $treff['tittelinfo'] . '" />' . "\n";
		}
		$postout .= "<br>\n";

		$postout .= '<a target="_blank" href="https://twitter.com/intent/tweet?url=' . urlencode(plugins_url( 'gotourn.php', __FILE__ ) . "?params=" . $treff['facebook']) . '&via=bibvenn&text=' . htmlspecialchars($treff['twitter']) . '"><img style="width: 20px; height: 20px;" src="' . twitter_ikon() . '" alt="Del på Twitter" /></a>';

		$postout .= "&nbsp;&nbsp;\n";

		$postout .= "<a target=\"_self\" href=\"javascript:fbShare('" . plugins_url( 'gotourn.php', __FILE__ ) . "?params=" . $treff['facebook'] . "', 700, 350)\"><img style=\"width: 50px; height: 21px;\" src=\"" . facebook_ikon() . "\" alt=\"Facebook-deling\" /></a>";



		$postout .= '</div>' . "\n";

		$postout .= '<div class="infocontainer">' . "\n";
		$postout .= '<h2>' . str_replace (": :" , ":" , $treff['tittelinfo']) . '</h2>' . "\n";
		$postout .= '<p>' . "\n";
		if ((isset ($treff['forfatter'])) && ($treff['forfatter'] != "")) {
			$postout .= '<strong>Forfatter : </strong>' . $treff['forfatter'] . '<br>' . "\n";
		}

		if ((isset ($treff['ansvarsangivelse'])) && ($treff['ansvarsangivelse'] != "")) {
			$postout .= '<strong>Opphav : </strong>' . $treff['ansvarsangivelse'] . '<br>' . "\n";
		}

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
			$postout .= '<strong>Utgitt : </strong>' . $utgitt . "<br>\n";
		}

		if ((isset($treff['isbn'])) && ($treff['isbn'] != "")) {
			$postout .= '<strong>ISBN : </strong>' . $treff['isbn'] . "<br>\n";
		}

		$postout .= '<br style="clear: both;">' . "\n";

		$ledige = 0;
		$uklar = 0;
		if (is_array($treff['bestand'])) {
			foreach ($treff['bestand'] as $bestand) { // Noen ledige?
				if ($bestand['h'] == "0") {
					$ledige++;
				}
			}
		} else {
			$uklar = 1; // bestand er ikke array, uklar bestandsinfo
		}
		if ($ledige > 0) {
			$postout .= '<img src="' . ilsdot("green") . '" alt="Ledig!" />&nbsp;Ledig!<br><br>' . "\n";
		} else {
			if ($uklar == 1) {
				$postout .= '<img src="' . ilsdot("red") . '" alt="Uklar bestand" />&nbsp;Uklar bestand - kontakt biblioteket!<br><br>' . "\n";
			} else {
				$postout .= '<img src="' . ilsdot("red") . '" alt="Ingen ledige!" />&nbsp;Ingen ledige...<br><br>' . "\n";
			}
		}

		if (isset($treff['fulltekst'])) { // finnes den på nett?
			$postout .= "<input class=\"onlineknapp\" type=\"button\" value=\"Les p&aring; nett\" onClick=\"location.href='" . $treff['fulltekst'] . "'\">\n";
		}
		$bestilleurl = str_replace ("websok?" , "mappami?jumpmode=reservering&" , $treff['permalink']); // oh, you clever
		if ($bestilleurl != "") {
			$postout .= "<input class=\"bestilleknapp\" type=\"button\" value=\"Bestille/reservere\" onClick=\"location.href='" . $bestilleurl . "'\">\n";
		}
		$bestilleurl = ''; // må rydde opp
		$uklar = ''; // må rydde opp
		$postout .= '</p>' . "\n";
		$postout .= '</div>' . "\n"; // slutt på infoboks

		// EKSPERIMENTELL TAB-LØSNING

		$postout .= '<div class="tabs">' . "\n";
		$postout .= '<ul class="tab-links" style="padding: 0;">' . "\n";
		$postout .= '<li class="active"><a href="#tab1">Eksemplarer</a></li>' . "\n";
		$postout .= '<li><a href="#tab2">Beskrivelse</a></li>' . "\n";
		$postout .= '<li><a href="#tab3">Flere opplysninger</a></li>' . "\n";
		$postout .= '</ul>' . "\n";
		 
		$postout .= '<div class="tab-content">' . "\n";
		$postout .= '<div id="tab1" class="tab active">' . "\n";
	
		if (is_array($treff['bestand'])) {
			foreach ($treff['bestand'] as $bestand) {
				$postout .= $bestand['bibnavn'];
				if (isset($bestand['b'])) {
					$postout .= '&nbsp/&nbsp' . $bestand['b'];
				}
				if (isset($bestand['c'])) {
					$postout .= '&nbsp/&nbsp' . $bestand['c'];
				}
				$postout .= ' : ';
				if ((!isset($bestand['h'])) || (!isset($bestand['f']))) { // sett til ukjent hvis ikke satt 
					$bestand['h'] = "1";
					$bestand['f'] = "-1";
				}
				$postout .= "<strong>" . bestandsinfo ($bestand['h'] , $bestand['f']) . "</strong>"; // status, restriction
				if (($bestand['h'] == "4") || ($bestand['h'] == "5")) { // UTLÅNT
					setlocale (LC_TIME , "nb_NO"); // norsk dato
					$postout .= " til " . strftime("%e. %B %G" , strtotime($bestand['y']));
				}
			$postout .= "<br>\n";
			}
		}
	
		$postout .= '</div>' . "\n";
 
		$postout .= '<div id="tab2" class="tab">' . "\n";
		if ((isset($treff['beskrivelse'])) && ($treff['beskrivelse'] != "")) {
			$postout .= '<p>' . $treff['beskrivelse'] . '</p>' . "\n";
		}

		if ((isset($treff['omfang'])) && (trim($treff['omfang']) != "")) {
			$postout .= '<strong>Omfang: </strong>' . $treff['omfang'] . "<br>\n";
		}

		$postout .= '</div>' . "\n";
 

		$postout .= '<div id="tab3" class="tab">' . "\n";
		$postout .= '<p>' . "\n";		

		if (isset($treff['originaltittel'])) {
			$postout .= "<strong>Originaltittel: </strong>" . $treff['originaltittel'] . "<br>\n";
		}
		
		if (is_array($treff['dewey'])) {
			$postout .= '<strong>Dewey : </strong>';
			$alledewey = implode (" / " , $treff['dewey']);
			$postout .= $alledewey . "<br>\n";
		}
		
		if (isset($treff['generellnote'])) {
			if (is_array($treff['generellnote'])) {
				$generellnote = implode (". " , $treff['generellnote']);
			} else {
				$generellnote = $treff['generellnote'];
			}
			$postout .= "<strong>Generell note: </strong>" . $generellnote . "<br>\n";
		}

		if (isset($treff['innholdsnote'])) {
			if (is_array($treff['innholdsnote'])) {
				$innholdsnote = implode (". " , $treff['innholdsnote']);
			} else {
				$innholdsnote = $treff['innholdsnote'];
			}
			$postout .= "<strong>Innholdsnote: </strong>" . $innholdsnote . "<br>\n";
		}


		if (isset($treff['medarbeidere'])) {
			if (is_array($treff['medarbeidere'])) {
				$medarbeidere = implode (". " , $treff['medarbeidere']);
			} else {
				$medarbeidere = $treff['medarbeidere'];
			}
			$postout .= "<strong>Medarbeidere: </strong>" . $medarbeidere . "<br>\n";
		}


		if (isset($treff['titler'])) {
			if (is_array($treff['titler'])) {
				$titler = implode (" ; " , $treff['titler']);
			} else {
				$titler = $treff['titler'];
			}
			$postout .= "<strong>Tittelinformasjon: </strong>" . $titler . "<br>\n";
		}


		if (isset($treff['emneord'])) {
			if (is_array($treff['emneord'])) {
				$emneord = implode (" ; " , $treff['emneord']);
			} else {
				$emneord = $treff['emneord'];
			}
			$postout .= "<strong>Emneord: </strong>" . $emneord . "<br>\n";
		}


		$postout .= '</p>' . "\n";
		$postout .= '</div>' . "\n";
 
		$postout .= '</div>' . "\n";
		$postout .= '</div>' . "\n";


	}


	// ************* BIBSYS-VISNING ******************

	if ($treff['biblioteksystem'] == "bibsys") {
		$postout .= '<div class="bildecontainer">' . "\n";
		if ((isset($treff['omslag'])) && ($treff['omslag'] != "")) { // omslag finnes
			$postout .= '<img src="' . $treff['omslag'] . '" alt="' . $treff['tittelinfo'] . '" />' . "\n";
		} else {
			$postout .= '<img src="' . plugins_url( 'icons/ikke_digital.jpg', __FILE__ ) . '" alt="' . $treff['tittelinfo'] . '" />' . "\n";
		}
		$postout .= "<br>\n";

		$postout .= '<a target="_blank" href="https://twitter.com/intent/tweet?url=' . urlencode(plugins_url( 'gotourn.php', __FILE__ ) . "?params=" . $treff['facebook']) . '&via=bibvenn&text=' . htmlspecialchars($treff['twitter']) . '"><img style="width: 20px; height: 20px;" src="' . twitter_ikon() . '" alt="Del på Twitter" /></a>';

		$postout .= "&nbsp;&nbsp;\n";

		$postout .= "<a target=\"_self\" href=\"javascript:fbShare('" . plugins_url( 'gotourn.php', __FILE__ ) . "?params=" . $treff['facebook'] . "', 700, 350)\"><img style=\"width: 50px; height: 21px;\" src=\"" . facebook_ikon() . "\" alt=\"Facebook-deling\" /></a>";

		$postout .= '</div>' . "\n";

		$postout .= '<div class="infocontainer">' . "\n";
		$postout .= '<h2>' . str_replace (": :" , ":", $treff['tittelinfo']) . '</h2>' . "\n";
		$postout .= '<p>' . "\n";
		if ((isset ($treff['forfatter'])) && ($treff['forfatter'] != "")) {
			$postout .= '<strong>Forfatter : </strong>' . $treff['forfatter'] . '<br>' . "\n";
		}
		
		if ((isset ($treff['ansvarsangivelse'])) && ($treff['ansvarsangivelse'] != "")) {
			$postout .= '<strong>Opphav : </strong>' . $treff['ansvarsangivelse'] . '<br>' . "\n";
		}

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
			$postout .= '<strong>Utgitt : </strong>' . $utgitt . "<br>\n";
		}

		if ((isset($treff['isbn'])) && ($treff['isbn'] != "")) {
			$postout .= '<strong>ISBN : </strong>' . $treff['isbn'] . "<br>\n";
		}

		if ((isset($treff['beskrivelse'])) && ($treff['beskrivelse'] != "")) {
			$postout .= $treff['beskrivelse'] . '<br>' . "\n";
		}

		$postout .= '<br style="clear: both;">' . "\n";
		
		$ledige = 0;
		if ((isset($treff['bestand'])) && (is_array($treff['bestand']))) {
			foreach ($treff['bestand'] as $bestand) { // Noen ledige?
				if ($bestand->circulationStatus == "0") {
					$ledige++;
				}
			}
		}
		
		$bibsysbestand = get_option('wl_ils_option_bibsysbestand' , '0');
		if ($bibsysbestand == "1") { // bare hvis hake for bestand i Bibsys er valgt!
			if ($ledige > 0) {
				$postout .= '<img src="' . ilsdot("green") . '" alt="Ledig!" />&nbsp;Ledig!<br><br>' . "\n";
			} else {
				$postout .= '<img src="' . ilsdot("red") . '" alt="Ingen ledige!" />&nbsp;Ingen ledige...<br><br>' . "\n";
			}
		}	
		
		if (isset($treff['fulltekst'])) { // finnes den på nett?
			$postout .= "<input class=\"onlineknapp\" type=\"button\" value=\"Les p&aring; nett\" onClick=\"location.href='" . $treff['fulltekst'] . "'\">\n";
		}
		
		$bestilleurl = str_replace ("show" , "acquire" , $treff['permalink']); // oh, you clever AGAIN!!
		$postout .= "<input class=\"bestilleknapp\" type=\"button\" value=\"Bestille/reservere\" onClick=\"location.href='" . $bestilleurl . "'\">\n";
		$bestilleurl = ''; // må rydde opp
		$postout .= '</p>' . "\n";
		$postout .= '</div>' . "\n"; // slutt på infoboks
		
		// EKSPERIMENTELL TAB-LØSNING

		$postout .= '<div class="tabs">' . "\n";
		$postout .= '<ul class="tab-links" style="padding: 0;">' . "\n";
		$postout .= '<li class="active"><a href="#tab1">Eksemplarer</a></li>' . "\n";
		$postout .= '<li><a href="#tab2">Beskrivelse</a></li>' . "\n";
		$postout .= '<li><a href="#tab3">Flere opplysninger</a></li>' . "\n";
		$postout .= '</ul>' . "\n";
		 
		$postout .= '<div class="tab-content">' . "\n";
		$postout .= '<div id="tab1" class="tab active">' . "\n";
		$postout .= '<p>' . "\n";
	
		if ((isset($treff['bestand'])) && (is_array($treff['bestand']))) { // bare hvis vi har bestandinfo
			foreach ($treff['bestand'] as $bestand) {
				if ($bestand->collection == "NB/DIG nbdigi") {
					$postout .= 'Boken er tilgjengelig digitalt. Klikk p&aring; knappen "Les online" for &aring; lese den!';
				} else {
					$postout .= $bestand->institution;
					if (isset($bestand->collection)) {
						$postout .= '&nbsp/&nbsp' . $bestand->collection;
					}
					if (isset($bestand->callnumber)) {
						$postout .= '&nbsp/&nbsp' . $bestand->callnumber;
					}
					$postout .= ' : ';
					$postout .= "<strong>" . bestandsinfo ($bestand->circulationStatus , $bestand->useRestriction) . "</strong>"; // status, restriction
					if (($bestand->circulationStatus == "4") || ($bestand->circulationStatus == "5")) { // UTLÅNT
						setlocale (LC_TIME , "nb_NO"); // norsk dato
						$postout .= " til " . strftime("%e. %B %G" , strtotime($bestand['y']));
					}
				}	
			$postout .= "<br>\n";
			}
		$postout .= '</p>' . "\n";
		}

		$postout .= '</div>' . "\n";
 
		$postout .= '<div id="tab2" class="tab">' . "\n";
		$postout .= '<p>' . "\n";

		if ((isset($treff['beskrivelse'])) && ($treff['beskrivelse'] != "")) {
			$postout .= $treff['beskrivelse'] . '<br>' . "\n";
		}


		if ((isset($treff['omfang'])) && (trim($treff['omfang']) != "")) {
			$postout .= '<strong>Omfang: </strong>' . $treff['omfang'] . "<br>\n";
		}

		if (isset($treff['medarbeidere'])) {
			if (is_array($treff['medarbeidere'])) {
				$medarbeidere = implode (". " , $treff['medarbeidere']);
			} else {
				$medarbeidere = $treff['medarbeidere'];
			}
			$postout .= "<strong>Medarbeidere: </strong>" . $medarbeidere . "<br>\n";
		}

		if (isset($treff['generellnote'])) {
			if (is_array($treff['generellnote'])) {
				$generellnote = implode (". " , $treff['generellnote']);
			} else {
				$generellnote = $treff['generellnote'];
			}
			$postout .= "<strong>Generell note: </strong>" . $generellnote . "<br>\n";
		}

		$postout .= '</p>' . "\n";
		$postout .= '</div>' . "\n";
 

		$postout .= '<div id="tab3" class="tab">' . "\n";
		$postout .= '<p>' . "\n";	

		if (isset($treff['originaltittel'])) {
			$postout .= "<strong>Originaltittel: </strong>" . $treff['originaltittel'] . "<br>\n";
		}		
		
		if (is_array($treff['dewey'])) {
			$postout .= '<strong>Dewey : </strong>';
			$alledewey = implode (" / " , $treff['dewey']);
			$postout .= $alledewey . "<br>\n";
		}


		if (isset($treff['titler'])) {
			if (is_array($treff['titler'])) {
				$titler = implode (" ; " , $treff['titler']);
			} else {
				$titler = $treff['titler'];
			}
			$postout .= "<strong>Tittelinformasjon: </strong>" . $titler . "<br>\n";
		}


		if (isset($treff['emneord'])) {
			if (is_array($treff['emneord'])) {
				$emneord = implode (" ; " , $treff['emneord']);
			} else {
				$emneord = $treff['emneord'];
			}
			$postout .= "<strong>Emneord: </strong>" . $emneord . "<br>\n";
		}

		$postout .= '</p>' . "\n";
		$postout .= '</div>' . "\n";
 
		$postout .= '</div>' . "\n";
		
		
		
		

	}

	// ************* KOHA-VISNING ******************

	if ($treff['biblioteksystem'] == "koha") {
		$postout .= '<div class="bildecontainer">' . "\n";
		if ((isset($treff['omslag'])) && ($treff['omslag'] != "")) { // omslag finnes
			$postout .= '<img src="' . $treff['omslag'] . '" alt="' . $treff['tittelinfo'] . '" />' . "\n";
		} else {
			$postout .= '<img src="' . plugins_url( 'icons/ikke_digital.jpg', __FILE__ ) . '" alt="' . $treff['tittelinfo'] . '" />' . "\n";
		}
		$postout .= '</div>' . "\n";

		$postout .= '<div class="infocontainer">' . "\n";
		$postout .= '<h2>' . str_replace (": :" , ":", $treff['tittelinfo']) . '</h2>' . "\n";
		$postout .= '<p>' . "\n";
		if ((isset ($treff['forfatter'])) && ($treff['forfatter'] != "")) {
			$postout .= '<strong>Forfatter : </strong>' . $treff['forfatter'] . '<br>' . "\n";
		}
		
		$utgitt = '';
		if (isset($treff['utgitthvem'])) {
			$utgitt = $treff['utgitthvem'];
		}
		if (isset($treff['utgitthvor'])) {
			$utgitt .= ", " . $treff['utgitthvor'];
		}
		if (isset($treff['utgittaar'])) {
			$utgitt .= ", " . $treff['utgittaar'];
		}
		if ($utgitt != "") {
			$postout .= '<strong>Utgitt : </strong>' . $utgitt . "<br>\n";
		}

		if ((isset($treff['isbn'])) && ($treff['isbn'] != "")) {
			$postout .= '<strong>ISBN : </strong>' . $treff['isbn'] . "<br>\n";
		}

		if ((isset($treff['beskrivelse'])) && ($treff['beskrivelse'] != "")) {
			$postout .= $treff['beskrivelse'] . '<br>' . "\n";
		}

		$postout .= '<br style="clear: both;">' . "\n";
		
		// Kode for bestand her er slettet - vi har ikke bestandsinfo i Koha

		if (isset($treff['fulltekst'])) { // finnes den på nett?
			$postout .= "<input class=\"onlineknapp\" type=\"button\" value=\"Les p&aring; nett\" onClick=\"location.href='" . $treff['fulltekst'] . "'\">\n";
		}
		
		$bestilleurl = str_replace ("show" , "acquire" , $treff['permalink']); // oh, you clever AGAIN!!
		$postout .= "<input class=\"bestilleknapp\" type=\"button\" value=\"Bestille/reservere\" onClick=\"location.href='" . $bestilleurl . "'\">\n";
		$bestilleurl = ''; // må rydde opp
		$postout .= '</p>' . "\n";
		$postout .= '</div>' . "\n"; // slutt på infoboks
		if ((isset($treff['bestand'])) && (is_array($treff['bestand']))) { // bare hvis vi har bestandinfo
			$postout .= '<div class="bestandcontainer">' . "\n";
			$postout .= '<h3>Eksemplarer:</h3>' . "\n";
			$postout .= '<p>' . "\n";
			foreach ($treff['bestand'] as $bestand) {
				$postout .= $bestand->institution;
				if (isset($bestand->collection)) {
					$postout .= '&nbsp/&nbsp' . $bestand->collection;
				}
				if (isset($bestand->callnumber)) {
					$postout .= '&nbsp/&nbsp' . $bestand->callnumber;
				}
				$postout .= ' : ';
				$postout .= bestandsinfo ($bestand->circulationStatus , $bestand->useRestriction); // status, restriction
				if (($bestand->circulationStatus == "4") || ($bestand->circulationStatus == "5")) { // UTLÅNT
					setlocale (LC_TIME , "nb_NO"); // norsk dato
					$postout .= " til " . strftime("%e. %B %G" , strtotime($bestand['y']));
				}
				$postout .= '<br>' . "\n";
			}
			$postout .= '</div>' . "\n"; // slutt på bestand
		}
	}


	$postout .= '<br style="clear: both;">' . "\n";
	$postout .= '</div>'; // slutt enkeltpostvisning

	} else {
		return ("Ingen post angitt!");
	}

// Husk å returnere noe, ikke printe!
return $postout;
}










// 		CODE FOR WIDGET IS BELOW !!


class wl_ils_widget extends WP_Widget {

    protected $widget_slug = 'wl_ils_widget';

	/*--------------------------------------------------*/
	/* Constructor
	/*--------------------------------------------------*/

	/**
	 * Specifies the classname and description, instantiates the widget,
	 * loads localization files, and includes necessary stylesheets and JavaScript.
	 */
	public function __construct() {

		// Hooks fired when the Widget is activated and deactivated
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

		parent::__construct(
			$this->get_widget_slug(),
			__( 'Inter Library Search by Webloft', $this->get_widget_slug() ),
			array(
				'classname'  => $this->get_widget_slug().'-class',
				'description' => __( 'Setter inn søkefelt for å søke og presentere fine trefflister integrert i Wordpress.', $this->get_widget_slug() )
			)
		);

		// Register admin styles and scripts
		add_action( 'admin_print_styles', array( $this, 'register_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_scripts' ) );

		// Register site styles and scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'register_widget_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_widget_scripts' ) );

		// Refreshing the widget's cached output with each new post
		add_action( 'save_post',    array( $this, 'flush_widget_cache' ) );
		add_action( 'deleted_post', array( $this, 'flush_widget_cache' ) );
		add_action( 'switch_theme', array( $this, 'flush_widget_cache' ) );


	} // end constructor

    public function get_widget_slug() {
        return $this->widget_slug;
    }

	/*--------------------------------------------------*/
	/* Widget API Functions
	/*--------------------------------------------------*/

	/**
	 * Outputs the content of the widget.
	 *
	 * @param array args  The array of form elements
	 * @param array instance The current instance of the widget
	 */
	public function widget( $args, $instance ) {


		// Check if there is a cached output
		$cache = wp_cache_get( $this->get_widget_slug(), 'widget' );

		if ( !is_array( $cache ) )
			$cache = array();

		if ( ! isset ( $args['widget_id'] ) )
			$args['widget_id'] = $this->id;

		if ( isset ( $cache[ $args['widget_id'] ] ) )
			return print $cache[ $args['widget_id'] ];
		
		// go on with your widget logic, put everything into a string and â€¦


		extract( $args, EXTR_SKIP );

		$widget_string = $before_widget;

		// Here is where you manipulate your widget's values based on their input fields

		ob_start();
		include( plugin_dir_path( __FILE__ ) . 'widget.php' );
		$widget_string .= ob_get_clean();
		$widget_string .= $after_widget;

		$cache[ $args['widget_id'] ] = $widget_string;

		wp_cache_set( $this->get_widget_slug(), $cache, 'widget' );

		print $widget_string;

	} // end widget
	
	
	public function flush_widget_cache() 
	{
    	wp_cache_delete( $this->get_widget_slug(), 'widget' );
	}
	/**
	 * Processes the widget's options to be saved.
	 *
	 * @param array new_instance The new instance of values to be generated via the update.
	 * @param array old_instance The previous instance of values before the update.
	 */
	public function update( $new_instance, $old_instance ) {

		$instance = $old_instance;

		// Here is where you update your widget's old values with the new, incoming values

		$instance['resultatside'] = strip_tags($new_instance['resultatside']);
		$instance['title'] = strip_tags($new_instance['title']);

		return $instance;

	} // end widget

	/**
	 * Generates the administration form for the widget.
	 *
	 * @param array instance The array of keys and values for the widget.
	 */
	public function form( $instance ) {

		// Define default values for your variables
		$defaults = array( 'resultatside' => '' , 'tittel' => 'Søk i katalogen');
		$instance = wp_parse_args(
			(array) $instance, $defaults
		);

		// Store the values of the widget in their own variable

		$resultatside = esc_attr($instance['resultatside']);
		$title = esc_attr($instance['title']);

		// Display the admin form
		include( plugin_dir_path(__FILE__) . 'admin.php' );

	} // end form

	/*--------------------------------------------------*/
	/* Public Functions
	/*--------------------------------------------------*/

	/**
	 * Loads the Widget's text domain for localization and translation.
	 */
	 
	public function widget_textdomain() {

	//load_plugin_textdomain( 'wl_ils', false, dirname( plugin_dir_path( __FILE__ ) ) . '/lang' );

		
	} // end widget_textdomain

	/**
	 * Fired when the plugin is activated.
	 *
	 * @param  boolean $network_wide True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog.
	 */
	public function activate( $network_wide ) {
		// Define activation functionality here
	} // end activate

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @param boolean $network_wide True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog
	 */
	public function deactivate( $network_wide ) {
		// Define deactivation functionality here
	} // end deactivate

	/**
	 * Registers and enqueues admin-specific styles.
	 */
	public function register_admin_styles() {

		//wp_enqueue_style( $this->get_widget_slug().'-admin-styles', plugins_url( 'css/admin.css', __FILE__ ) );

		
	} // end register_admin_styles

	/**
	 * Registers and enqueues admin-specific JavaScript.
	 */
	public function register_admin_scripts() {

		//wp_enqueue_script( $this->get_widget_slug().'-admin-script', plugins_url( 'admin.js', __FILE__ ), array('jquery') );

		
	} // end register_admin_scripts

	/**
	 * Registers and enqueues widget-specific styles.
	 */
	public function register_widget_styles() {

	// GJØRES ALLEREDE
	//wp_enqueue_style( 'wl_ils', plugins_url( '/wl-ils.css', __FILE__ ), false, '1.0', 'all' );

	} // end register_widget_styles

	/**
	 * Registers and enqueues widget-specific scripts.
	 */
	public function register_widget_scripts() {

	// GJØRES ALLEREDE
	//wp_enqueue_script( $this->get_widget_slug().'-iframe-script', plugins_url( 'js/iframeheight.js', __FILE__ ), array('jquery') );

	} // end register_widget_scripts

} // end class

// Settings page

add_action('admin_menu', 'SetupPage');
add_action('admin_init', 'RegisterSettings');

function SetupPage() {
    add_options_page(utf8_encode("ILS Search by Webloft"), "ILS Search by Webloft", "manage_options", "wl_ils_options", "wl_ils_settings_page");
}

function RegisterSettings() {
    // Add options to database if they don't already exist
    add_option("wl_ils_option_mittbibliotek", "2020000", "", "yes");
    add_option("wl_ils_option_omslagbokkilden", "0", "", "yes");
    add_option("wl_ils_option_omslagnb", "0", "", "yes");
    add_option("wl_ils_option_hamedbilder", "1", "", "yes");
    add_option("wl_ils_option_makstreff", "25", "", "yes");
    add_option("wl_ils_option_bibsysbestand", "0", "", "yes");
    add_option("wl_ils_option_enkeltpost", "", "", "yes");

    // Register settings that this form is allowed to update
    register_setting('wl_ils_options', 'wl_ils_option_mittbibliotek');
    register_setting('wl_ils_options', 'wl_ils_option_omslagbokkilden');
    register_setting('wl_ils_options', 'wl_ils_option_omslagnb');
    register_setting('wl_ils_options', 'wl_ils_option_hamedbilder');
    register_setting('wl_ils_options', 'wl_ils_option_makstreff');
    register_setting('wl_ils_options', 'wl_ils_option_bibsysbestand');
    register_setting('wl_ils_options', 'wl_ils_option_enkeltpost');
}

function wl_ils_settings_page() {
    if (!current_user_can('manage_options'))
        wp_die(__("You don't have access to this page"));
?><div class="wrap">
        <h1>ILS Search by Webloft - innstillinger</h1>

        <form method="post" action="options.php">

            <?php settings_fields('wl_ils_options'); ?>
<label for="wl_ils_option_mittbibliotek">Angi katalog det skal s&oslash;kes i:</label>&nbsp;&nbsp;
              <select name="wl_ils_option_mittbibliotek">
<?php 

$mittbibliotek = '';
$valgt = get_option('wl_ils_option_mittbibliotek' , '2020000');
$omslagbokkilden = get_option('wl_ils_option_omslagbokkilden' , '0');
$omslagnb = get_option('wl_ils_option_omslagnb' , '0');
$hamedbilder = get_option('wl_ils_option_hamedbilder' , '0');
$makstreff = get_option('wl_ils_option_makstreff' , '0');
$bibsysbestand = get_option('wl_ils_option_bibsysbestand' , '0');
$enkeltpost = get_option('wl_ils_option_enkeltpost' , '');

include ("serverliste.php");
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
</p>
<p>
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
<?php
}



// All set:
add_action( 'widgets_init', create_function( '', 'register_widget("wl_ils_widget");' ) );
add_shortcode("wl-ils", "wl_ils_func");
add_shortcode("wl-ils-enkeltpost", "enkeltpost_func");
