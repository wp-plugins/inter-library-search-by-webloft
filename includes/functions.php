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


?>
