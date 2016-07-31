<?php
$filename = "lotes/" . $_GET["filename"];
if(!file_exists($filename))
    echo "Arquivo não encontrado";
else
{
    //Exporta em formato txt para download
    header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    header ("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
    header ("Cache-Control: no-cache, must-revalidate");
    header ("Pragma: no-cache");
    header ("Content-Type: text/plain;");
    header ("Content-Disposition: attachment; filename=\"" . $_GET["filename"] . "\"" );
    header ("Content-Description: PHP Generated Data" );
    header ("Content-Transfer-Encoding: binary");
    echo file_get_contents($filename);
    die();
}
?>