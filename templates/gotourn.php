<?php

// skal motta lenke, bilde, tittel
// dynamisk lage og-data
// så videresende til lenke
// params: 0: Tittel 1: Beskrivelse 2: enkeltpostlenke 3: bilde 4: Forfatter 5: ISBN

$param = stristr($_SERVER['REQUEST_URI'] , "params="); // fra og med "params="
if (stristr($param , "&")) {
    $param = stristr($param , "&" , TRUE); // men bare fram til "&" hvis det finnes
}
$param = str_replace ("&" , "" , $param); // fjern &
$param = str_replace ("params=" , "" , $param); // fjern det andre

$param = urldecode(base64_decode($param));
$params = explode ("|x|", $param);

$bildeurl = $params[3];
list($width, $height, $type, $attr) = getimagesize($bildeurl);

header('Content-type: text/html; charset=utf-8');

?>
<html>
    <head>
        <meta charset="utf-8">

        <title>Du blir straks sendt videre</title>

        <meta name="twitter:card" content="photo" />
        <meta name="twitter:site" content="@bibvenn" />
        <meta name="twitter:title" content="<?php echo strip_tags(stripslashes($params[0]));?> (<?php echo strip_tags(stripslashes($params[4]));?>)" />
        <meta name="twitter:image" content="<?php echo urldecode($bildeurl);?>" />
        <meta name="twitter:url" content="<?php echo strip_tags(stripslashes($params[2]));?>" />

        <meta name="author" content="<?php echo strip_tags(stripslashes($params[4]));?>" />
        <meta property="og:type" content="book" />
        <meta property="book:author" content="<?php echo urlencode(strip_tags(stripslashes($params[4])));?>">
        <meta property="book:isbn" content="<?php echo trim(strip_tags(stripslashes($params[5])));?>">
        <meta property="og:description" content="<?php echo strip_tags(stripslashes($params[1]));?>">
        <meta property="og:title" content="<?php echo strip_tags(stripslashes($params[0]));?>">
        <meta property="og:image" content="<?php echo urldecode($bildeurl);?>">
        <meta property="og:image:type" content="image/jpeg">
        <meta property="og:image:height" content="<?php echo $height;?>">
        <meta property="og:image:width" content="<?php echo $width;?>">

        <meta http-equiv="refresh" content="1;<?php echo strip_tags(stripslashes($params[2]));?>">
    </head>
    <body>
        <!-- <img src="<?php echo $url; ?>" />-->
    </body>
</html>
