<?php

include 'autoloader.php';

use Entity\Ride;
use Entity\RideFareEstimation;
use Entity\RideFilter;
use Entity\RideLoader;
use Entity\RideEstimateSaver;

// if file name omitted - load all *.csv in ./data dir
$file_loader = new RideLoader('');

try {
    while($ride = $file_loader->getRide()) {

    $Re = $ride->rideProcess();

    echo ' Ds: ' . $ride->Ds . ' U: ' .  $ride->Ds/$ride->Dt . ' $: ' . round($Re,2) . "\n" ;

    }
}
catch (\Exception $e) {
    echo "finita la comedia\n";
    $file_saver = RideEstimateSaver::getRideEstimateSaver();
}

$file_saver->rideEstimationSave();

