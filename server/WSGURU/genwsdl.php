<?php
require "vendor/autoload.php";
require "Presensi.php";

$gen = new \PHP2WSDL\PHPClass2WSDL("Presensi", "http://localhost/wsguru/server.php");

$gen->generateWSDL();
file_put_contents("presensi.wsdl", $gen->dump());
echo "Done!";