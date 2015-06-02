<?php

// Hent alle innlegg (og sider samtidig?)
// Lagre id og tittel der hvor [wl-ils] forekommer

$args = array(
'post_type' => array('post' , 'page'),
'posts_per_page' => '-1'
    );

if ($post_query = get_posts($args)) {

	$hits = '';
	foreach ($post_query as $post) {
	    if ( stripos($post->post_content, '[wl-ils]') !== false ) { // kortkoden finnes!!
			$hits[] = $post->ID . "|x|" . $post->post_title;
		}
	}
	if (is_array($hits)) { // vi har minst ett treff
		echo "<p>\n";
		echo "<label for=\"" . $this->get_field_id( 'title' ) . "\">Tittel:\n";
		echo "<input class=\"widefat\" id=\"" . $this->get_field_id( 'title' ) . "\" name=\"" . $this->get_field_name( 'title' ) . "\" type=\"text\" value=\"" . $title . "\" />\n";
		echo "</label>\n";
		echo "</p>\n\n";

		echo "<p>\n";
		echo "<label for=\"" . $this->get_field_id('resultatside') . "\">Hvilken bibliotekkatalog vil du søke i?</label>\n";
		echo "<select name=\"" . $this->get_field_name('katalog') . "\" id=\"" . $this->get_field_id('katalog') . "\" class=\"widefat\">\n";
		include ("serverliste.php");
		foreach ($bibliotek as $ettbibliotek) {
			$hepp = '';
			$temp = explode ("|x|" , $ettbibliotek);
			if ($temp[1] == $katalog) {
				$hepp = " selected";
			}
			echo "<option value=\"" . $temp[1] . "\"" . $hepp . ">" . $temp[0] . "</option>\n";
		}

		echo "</select>\n";
		echo "</p>\n";

		echo "<p>\n";
		echo "<label for=\"" . $this->get_field_id('resultatside') . "\">Hvilken side vil du sende trefflisten til?</label>\n";
		echo "<select name=\"" . $this->get_field_name('resultatside') . "\" id=\"" . $this->get_field_id('resultatside') . "\" class=\"widefat\">\n";

		foreach ($hits as $hit) {
			$digg = '';
			list ($id , $tittel) = explode ("|x|" , $hit);
			if ($id == $resultatside) { $digg = " selected"; }
			echo "<option value=\"" . $id . "\"" . $digg . ">" . $tittel . "</option>\n";
		}

		echo "</select>\n";
		echo "</p>\n";
	} else {
		echo "Du må sette inn kortkoden [wl-ils] i et innlegg eller på en side først!";
	}
}

?>
