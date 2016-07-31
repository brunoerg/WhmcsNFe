<?php
$filename = "lotes/" . $_GET["filename"];
//Exporta em formato txt para download

header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header ("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");
header ("Pragma: no-cache");
header ("Content-Type: text/plain;");
header ("Content-Disposition: attachment; filename=\"relatorio-" . uniqid() . ".txt\"" );
header ("Content-Description: PHP Generated Data" );
header ("Content-Transfer-Encoding: binary");

echo file_get_contents($filename);
die();

?>