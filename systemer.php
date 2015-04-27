<?php

//****************************************************************************************************
function koha_antalltreff($url) { // finner antall treff for et søk 
//****************************************************************************************************
	
	$koha_datafil = get_content($url);
	$koha_data    = simplexml_load_string($koha_datafil);
	
	$antallfunnet = $koha_data->channel->children('opensearch', true)->totalResults;
	
	return $antallfunnet;
	
} // end function

//****************************************************************************************************
function koha_sok($url, $posisjon) {
//****************************************************************************************************
	
	// Vi må slenge på posisjon i URL-en
	$url = $url . "&offset=" . $posisjon;
	
	$koha_datafil = get_content($url);
	$koha_data    = simplexml_load_string($koha_datafil);
	$totalhtml    = '';
	$pendel       = 0;
	$hitcounter   = 0;
	$treff        = '';
	
	foreach ($koha_data->channel->item as $item) {
		$treff[$hitcounter]['permalink']  = (string)$item->link;
		$treff[$hitcounter]['tittel']     = (string)$item->title;
		$treff[$hitcounter]['tittelinfo'] = (string)$treff[$hitcounter]['tittel'];
		
		if (isset($item->description->p[0])) { // Koha-knøl
			$beskrivelsetemp = strip_tags($item->description->p[0]);
			$beskrivelsetemp = preg_replace('/[ \t]+/', ' ', preg_replace('/[\r\n]+/', "\n", $beskrivelsetemp));
		} else {
			$beskrivelsetemper = explode("<p>", $item->description);
			$beskrivelsetemp   = $beskrivelsetemper[1];
			$beskrivelsetemp   = strip_tags($item->description);
			$beskrivelsetemp   = preg_replace('/[ \t]+/', ' ', preg_replace('/[\r\n]+/', "\n", $beskrivelsetemp)); // fjerne tabs, mellomrom...

			$beskrivelsetemper = explode("Place Hold on", $beskrivelsetemp); // vil ikke ha med den siste "Place hold on"-teksten
			$beskrivelsetemp   = $beskrivelsetemper[0];
		}
		$beskrivelsetemp                   = str_replace("By ", "", $beskrivelsetemp); // Hvorfor står det "By " i beskrivelsen?
		$treff[$hitcounter]['beskrivelse'] = $beskrivelsetemp;
		
		$treff[$hitcounter]['orgisbn'] = (string)$item->children('dc', true)->identifier;
		$treff[$hitcounter]['orgisbn'] = str_replace("ISBN ", "", $treff[$hitcounter]['orgisbn']); // fjerne ISBN 
		$treff[$hitcounter]['isbn'] = str_replace("-", "", $treff[$hitcounter]['orgisbn']); // fjerne bindestrek
		$treff[$hitcounter]['isbn'] = str_replace(" ", "", $treff[$hitcounter]['isbn']); // fjerne mellomrom
		
		// Fjerne ISBN fra beskrivelsesteksten
		$treff[$hitcounter]['beskrivelse'] = str_replace ($treff[$hitcounter]['orgisbn'] , "" , $treff[$hitcounter]['beskrivelse']);

		$hitcounter++;
		
	} // slutt på hvert item
	
	
	return ($treff);
	
} // end function


//****************************************************************************************************
function bibliofil_antalltreff($url) { // finner antall treff for et søk
//****************************************************************************************************
	
	$sru_datafil = get_content($url);
	$sru_data    = simplexml_load_string($sru_datafil);
	
	$namespaces = $sru_data->getNameSpaces(true);
	$srw        = $sru_data->children($namespaces['SRU']); // alle som er srw:ditten og srw:datten
	
	$antallfunnet = $srw->numberOfRecords;
	
	return $antallfunnet;
	
} // end function

//****************************************************************************************************
function bibliofil_sok($url, $posisjon) {
//****************************************************************************************************
	
	// Vi må slenge på posisjon i URL-en
	$url = $url . "&startRecord=" . $posisjon;
	$sru_datafil = get_content($url);
	$sru_data    = simplexml_load_string($sru_datafil);
	
	$namespaces = $sru_data->getNameSpaces(true);
	$srw        = $sru_data->children($namespaces['SRU']); // alle som er srw:ditten og srw:datten
	
	// Så ta selve filen og plukke ut det vi skal ha
	
	$hepphepp = str_replace("marcxchange:", "", $sru_datafil);
	$hepphepp = strip_tags($hepphepp, "<record><leader><controlfield><datafield><subfield>");
	$hepphepp = stristr($hepphepp, "<record");
	
	$newfile = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
	$newfile .= "<collection>\n";
	$newfile .= $hepphepp;
	$newfile .= "</collection>";
	
	// Retrieve a set of MARC records from a file
	
	require 'File/MARCXML.php';
	
	$journals = new File_MARCXML($newfile, File_MARC::SOURCE_STRING);
	// Iterate through the retrieved records
	
	$totalhtml  = '';
	$pendel     = 0;
	$hitcounter = 0;
	$treff      = '';
	
	while ($record = $journals->next()) {
		
		// initialize variables
		

		if ($record->getField("001")) {
			$identifier = $record->getField("001");
			$treff[$hitcounter]['identifier'] = trim(substr($identifier, 5)); // fjerne feltkoden i starten
		}

		if ($record->getField("996")) {
			$permalink = $record->getField("996")->getSubfield("u");
			$permalink = substr($permalink, 5); // fjerne feltkoden i starten
			if (stristr($permalink, "http:")) { // hvis begynner med http:
				$treff[$hitcounter]['permalink'] = $permalink;
			} else { // ellers må vi legge til http:
				$treff[$hitcounter]['permalink'] = "http://" . $permalink;
			}
		} else { // no permalink
			$treff[$hitcounter]['permalink'] = "";
		}
	
		if ($record->getField("245")) {
			$tittel                          = $record->getField("245")->getSubfield("a");
			$treff[$hitcounter]['tittel']    = substr($tittel, 5); // fjerne feltkoden i starten
			$subtittel                       = $record->getField("245")->getSubfield("b");
			$treff[$hitcounter]['subtittel'] = substr($subtittel, 5); // fjerne feltkoden i starten
			if ($record->getField("245")->getSubfield("c")) {
				$ansvar                      = $record->getField("245")->getSubfield("c");
				$treff[$hitcounter]['ansvarsangivelse'] = substr($ansvar, 5); // fjerne feltkoden i starten
			}
		}
		
		if ($record->getField("574")) { // Originaltittel
			$originaltittel = $record->getField("574")->getSubfield("a");
			$originaltittel = substr($originaltittel, 5); // fjerne feltkoden i starten
			$originaltittel = str_ireplace ("originaltittel:" , "", $originaltittel);
			$originaltittel = str_ireplace ("originaltittel :" , "", $originaltittel);
			$originaltittel = str_ireplace ("originaltitler:" , "", $originaltittel);
			$originaltittel = str_ireplace ("originaltitler :" , "", $originaltittel);
			$treff[$hitcounter]['originaltittel'] = trim($originaltittel);
		}

		if ($record->getField("100")) {
			$forfatter                       = $record->getField("100")->getSubfield("a");
			$treff[$hitcounter]['forfatter'] = substr($forfatter, 5); // fjerne feltkoden i starten
			if ($record->getField("100")->getSubfield("d")) {
				$forfatterliv                = $record->getField("100")->getSubfield("d");
				$treff[$hitcounter]['forfatterliv'] = substr($forfatterliv, 5); // fjerne feltkoden i starten
			}
		}

		if ($record->getField("110")) {
			$korporasjon                       = $record->getField("110")->getSubfield("a");
			$treff[$hitcounter]['korporasjon'] = substr($korporasjon, 5); // fjerne feltkoden i starten
		}
		
		if ($record->getField("20")) {
			$isbn                       = $record->getField("20")->getSubfield("a");
			$treff[$hitcounter]['isbn'] = substr($isbn, 5); // fjerne feltkoden i starten
			if ($record->getField("20")->getSubfield("b")) {
				$heftetbundet = $record->getField("20")->getSubfield("b");
				$treff[$hitcounter]['heftetbundet'] = substr($heftetbundet, 5); // fjerne feltkoden i starten
			}
		}
		
		if ($record->getField("520")) {
			$beskrivelse                       = $record->getField("520")->getSubfield("a");
			$treff[$hitcounter]['beskrivelse'] = substr($beskrivelse, 5); // fjerne feltkoden i starten
		}
		
		if ($record->getField("260")) {
			$utgitthvor                       = $record->getField("260")->getSubfield("a");
			$treff[$hitcounter]['utgitthvor'] = substr($utgitthvor, 5);
			$utgitthvem                       = $record->getField("260")->getSubfield("b");
			$treff[$hitcounter]['utgitthvem'] = substr($utgitthvem, 5);
			$utgittaar                        = $record->getField("260")->getSubfield("c");
			$utgittaar                        = substr($utgittaar, 5);
			$utgittaar                        = str_replace("[", "", $utgittaar); // disse to linjene fjerner [ og ] i årstall
			$treff[$hitcounter]['utgittaar']  = str_replace("]", "", $utgittaar);
			$utgittaar                        = str_replace("<", "", $utgittaar); // disse to linjene fjerner < og > i årstall
			$treff[$hitcounter]['utgittaar']  = str_replace(">", "", $utgittaar);
			
		}
		
		if ($record->getField("300")) { // omfang
			$omfang = $record->getField("300")->getSubfield("a");
			$omfang = substr($omfang, 5);
			if ($record->getField("300")->getSubfield("b")) {
				$cheese = $record->getField("300")->getSubfield("b");
				$cheese = substr($cheese, 5);
				$omfang .= " : " . $cheese;
			}
		$treff[$hitcounter]['omfang'] = $omfang;
		}


		if ($record->getField("019")) {
			$materialkode                       = $record->getField("019")->getSubfield("b");
			$treff[$hitcounter]['materialkode'] = substr($materialkode, 5);
			
			// Hvis flere adskilt med komma går vi for den første
			
			if (stristr($treff[$hitcounter]['materialkode'], ",")) {
				$temp                               = explode(",", $treff[$hitcounter]['materialkode']);
				$treff[$hitcounter]['materialkode'] = $temp[0];
			}
		}
		
		// Ansvarsangivelse
		
		if (isset($treff[$hitcounter]['ansvarsangivelse'])) {
			$treff[$hitcounter]['opphav'] = $treff[$hitcounter]['ansvarsangivelse'];
		}

		if (isset($treff[$hitcounter]['forfatter'])) {
			$treff[$hitcounter]['opphav'] = $treff[$hitcounter]['forfatter'];
			if (isset($treff[$hitcounter]['forfatterliv'])) {
				$treff[$hitcounter]['opphav'] .= " (" . $treff[$hitcounter]['forfatterliv'] . ")";
			}
		}
		
		if (isset($treff[$hitcounter]['korporasjon'])) {
			$treff[$hitcounter]['opphav'] = $treff[$hitcounter]['korporasjon'];
		}
		
		// Tittel
		
		$treff[$hitcounter]['tittelinfo'] = $treff[$hitcounter]['tittel'];
		if ($treff[$hitcounter]['subtittel'] != '') {
			$treff[$hitcounter]['tittelinfo'] .= " : " . $treff[$hitcounter]['subtittel'];
		}
		if (isset($treff[$hitcounter]['materialkode'])) {
			if ($treff[$hitcounter]['materialkode'] == 'ee') { // DVD?
				$treff[$hitcounter]['tittelinfo'] .= " : DVD";
			}
		}
			// GJØRE TITTELINFO PEN:
			$treff[$hitcounter]['tittelinfo'] = str_replace(": :", ":", $treff[$hitcounter]['tittelinfo']);

	// Ikon
		if (isset($treff[$hitcounter]['materialkode'])) { // materialkode er angitt
			
			switch ($treff[$hitcounter]['materialkode']) {
				case "ee":
					$treff[$hitcounter]['type'] = "dvd";
					break;
				case "l":
					$treff[$hitcounter]['type'] = "bok";
					break;
				case "dc":
					$treff[$hitcounter]['type'] = "cd";
					break;
				case "de":
					$treff[$hitcounter]['type'] = "digikort";
					break;
				case "ga":
					$treff[$hitcounter]['type'] = "nedlastbar";
					break;
				case "dd":
					$treff[$hitcounter]['type'] = "lyd";
					break;
				case "di":
					$treff[$hitcounter]['type'] = "lydbok";
					break;
				case "dz":
					$treff[$hitcounter]['type'] = "mp3-lyd";
					break;
				case "c":
					$treff[$hitcounter]['type'] = "note";
					break;
				case "ed":
					$treff[$hitcounter]['type'] = "vhs";
					break;
				case "dg":
					$treff[$hitcounter]['type'] = "musikk";
					break;
				default:
					$treff[$hitcounter]['type'] = "ukjent";
					break;
			}
			
		} else { // materialkode ikke angitt, ergo ukjent
			$treff[$hitcounter]['type'] = "ukjent";
		}
		
		// REPETERBARE FELTER SJEKKES HER
		
		foreach ($record->getFields() as $tag => $subfields) {
			
			// Bestand: Sjekke 850

			if ($tag == '850') {
				foreach ($subfields->getSubfields() as $code => $value) {
					$ettfelt[(string) $code] = substr((string) $value, 5);
				}
				$ettfelt['bibnavn'] = bibnr_to_name($ettfelt['a']);
				$etteks[] = $ettfelt;
				unset($ettfelt);
			}


			// Lese utdrag: Sjekke 856
	
			if ($tag == '856') {
				foreach ($subfields->getSubfields() as $code => $value) {
					$ettfelt[(string) $code] = substr((string) $value, 5);
				}
				if ((isset($ettfelt['z'])) && ($ettfelt['z'] == "Les utdrag")) {
					if (isset($ettfelt['u'])) {
						$treff[$hitcounter]['pdfutdrag'] = $ettfelt['u'];
					}
				}

			}

			// Dewey: Sjekke 082 $a

			if ($tag == '082') {
				foreach ($subfields->getSubfields() as $code => $value) {
					$ettfelt[(string) $code] = substr((string) $value, 5);
				}
				if (isset($ettfelt['a'])) {
					$dewey = $ettfelt['a'];
					$endewey[] = $dewey;
				}
			}

			// Emneord: Sjekke 650 $a

			if ($tag == '650') {
				foreach ($subfields->getSubfields() as $code => $value) {
					$ettfelt[(string) $code] = substr((string) $value, 5);
				}
				if ((isset($ettfelt['a'])) && ($ettfelt['a'] != "")) {
					$emneord = $ettfelt['a'];
					$ettemneord[] = $emneord;
				}
			}

			// Generell note: Sjekke 500 $a

			if ($tag == '500') {
				foreach ($subfields->getSubfields() as $code => $value) {
					$ettfelt[(string) $code] = substr((string) $value, 5);
				}
				if ((isset($ettfelt['a'])) && ($ettfelt['a'] != "")) {
					$generellnote = $ettfelt['a'];
					$engenerellnote[] = $generellnote;
				}
			}

			// Innholdsnote: Sjekke 505 $a

			if ($tag == '505') {
				foreach ($subfields->getSubfields() as $code => $value) {
					$ettfelt[(string) $code] = substr((string) $value, 5);
				}
				if ((isset($ettfelt['a'])) && ($ettfelt['a'] != "")) {
					$innholdsnote = $ettfelt['a'];
					$eninnholdsnote[] = $innholdsnote;
				}
			}

			// Medarbeidernote: Sjekke 511 $a
			
			if ($tag == '511') {
				foreach ($subfields->getSubfields() as $code => $value) {
					$ettfelt[(string) $code] = substr((string) $value, 5);
				}
				if ((isset($ettfelt['a'])) && ($ettfelt['a'] != "")) {
					$medarbeidere = $ettfelt['a'];
					$enmedarbeidere[] = $medarbeidere;
				}
			}

			// Titler: Sjekke 740 $a
			
			if ($tag == '740') {
				foreach ($subfields->getSubfields() as $code => $value) {
					$ettfelt[(string) $code] = substr((string) $value, 5);
				}
				if ((isset($ettfelt['a'])) && ($ettfelt['a'] != "")) {
					$titler = $ettfelt['a'];
					$entittel[] = $titler;
				}
			}

		}
		
		if (isset($ettemneord) && (is_array($ettemneord))) {
			$ettemneord = array_unique ($ettemneord);
			sort ($ettemneord);
			$treff[$hitcounter]['emneord'] = $ettemneord;
		}
		@$treff[$hitcounter]['bestand'] = $etteks;
		@$treff[$hitcounter]['dewey'] = $endewey;
		@$treff[$hitcounter]['generellnote'] = $engenerellnote;
		@$treff[$hitcounter]['innholdsnote'] = $eninnholdsnote;
		@$treff[$hitcounter]['medarbeidere'] = $enmedarbeidere;
		@$treff[$hitcounter]['titler'] = $entittel;
	
		unset($etteks, $endewey, $ettemneord, $engenerellnote, $eninnholdsnote, $enmedarbeidere, $entittel);
		
		$hitcounter++;
	} // slutt på hvert item
	
	/*
	Omslag (hvordan?)
	Tittel (årstall)   ev     Tittel : DVD (årstall)
	Forfatter
	Beskrivelse (ligger i 520 $a noen ganger)
	Ikon basert på materialtype (liste her: 
	
	AKTUELLE KODER: 
	ee (DVD)
	l (bok)
	dc (CD)
	de (digikort)
	ga (nedlastbar fil)	
	dd (avspiller med lydfil)
	di (lydbok)
	dz (mp3, vi bruker lyd)
	c (Musikktrykk)
	ed (Videokassett VHS)
	dg (Musikk)
	
	ALLE IKONER VI TRENGER: https://www.iconfinder.com/iconsets/windows-8-metro-style
	
	IKONER: Bok, lyd, note, film DVD, film VHS
	
	*/

	return ($treff);
	
} // end function



//****************************************************************************************************
function tidemann_antalltreff($url) { // finner antall treff for et søk
//****************************************************************************************************
	
	$sru_datafil = get_content($url);
	$sru_data    = simplexml_load_string($sru_datafil);
	
	$namespaces = $sru_data->getNameSpaces(true);
	$srw        = $sru_data->children($namespaces['srw']); // alle som er srw:ditten og srw:datten
	
	$antallfunnet = $srw->numberOfRecords;
	
	return $antallfunnet;
	
} // end function

//****************************************************************************************************
function tidemann_sok($url, $posisjon) {
//****************************************************************************************************
	
	// Vi må slenge på posisjon i URL-en
	$url = $url . "&startRecord=" . $posisjon;
	$sru_datafil = get_content($url);
	$sru_data    = simplexml_load_string($sru_datafil);
	$namespaces = $sru_data->getNameSpaces(true);
	$srw        = $sru_data->children($namespaces['srw']); // alle som er srw:ditten og srw:datten
	
	// Så ta selve filen og plukke ut det vi skal ha
	
	$hepphepp = str_replace("marc:", "", $sru_datafil);
	$hepphepp = strip_tags($hepphepp, "<record><leader><controlfield><datafield><subfield>");
	$hepphepp = stristr($hepphepp, "<record");
	
	$newfile = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
	$newfile .= "<collection>\n";
	$newfile .= $hepphepp;
	$newfile .= "</collection>";
	
	// Retrieve a set of MARC records from a file
	
	require 'File/MARCXML.php';
	
	$journals = new File_MARCXML($newfile, File_MARC::SOURCE_STRING);
	// Iterate through the retrieved records
	
	$totalhtml  = '';
	$pendel     = 0;
	$hitcounter = 0;
	$treff      = '';
	
	while ($record = $journals->next()) {
		
		// initialize variables
		

		if ($record->getField("001")) {
			$identifier = $record->getField("001");
			$treff[$hitcounter]['identifier'] = trim(substr($identifier, 5)); // fjerne feltkoden i starten
		}

		if ($record->getField("996")) {
			$permalink = $record->getField("996")->getSubfield("u");
			$permalink = substr($permalink, 5); // fjerne feltkoden i starten
			if (stristr($permalink, "http:")) { // hvis begynner med http:
				$treff[$hitcounter]['permalink'] = $permalink;
			} else { // ellers må vi legge til http:
				$treff[$hitcounter]['permalink'] = "http://" . $permalink;
			}
		} else { // no permalink
			$treff[$hitcounter]['permalink'] = "";
		}

		if ($record->getField("245")) {
			$tittel                          = $record->getField("245")->getSubfield("a");
			$treff[$hitcounter]['tittel']    = substr($tittel, 5); // fjerne feltkoden i starten
			$subtittel                       = $record->getField("245")->getSubfield("b");
			$treff[$hitcounter]['subtittel'] = substr($subtittel, 5); // fjerne feltkoden i starten
			if ($record->getField("245")->getSubfield("c")) {
				$ansvar                      = $record->getField("245")->getSubfield("c");
				$treff[$hitcounter]['ansvarsangivelse'] = substr($ansvar, 5); // fjerne feltkoden i starten
			}
		}
		
		if ($record->getField("574")) { // Originaltittel
			$originaltittel = $record->getField("574")->getSubfield("a");
			$originaltittel = substr($originaltittel, 5); // fjerne feltkoden i starten
			$originaltittel = str_ireplace ("originaltittel:" , "", $originaltittel);
			$originaltittel = str_ireplace ("originaltittel :" , "", $originaltittel);
			$originaltittel = str_ireplace ("originaltitler:" , "", $originaltittel);
			$originaltittel = str_ireplace ("originaltitler :" , "", $originaltittel);
			$treff[$hitcounter]['originaltittel'] = trim($originaltittel);
		}

		if ($record->getField("100")) {
			$forfatter                       = $record->getField("100")->getSubfield("a");
			$treff[$hitcounter]['forfatter'] = substr($forfatter, 5); // fjerne feltkoden i starten
			if ($record->getField("100")->getSubfield("d")) {
				$forfatterliv                = $record->getField("100")->getSubfield("d");
				$treff[$hitcounter]['forfatterliv'] = substr($forfatterliv, 5); // fjerne feltkoden i starten
			}
		}

		if ($record->getField("110")) {
			$korporasjon                       = $record->getField("110")->getSubfield("a");
			$treff[$hitcounter]['korporasjon'] = substr($korporasjon, 5); // fjerne feltkoden i starten
		}
		
		if ($record->getField("20")) {
			$isbn                       = $record->getField("20")->getSubfield("a");
			$treff[$hitcounter]['isbn'] = substr($isbn, 5); // fjerne feltkoden i starten
			if ($record->getField("20")->getSubfield("b")) {
				$heftetbundet = $record->getField("20")->getSubfield("b");
				$treff[$hitcounter]['heftetbundet'] = substr($heftetbundet, 5); // fjerne feltkoden i starten
			}
		}
		
		if ($record->getField("520")) {
			$beskrivelse                       = $record->getField("520")->getSubfield("a");
			$treff[$hitcounter]['beskrivelse'] = substr($beskrivelse, 5); // fjerne feltkoden i starten
		}
		
		if ($record->getField("260")) {
			$utgitthvor                       = $record->getField("260")->getSubfield("a");
			$treff[$hitcounter]['utgitthvor'] = substr($utgitthvor, 5);
			$utgitthvem                       = $record->getField("260")->getSubfield("b");
			$treff[$hitcounter]['utgitthvem'] = substr($utgitthvem, 5);
			$utgittaar                        = $record->getField("260")->getSubfield("c");
			$utgittaar                        = substr($utgittaar, 5);
			$utgittaar                        = str_replace("[", "", $utgittaar); // disse to linjene fjerner [ og ] i årstall
			$treff[$hitcounter]['utgittaar']  = str_replace("]", "", $utgittaar);
			$utgittaar                        = str_replace("<", "", $utgittaar); // disse to linjene fjerner < og > i årstall
			$treff[$hitcounter]['utgittaar']  = str_replace(">", "", $utgittaar);
			
		}
		
		if ($record->getField("300")) { // omfang
			$omfang = $record->getField("300")->getSubfield("a");
			$omfang = substr($omfang, 5);
			if ($record->getField("300")->getSubfield("b")) {
				$cheese = $record->getField("300")->getSubfield("b");
				$cheese = substr($cheese, 5);
				$omfang .= " : " . $cheese;
			}
		$treff[$hitcounter]['omfang'] = $omfang;
		}


		if ($record->getField("019")) {
			$materialkode                       = $record->getField("019")->getSubfield("b");
			$treff[$hitcounter]['materialkode'] = substr($materialkode, 5);
			
			// Hvis flere adskilt med komma går vi for den første
			
			if (stristr($treff[$hitcounter]['materialkode'], ",")) {
				$temp                               = explode(",", $treff[$hitcounter]['materialkode']);
				$treff[$hitcounter]['materialkode'] = $temp[0];
			}
		}
		
		// Ansvarsangivelse
		
		if (isset($treff[$hitcounter]['ansvarsangivelse'])) {
			$treff[$hitcounter]['opphav'] = $treff[$hitcounter]['ansvarsangivelse'];
		}

		if (isset($treff[$hitcounter]['forfatter'])) {
			$treff[$hitcounter]['opphav'] = $treff[$hitcounter]['forfatter'];
			if (isset($treff[$hitcounter]['forfatterliv'])) {
				$treff[$hitcounter]['opphav'] .= " (" . $treff[$hitcounter]['forfatterliv'] . ")";
			}
		}
		
		if (isset($treff[$hitcounter]['korporasjon'])) {
			$treff[$hitcounter]['opphav'] = $treff[$hitcounter]['korporasjon'];
		}
		
		// Tittel
		
		$treff[$hitcounter]['tittelinfo'] = $treff[$hitcounter]['tittel'];
		if ($treff[$hitcounter]['subtittel'] != '') {
			$treff[$hitcounter]['tittelinfo'] .= " : " . $treff[$hitcounter]['subtittel'];
		}
		if (isset($treff[$hitcounter]['materialkode'])) {
			if ($treff[$hitcounter]['materialkode'] == 'ee') { // DVD?
				$treff[$hitcounter]['tittelinfo'] .= " : DVD";
			}
		}
			// GJØRE TITTELINFO PEN:
			$treff[$hitcounter]['tittelinfo'] = str_replace(": :", ":", $treff[$hitcounter]['tittelinfo']);

	// Ikon
		if (isset($treff[$hitcounter]['materialkode'])) { // materialkode er angitt
			
			switch ($treff[$hitcounter]['materialkode']) {
				case "ee":
					$treff[$hitcounter]['type'] = "dvd";
					break;
				case "l":
					$treff[$hitcounter]['type'] = "bok";
					break;
				case "dc":
					$treff[$hitcounter]['type'] = "cd";
					break;
				case "de":
					$treff[$hitcounter]['type'] = "digikort";
					break;
				case "ga":
					$treff[$hitcounter]['type'] = "nedlastbar";
					break;
				case "dd":
					$treff[$hitcounter]['type'] = "lyd";
					break;
				case "di":
					$treff[$hitcounter]['type'] = "lydbok";
					break;
				case "dz":
					$treff[$hitcounter]['type'] = "mp3-lyd";
					break;
				case "c":
					$treff[$hitcounter]['type'] = "note";
					break;
				case "ed":
					$treff[$hitcounter]['type'] = "vhs";
					break;
				case "dg":
					$treff[$hitcounter]['type'] = "musikk";
					break;
				default:
					$treff[$hitcounter]['type'] = "ukjent";
					break;
			}
			
		} else { // materialkode ikke angitt, ergo ukjent
			$treff[$hitcounter]['type'] = "ukjent";
		}
		
		// REPETERBARE FELTER SJEKKES HER
		
		foreach ($record->getFields() as $tag => $subfields) {
			
			// Bestand: Sjekke 850

			if ($tag == '850') {
				foreach ($subfields->getSubfields() as $code => $value) {
					$ettfelt[(string) $code] = substr((string) $value, 5);
				}
				$ettfelt['bibnavn'] = bibnr_to_name($ettfelt['a']);
				$etteks[] = $ettfelt;
				unset($ettfelt);
			}
	
			// Dewey: Sjekke 082 $a

			if ($tag == '082') {
				foreach ($subfields->getSubfields() as $code => $value) {
					$ettfelt[(string) $code] = substr((string) $value, 5);
				}
				if (isset($ettfelt['a'])) {
					$dewey = $ettfelt['a'];
					$endewey[] = $dewey;
				}
			}

			// Emneord: Sjekke 650 $a

			if ($tag == '650') {
				foreach ($subfields->getSubfields() as $code => $value) {
					$ettfelt[(string) $code] = substr((string) $value, 5);
				}
				if ((isset($ettfelt['a'])) && ($ettfelt['a'] != "")) {
					$emneord = $ettfelt['a'];
					$ettemneord[] = $emneord;
				}
			}

			// Generell note: Sjekke 500 $a

			if ($tag == '500') {
				foreach ($subfields->getSubfields() as $code => $value) {
					$ettfelt[(string) $code] = substr((string) $value, 5);
				}
				if ((isset($ettfelt['a'])) && ($ettfelt['a'] != "")) {
					$generellnote = $ettfelt['a'];
					$engenerellnote[] = $generellnote;
				}
			}

			// Innholdsnote: Sjekke 505 $a

			if ($tag == '505') {
				foreach ($subfields->getSubfields() as $code => $value) {
					$ettfelt[(string) $code] = substr((string) $value, 5);
				}
				if ((isset($ettfelt['a'])) && ($ettfelt['a'] != "")) {
					$innholdsnote = $ettfelt['a'];
					$eninnholdsnote[] = $innholdsnote;
				}
			}

			// Medarbeidernote: Sjekke 511 $a
			
			if ($tag == '511') {
				foreach ($subfields->getSubfields() as $code => $value) {
					$ettfelt[(string) $code] = substr((string) $value, 5);
				}
				if ((isset($ettfelt['a'])) && ($ettfelt['a'] != "")) {
					$medarbeidere = $ettfelt['a'];
					$enmedarbeidere[] = $medarbeidere;
				}
			}

			// Titler: Sjekke 740 $a
			
			if ($tag == '740') {
				foreach ($subfields->getSubfields() as $code => $value) {
					$ettfelt[(string) $code] = substr((string) $value, 5);
				}
				if ((isset($ettfelt['a'])) && ($ettfelt['a'] != "")) {
					$titler = $ettfelt['a'];
					$entittel[] = $titler;
				}
			}

		}
		
		if (isset($ettemneord) && (is_array($ettemneord))) {
			$ettemneord = array_unique ($ettemneord);
			sort ($ettemneord);
			$treff[$hitcounter]['emneord'] = $ettemneord;
		}
		@$treff[$hitcounter]['bestand'] = $etteks;
		@$treff[$hitcounter]['dewey'] = $endewey;
		@$treff[$hitcounter]['generellnote'] = $engenerellnote;
		@$treff[$hitcounter]['innholdsnote'] = $eninnholdsnote;
		@$treff[$hitcounter]['medarbeidere'] = $enmedarbeidere;
		@$treff[$hitcounter]['titler'] = $entittel;
	
		unset($etteks, $endewey, $ettemneord, $engenerellnote, $eninnholdsnote, $enmedarbeidere, $entittel);
		
		$hitcounter++;
	} // slutt på hvert item
	
	/*
	Omslag (hvordan?)
	Tittel (årstall)   ev     Tittel : DVD (årstall)
	Forfatter
	Beskrivelse (ligger i 520 $a noen ganger)
	Ikon basert på materialtype (liste her: 
	
	AKTUELLE KODER: 
	ee (DVD)
	l (bok)
	dc (CD)
	de (digikort)
	ga (nedlastbar fil)	
	dd (avspiller med lydfil)
	di (lydbok)
	dz (mp3, vi bruker lyd)
	c (Musikktrykk)
	ed (Videokassett VHS)
	dg (Musikk)
	
	ALLE IKONER VI TRENGER: https://www.iconfinder.com/iconsets/windows-8-metro-style
	
	IKONER: Bok, lyd, note, film DVD, film VHS
	
	*/

	return ($treff);
	
} // end function


//****************************************************************************************************	
function bibsys_sok($url, $avdkode, $hamedbibsys, $posisjon) { // url til søk, avdkode til bibl, sjekke bestand? (1/0), starte på treff nummer
//****************************************************************************************************

	// Vi må slenge på posisjon i URL-en
	$url         = $url . "&startRecord=" . $posisjon;
	$sru_datafil = get_content($url);
	$sru_data    = simplexml_load_string($sru_datafil);
	$namespaces  = $sru_data->getNameSpaces(true);
	$srw         = $sru_data->children($namespaces['srw']); // alle som er srw:ditten og srw:datten
	
	// Så ta selve filen og plukke ut det vi skal ha
	
	$hepphepp = str_replace("marc:", "", $sru_datafil);
	$hepphepp = strip_tags($hepphepp, "<record><controlfield><leader><datafield><subfield>");
	$hepphepp = stristr($hepphepp, "<record");
	
	$newfile = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
	$newfile .= "<collection>\n";
	$newfile .= $hepphepp;
	$newfile .= "</collection>";
	
	// Retrieve a set of MARC records from a file
	require 'File/MARCXML.php';
	
	$journals = new File_MARCXML($newfile, File_MARC::SOURCE_STRING);
	
	// Iterate through the retrieved records
	
	$totalhtml  = '';
	$pendel     = 0;
	$hitcounter = 0;
	$treff      = '';
	
	while ($record = $journals->next()) {
	
		// Elektronisk utgave finnes i 776
		if ($record->getField("776")) {
			if ($record->getField("776")->getSubfield("w")) {
				$treff[$hitcounter]['fulltekst'] = $record->getField("776")->getSubfield("w");
				$treff[$hitcounter]['fulltekst'] = substr($treff[$hitcounter]['fulltekst'], -9); // 9 siste = dokid
				$treff[$hitcounter]['fulltekst'] = "http://ask.bibsys.no/ask/action/show?pid=" . $treff[$hitcounter]['fulltekst'] . "&kid=biblio";
			}
		}

		if ($record->getField("996")) { // lete etter permalenke først i 996
			$permalink                       = $record->getField("996")->getSubfield("u");
			$treff[$hitcounter]['permalink'] = stristr($permalink, "http");
		}
		
		if ($record->getField("001")) { // så leter vi i 001
			$treff[$hitcounter]['identifier'] = $record->getField("001");
			$treff[$hitcounter]['identifier'] = trim(substr($treff[$hitcounter]['identifier'], 5));
			$treff[$hitcounter]['permalink']  = "http://ask.bibsys.no/ask/action/show?pid=" . $treff[$hitcounter]['identifier'] . "&kid=biblio";
			$bibsystocheck[]                  = "id=" . $treff[$hitcounter]['identifier']; // legger til i listen over Bisys-bestand å sjekke
		}
		
		$tempbeskrivelse = $record->getFields("856"); // lenke til fulltekst eller omslagsbilde finnes ev. her
		if ($tempbeskrivelse) {
			foreach ($tempbeskrivelse as $tempfield) {
				$typeinfo = $tempfield->getSubfield("3");
				if (stristr($typeinfo, "Beskrivelse")) {
					$treff[$hitcounter]['descriptionlink'] = $tempfield->getSubfield("u");
					$treff[$hitcounter]['descriptionlink'] = trim(substr($treff[$hitcounter]['descriptionlink'], 5));
				}
				if (stristr($typeinfo, "Omslagsbilde")) {
					$treff[$hitcounter]['omslag'] = $tempfield->getSubfield("u");
					$treff[$hitcounter]['omslag'] = trim(substr($treff[$hitcounter]['omslag'], 5));
				}
				if (stristr($typeinfo, "Fulltekst")) {
					$treff[$hitcounter]['fulltekst'] = $tempfield->getSubfield("u");
					$treff[$hitcounter]['fulltekst'] = trim(substr($treff[$hitcounter]['fulltekst'], 5));
				}
			}
		}
		
		if ($record->getField("245")) {
			$tittel                          = $record->getField("245")->getSubfield("a");
			$treff[$hitcounter]['tittel']    = substr($tittel, 5); // fjerne feltkoden i starten
			if ($record->getField("245")->getSubfield("b")) {
				$subtittel                       = $record->getField("245")->getSubfield("b");
				$treff[$hitcounter]['subtittel'] = substr($subtittel, 5); // fjerne feltkoden i starten
			}
			if ($record->getField("245")->getSubfield("c")) {
				$ansvar                      = $record->getField("245")->getSubfield("c");
				$treff[$hitcounter]['ansvarsangivelse'] = substr($ansvar, 5); // fjerne feltkoden i starten
			}
		}

		if ($record->getField("246")) { // Originaltittel
			if (($record->getField("246")->getSubfield("i")) && (stristr($record->getField("246")->getSubfield("i") , "originaltittel"))) {
				$originaltittel = $record->getField("246")->getSubfield("a");
				$originaltittel = substr($originaltittel, 5); // fjerne feltkoden i starten
				$originaltittel = str_ireplace ("originaltittel:" , "", $originaltittel);
				$originaltittel = str_ireplace ("originaltittel :" , "", $originaltittel);
				$originaltittel = str_ireplace ("originaltitler:" , "", $originaltittel);
				$originaltittel = str_ireplace ("originaltitler :" , "", $originaltittel);
				$treff[$hitcounter]['originaltittel'] = trim($originaltittel);
			}
		}
		
		if ($record->getField("300")) { // omfang
			$omfang = $record->getField("300")->getSubfield("a");
			$omfang = substr($omfang, 5);
			if ($record->getField("300")->getSubfield("b")) {
				$cheese = $record->getField("300")->getSubfield("b");
				$cheese = substr($cheese, 5);
				$omfang .= " : " . $cheese;
			}
		$treff[$hitcounter]['omfang'] = $omfang;
		}
		
		if ($record->getField("100")) {
			$forfatter                       = $record->getField("100")->getSubfield("a");
			$treff[$hitcounter]['forfatter'] = substr($forfatter, 5); // fjerne feltkoden i starten
			if ($record->getField("100")->getSubfield("d")) {
				$forfatterliv                = $record->getField("100")->getSubfield("d");
				$treff[$hitcounter]['forfatterliv'] = substr($forfatterliv, 5); // fjerne feltkoden i starten
			}
		}
	
	
		if ($record->getField("110")) {
			$korporasjon                       = $record->getField("110")->getSubfield("a");
			$treff[$hitcounter]['korporasjon'] = substr($korporasjon, 5); // fjerne feltkoden i starten
		}
		
		if ($record->getField("20")) {
			$isbn                       = $record->getField("20")->getSubfield("a");
			$treff[$hitcounter]['isbn'] = substr($isbn, 5); // fjerne feltkoden i starten
			if ($record->getField("20")->getSubfield("q")) {
				$heftetbundet = $record->getField("20")->getSubfield("q");
				$treff[$hitcounter]['heftetbundet'] = substr($heftetbundet, 5); // fjerne feltkoden i starten
			}
		}
		
		if ($record->getField("520")) {
			$beskrivelse                       = $record->getField("520")->getSubfield("a");
			$treff[$hitcounter]['beskrivelse'] = substr($beskrivelse, 5); // fjerne feltkoden i starten
		}
		
		if ($record->getField("260")) {
			$utgitthvor                       = $record->getField("260")->getSubfield("a");
			$treff[$hitcounter]['utgitthvor'] = substr($utgitthvor, 5);
			$utgitthvem                       = $record->getField("260")->getSubfield("b");
			$treff[$hitcounter]['utgitthvem'] = substr($utgitthvem, 5);
			$utgittaar                        = $record->getField("260")->getSubfield("c");
			$utgittaar                        = substr($utgittaar, 5);
			$utgittaar                        = str_replace("[", "", $utgittaar); // disse to linjene fjerner [ og ] i årstall
			$treff[$hitcounter]['utgittaar']  = str_replace("]", "", $utgittaar);
		}
		
		if ($record->getField("019")) {
			$materialkode                       = $record->getField("019")->getSubfield("b");
			$treff[$hitcounter]['materialkode'] = substr($materialkode, 5);
			
			// Hvis flere adskilt med komma går vi for den siste
			
			if (stristr($treff[$hitcounter]['materialkode'], ",")) {
				$temp                               = explode(",", $treff[$hitcounter]['materialkode']);
				$treff[$hitcounter]['materialkode'] = end($temp);
			}
		} else {
			$treff[$hitcounter]['materialkode'] = ''; // initialize
		}
		
		// Ansvarsangivelse
		
		if (isset($treff[$hitcounter]['forfatter'])) {
			$treff[$hitcounter]['opphav'] = $treff[$hitcounter]['forfatter'];
			if (isset($treff[$hitcounter]['forfatterliv'])) {
				$treff[$hitcounter]['opphav'] .= " (" . $treff[$hitcounter]['forfatterliv'] . ")";
			}
		}

		
		if (isset($treff[$hitcounter]['korporasjon'])) {
			$treff[$hitcounter]['opphav'] = $treff[$hitcounter]['korporasjon'];
		}
		

		// Tittel
		$treff[$hitcounter]['tittelinfo'] = $treff[$hitcounter]['tittel'];
		if (isset($treff[$hitcounter]['subtittel'])) {
			$treff[$hitcounter]['tittelinfo'] .= " : " . $treff[$hitcounter]['subtittel'];
		}
		if ($treff[$hitcounter]['materialkode'] == 'ee') { // DVD?
			$treff[$hitcounter]['tittelinfo'] .= " : DVD";
		}
		
			// Gjøre det pent
			$treff[$hitcounter]['tittelinfo'] = str_replace(": :", ":", $treff[$hitcounter]['tittelinfo']);
		
		// Ikon - her bruker vi leader-feltet, posisjon 006/007
		
		$leader                             = $record->getLeader();
		$treff[$hitcounter]['materialkode'] = substr($leader, 6, 1);
		$treff[$hitcounter]['kategorikode'] = substr($leader, 7, 1);
		
		switch ($treff[$hitcounter]['materialkode']) {
			case "a":
				$treff[$hitcounter]['type'] = "bok";
				break;
			case "c":
				$treff[$hitcounter]['type'] = "note";
				break;
			case "i":
				$treff[$hitcounter]['type'] = "lyd";
				break;
			case "j":
				$treff[$hitcounter]['type'] = "musikk";
				break;
			case "g":
				$treff[$hitcounter]['type'] = "dvd";
				break;
			default:
				$treff[$hitcounter]['type'] = "ukjent";
				break;
		}
		
		if ($treff[$hitcounter]['kategorikode'] == "s") { // Ops, periodika!!
			$treff[$hitcounter]['type'] = "periodika";
		}

	
		// REPETERBARE FELTER SJEKKES HER
		
		foreach ($record->getFields() as $tag => $subfields) {
			
			// Dewey: Sjekke 082 $a

			if ($tag == '082') {
				foreach ($subfields->getSubfields() as $code => $value) {
					$ettfelt[(string) $code] = substr((string) $value, 5);
				}
				if (isset($ettfelt['a'])) {
					$dewey = $ettfelt['a'];
					$endewey[] = $dewey;
				}
			}

			// Generell note: Sjekke 500 $a

			if ($tag == '500') {
				foreach ($subfields->getSubfields() as $code => $value) {
					$ettfelt[(string) $code] = substr((string) $value, 5);
				}
				if ((isset($ettfelt['a'])) && ($ettfelt['a'] != "")) {
					$generellnote = $ettfelt['a'];
					$engenerellnote[] = $generellnote;
				}
			}

			// Emneord: Sjekke 653 $a

			if ($tag == '653') {
				foreach ($subfields->getSubfields() as $code => $value) {
					$ettfelt[(string) $code] = substr((string) $value, 5);
				}
				if (isset($ettfelt['a'])) {
					$emne = $ettfelt['a'];
					$ettemne[] = $emne;
				}
			}

			// Titler: Sjekke 740 $a
			
			if ($tag == '740') {
				foreach ($subfields->getSubfields() as $code => $value) {
					$ettfelt[(string) $code] = substr((string) $value, 5);
				}
				if ((isset($ettfelt['a'])) && ($ettfelt['a'] != "")) {
					$titler = $ettfelt['a'];
					$entittel[] = $titler;
				}
			}

			// Medarbeidere: Sjekke 511 $a
			
			if ($tag == '511') {
				foreach ($subfields->getSubfields() as $code => $value) {
					$ettfelt[(string) $code] = substr((string) $value, 5);
				}
				if ((isset($ettfelt['a'])) && ($ettfelt['a'] != "")) {
					$medarbeidere = $ettfelt['a'];
					$enmedarbeidere[] = $medarbeidere;
				}
			}
		}	
		// SLUTT PÅ REPETERBARE FELTER, LA OSS OPPDATERE OG RYDDE
		@$treff[$hitcounter]['dewey'] = $endewey;
		@$treff[$hitcounter]['emneord'] = $ettemne;
		@$treff[$hitcounter]['generellnote'] = $engenerellnote;
		@$treff[$hitcounter]['titler'] = $entittel;
		@$treff[$hitcounter]['medarbeidere'] = $enmedarbeidere;
	
		unset($endewey, $ettemne, $engenerellnote, $entittel, $enmedarbeidere);
		
		$hitcounter++;

	} // slutt på hvert item
	
	// Sjekke bestand i Bibsys HVIS denne option er satt	
	if (($hamedbibsys == 1) && (isset($bibsystocheck)) && (is_array($bibsystocheck))) {
		unset ($targetdokumenter);
		$bibsysstreng      = implode("&", $bibsystocheck);
		$tilgjengelig      = "http://alfa-a.bibsys.no/services/json/availabilityService.jsp?" . $bibsysstreng;
		$hvaertilgjengelig = json_decode(get_content($tilgjengelig));		
//domp ($hvaertilgjengelig);
		foreach ($hvaertilgjengelig->list->documents as $ettdokument) {
//rop ($ettdokument->bibcode);
			if ($ettdokument->bibcode == $avdkode) {
				$targetdokumenter[] = $ettdokument; // Hvis vår avdkode vil vi ha den
			}
			if (($ettdokument->circulationStatus == "0") && ($ettdokument->useRestriction == "0")) {
				$targetdokumenter[] = $ettdokument; // Dette betyr digitalt tilgjengelig... tror vi. 
			}

		}
		$targetdokumenter = array_map("unserialize", array_unique(array_map("serialize", $targetdokumenter))); // fjerne duplikater
		// Bibsys gir treff også på tapte dokumenter, dokumenter som ikke finnes... så hvis bestandsinfo ikke returneres dropper vi treffet
		
		foreach ($treff as $mangetreff => &$etttreff) { // for hvert treff i trefflista
			if (is_array($targetdokumenter)) {
				foreach ($targetdokumenter as $ettdokument) { // for hvert bestandstreff
					if ($etttreff['identifier'] == $ettdokument->objektId) {
						$etttreff['bestand'][] = $ettdokument;
					}
				}
			$nyetreff[] = $etttreff;
			}				
		}

		// Må luke ut noen duplikater, siden treff f.eks. blir lagt til to ganger hvis to eksemplarer er tilgjengelige
		$nyetreff = array_map("unserialize", array_unique(array_map("serialize", $nyetreff))); // fjerne duplikater		
		return ($nyetreff);
		
	} else { // skal ikke sjekke bestand, vi tar den trefflista vi har		
		return ($treff);
	}
} // slutt på bibsys-søke-funksjon

//****************************************************************************************************
function bibsys_antalltreff($url) {
//****************************************************************************************************
	
	$sru_datafil = get_content($url);
	$sru_data    = simplexml_load_string($sru_datafil);
	
	$namespaces = $sru_data->getNameSpaces(true);
	$srw        = $sru_data->children($namespaces['srw']); // alle som er srw:ditten og srw:datten
	
	$antallfunnet = $srw->numberOfRecords;
	
	return $antallfunnet;
}

?>
