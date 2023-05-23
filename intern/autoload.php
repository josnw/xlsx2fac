<?php

include_once './vendor/autoload.php';
include_once './intern/data/sw_constants.php';
include_once './intern/data/sw_constants_private.php';

foreach (glob("./intern/classes/*.php") as $filename)
{
    include_once $filename;
}
foreach (glob("../intern/classes/*.php") as $filename)
{
    include_once $filename;
}

?>