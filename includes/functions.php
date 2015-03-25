<?php

function url_exists($url) {

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

function rop ($whattorop) {
	echo "<h1>" . $whattorop . "</h1>";
}

function domp ($whattodomp) {
	echo "<pre>";
	print_r ($whattodomp);
	echo "</pre>";
}

// Tar ut første X ord av en streng

function trunc($phrase, $max_words) {
   $phrase_array = explode(' ',$phrase);
   if(count($phrase_array) > $max_words && $max_words > 0)
      $phrase = implode(' ',array_slice($phrase_array, 0, $max_words)).'...';
   return $phrase;
}


// Failsafe function to read file

function get_content($url) { // Les inn en fil
//echo "<h1>get_content: " . $url . "</h1>";
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

function ilsdot($color) {

if ($color == "green") {

return ("data:image/png;base64,
iVBORw0KGgoAAAANSUhEUgAAAAwAAAAMCAIAAADZF8uwAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH3wMZDBIQKVT/TAAAAUJJREFUGNN9kE8ogwEYh3/v+32bGa1NDKsVO7FRi4iD4uZIcXCTKNnBGTni4uDiiAOS4y4rbv4c5LDEZVlbitlqpk1r9tm+73UYOTDP9fn1OzwkIgAAnDxe7d0H47lnIvQ5fQHfZKe9vaKoMpq/2DiOnRb1D110ACZWrWrten9grmMMAAPYvN0/jIay2luxrJWMcskoF0rFl/fMyvX2WTIMgF+13NbdUb5UIGIiIhCBiIhISRcya+FdAHyZuknkU0SMXxAr58lwopDmu0yMSEEVNF2L557YgIH/IAPCXodHRK82MSmqx+bioVa/09oo8sefiD7Q3O2ua2GnpWHBO1GrWkQMgXxrEdEbLI5l/8xXp9We2fG2EZu53swmJmZii2p21NiX/NOj7sGf4gB2IsGDaCiSfTCx0tvkXeyaGnb1VtQn4BeAqCieO4AAAAAASUVORK5CYII=");

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



function bestandsinfo ($status , $restriction) {
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


?>
