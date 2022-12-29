<?php
require 'vendor/autoload.php';

$getCityDo = new \Diaojinlong\GetCity\GetCityDo();
echo $getCityDo->getJsonData(2);