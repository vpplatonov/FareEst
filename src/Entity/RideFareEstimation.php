<?php
namespace Entity;

use Entity\Ride;
use Entity\RideAwareTrait;
use Entity\RideFareEstimationAwareInterface as FareEstimation;
use Entity\RideFilterAwareInterface as Filter;

class RideFareEstimation implements
    RideFareEstimationAwareInterface
{
    const SHOULD_REMOVED = 100;
    const MOVING = 10;
    const IDLE   = 0;
    const START_TIME = "00:00";
    const END_TIME   = "05:00";
    const END_DAY    = "23:59";
    const NIGHT      = 1;
    const DAY        = 2;

    const START_EACH_RIDE    = 1.19;
    const TOTAL_FARE_ATLEAST = 3.16;

    // working hours
    private static $fare  = [self::NIGHT => 0.68,
                             self::DAY   => 1.19,
                             self::IDLE  => 1.85
                            ];

    use RideAwareTrait;

    public function fareEstimation(Ride $ride)
    {
        $this->ride = $ride;
        $fareEstimation = 0;
        $totalRideIdle  = 0;
        $totalRideDay   = 0;
        $totalRideNight = 0;
        $is_filterd = false;
        $this->ride->resetRide();

        // Calculate real value for Ride.
        while(($tuple = $this->ride->getNextTuple()) !== null ) {

            if (isset($tuple[Ride::DISTANCE]) && $tuple[Ride::DISTANCE] != 0) {
                $is_filterd = true;

                // Convert to data time format
                $tuple_time = date("H:i",$tuple[Ride::TIMESTAMP]);

                switch ( self::START_TIME <= $tuple_time && $tuple_time <= self::END_TIME) {
                    case true :
                        $totalRideNight += $tuple[Ride::DISTANCE];
                        break;
                    case false:
                        $totalRideDay += $tuple[Ride::DISTANCE];
                        break;
                }
            }
            elseif (isset($tuple[Ride::IDLE]) && $tuple[Ride::IDLE] != 0 ) {
                $is_filterd = true;
                $totalRideIdle += $tuple[Ride::IDLE];
            }
        }
        if (!$is_filterd ) {
            throw new \Exception('MUST use RideFilter before calculate Estimation');
        }

        // Calculate total cost.
        $fareEstimation = self::START_EACH_RIDE +
                          $totalRideIdle  * static::$fare[self::IDLE] +
                          $totalRideDay   * static::$fare[self::DAY]  +
                          $totalRideNight * static::$fare[self::NIGHT];

        return $fareEstimation > self::TOTAL_FARE_ATLEAST ?  $fareEstimation : self::TOTAL_FARE_ATLEAST;
    }
}

