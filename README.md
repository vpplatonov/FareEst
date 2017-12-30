# TaxiBit Tets

Version 0.0.1 Created by Platonov Valerii

## Introduction

I have tried to design my code to be modular; 
Design patterns I have used are Singleton ( RideEstimationSaver entity ) & IoC ( Ride entity );

Dependencies are managed via composer. Simple run in root of project

php composer.phar install


Code follow PSR-2 coding standards and support PHP 5.4;

Design of my solution is capable of ingesting large datasets;

## Requirements

Composer;
PHPUnit ( will be installed by composer );

## Installation

Simply clone this project into your directory. Install composer.

Run project :

php module.php


Run test

./vendor/bin/phpunit ./tests/RideDataTest.php


Provided Classes
----------------

    'Entity\\Ride'
    'Entity\\RideAwareTrait'
    'Entity\\RideEstimateSaver'
    'Entity\\RideFareEstimation'
    'Entity\\RideFareEstimationAwareInterface'
    'Entity\\RideFilter'
    'Entity\\RideFilterAwareInterface'
    'Entity\\RideLoader'
    
    