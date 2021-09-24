<?php

\error_reporting(E_ALL);

include_once \dirname(__DIR__).'/../vendor/autoload.php';
include_once __DIR__.'/../templates/base.php';

/********************************
 Create the Pricehubble object,
 ********************************/
function getPricehubble()
{
    return new Antistatique\Pricehubble\Pricehubble();
}
