<?php
namespace Entity;

use Entity\Ride;

interface RideFareEstimationAwareInterface
{
    public function fareEstimation(Ride $ride);
}
