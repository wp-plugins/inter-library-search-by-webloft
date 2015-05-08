<?php

// Viser alle bibkoder fra serverliste.php

require_once ("serverliste.php");
?>

<html><head><title>Oversikt over bibliotek med koder</title></head>
<body>
<h1>Sett inn s&oslash;keskjema med kortkode</h1>
<p>Under f&oslash;lger en oversikt over koder du kan bruke i kortkoden n&aring;r du skal sette inn et s&oslash;keskjema p&aring; en side eller i et innlegg. Dersom du ikke angir noen ting men bare bruker kortkoden <strong>[wl-ils]</strong> vil du f&aring; en s&oslash;keboks til det biblioteket du har angitt i innstikkets innstillinger i Wordpress. &Oslash;nsker du imidlertid &aring; ha s&oslash;kebokser til flere forskjellige bibliotekkataloger p&aring; sidene dine, kan du angi:
<br><br>
<strong>[wl-ils mittbibliotek=KODE]</strong>
<br><br>
... der KODE er hentet fra tabellen under, f.eks. [wil-ils mittbibliotek=2060200] for Drammen bibliotek.
</p>
<h2>Her er kodene</h2>

<table border="1">

<?php

foreach ($bibliotek as $ettbibliotek) {
	$temp = explode ("|x|" , $ettbibliotek);
	echo "<tr><td>" . utf8_decode($temp[0]) . "</td><td>" . $temp[1] . "</td></tr>\n";
}

?>

</table>
</body>
</html>
