<?php
namespace Entity;

use Entity\Ride;

interface RideFilterAwareInterface
{
    public function filterSegments(Ride $ride);
}
