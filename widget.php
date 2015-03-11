<?php 

$resultatside = $instance['resultatside']; // ID til side som skal være TARGET for FORM
$title = apply_filters( 'widget_title', $instance['title'] );
$enkeltpost = get_option('wl_ils_option_enkeltpost' , '');

echo $before_widget;

// Finne guid for ID resultatside, sette som target

// Display the widget

if ($title) {
	echo $before_title . $title . $after_title;
}

$resultatperma = get_permalink($resultatside);

if (trim($enkeltpost) != "") {
	if (stristr($resultatperma, "?")) {
		$resultatperma .= "&enkeltposturl=" . base64_encode(get_permalink($enkeltpost));
	} else {
		$resultatperma .= "?enkeltposturl=" . base64_encode(get_permalink($enkeltpost));
	}
}


echo "<form target=\"_self\" action=\"" . $resultatperma . "\" method=\"POST\">\n";
echo "<input type=\"text\" id=\"search\" name=\"webloftsok_query\" placeholder=\"Søkeord...\" accept-charset=\"utf-8\" />";	 

echo "</form>";

echo $after_widget;

?>
