<?php

$resultatside = $instance['resultatside']; // ID til side som skal være TARGET for FORM
$title = apply_filters( 'widget_title', $instance['title'] );
$enkeltpost = get_option('wl_ils_option_enkeltpost' , '');
$katalog = $instance['katalog']; // Hvilken bibliotekkatalog skal vi søke i
$knappefarge = $instance['knappefarge']; // Bakgrunnsfarge på søkeknapp
$knappetekstfarge = $instance['knappetekstfarge']; // Fargen på knapp
$rundkant = $instance['rundkant'] ? '15px' : '0px'; // Rund kant?


echo $before_widget;

// Finne guid for ID resultatside, sette som target

// Display the widget

if ($title) {
	echo $before_title . $title . $after_title;
}

// Plukk ut query string

$resultatperma = get_permalink($resultatside);
$sjokk = explode ("?" , $resultatperma); // alle query strings i $sjokk[1] HVIS DET ER NOEN

echo "<form class=\"wlils_widget\" target=\"_self\" action=\"" . $sjokk[0] . "\" method=\"GET\">\n";
echo "<input style=\"border-radius: " . $rundkant . ";\" type=\"text\" id=\"search\" name=\"webloftsok_query\" placeholder=\"Søkeord...\" accept-charset=\"utf-8\" />";
echo "<input type=\"hidden\" name=\"katalog\" value=\"" . $katalog . "\" />";

if (isset($sjokk[1])) {
	$parameters = explode ("&" , $sjokk[1]); // array med parametre
	if (is_array($parameters)) {
		foreach ($parameters as $parameter) {
			$ettparameter = explode ("=" , $parameter);
			echo "<input type=\"hidden\" name=\"" . $ettparameter[0] . "\" value=\"" . $ettparameter[1] . "\" />";
		}
	}
}

if (trim($enkeltpost) != "") {
	echo "<input type=\"hidden\" name=\"enkeltposturl\" value=\"" . base64_encode(get_permalink($enkeltpost)) . "\" />";
	}

echo '<input style="border-radius: ' . $rundkant . '; color: ' . $knappetekstfarge . '; background-image: none; background-color: ' . $knappefarge . '" type="submit" value="Søk" />' . "\n";
echo "</form>";
echo $after_widget;

?>
