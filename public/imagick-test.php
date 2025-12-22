<?php
echo "PHP: ".PHP_VERSION."<br>";
echo "SAPI: ".php_sapi_name()."<br>";
echo "INI: ".php_ini_loaded_file()."<br>";
echo "extension_dir: ".ini_get('extension_dir')."<br>";
echo "imagick: ".(extension_loaded('imagick') ? "OK" : "YOK")."<br>";
