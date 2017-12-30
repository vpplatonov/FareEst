<?php
namespace Entity;

use Entity\Ride;
use Entity\RideAwareTrait;
use Entity\RideFareEstimation;
use Entity\RideFilterAwareInterface;

class RideFilter implements
    RideFilterAwareInterface
{
    /**
     * Radius in km.
     * @var int
     */
    const EARTH_RADIUS = 6371;

    use RideAwareTrait;

    public function filterSegments(Ride $ride)
    {
        $this->ride = $ride;
        $this->ride->resetRide();
        $segments = [];
        while(($segment = $this->ride->getNextSegment()) !== null ) {
            try {
                $tuple_param = $this->validateSegment($segment);
                $this->ride->setTupleParam($tuple_param);
            }
            catch (\Exception $e) {
    //            echo $e->getMessage() . ' in tuple ' . $segment[0][Ride::TIMESTAMP] . "\n";
                $this->ride->repareSegment();
            }
        }

        // remove last tuple;
        $this->ride->repareSegment();
        return $this->ride;
    }

    protected function validateSegment(array $segment)
    {
        if (count($segment) <> 2) {
            throw new \Exception('Segment MUST be array w dimensions 2');
        }
        // current tuple & next tuple,
        $segment;
        $c_tuple = 0;
        $n_tuple = 1;

        // Elapsed time in hours
        $Dt = $segment[$n_tuple][Ride::TIMESTAMP] - $segment[$c_tuple][Ride::TIMESTAMP];
        // remove missed tuple
        if ($Dt == 0 ) { // || ($U = $Ds/$Dt) > RideFareEstimation::SHOULD_REMOVED)) {
            throw new \Exception('missed pair of tuples w Dt: 0 , remove last of them');
        }

        $Dt = $Dt / 3600;

        // Covered Distance.
        // Use https://en.wikipedia.org/wiki/Haversine_formula
        $Ds = haversineGreatCircleDistance(
                $segment[$c_tuple][Ride::LAT],
                $segment[$c_tuple][Ride::LNG],
                $segment[$n_tuple][Ride::LAT],
                $segment[$n_tuple][Ride::LNG],
                self::EARTH_RADIUS);

        // remove missed tuple
        if (($U = $Ds/$Dt) > RideFareEstimation::SHOULD_REMOVED) {
            throw new \Exception('missed pair of tuples w U: ' . $U . ' remove last of them');
        }
        elseif ($U < RideFareEstimation::MOVING) {
            $segment[$c_tuple][Ride::DISTANCE] = 0;
            $segment[$c_tuple][Ride::IDLE] = $Dt;
            $this->ride->Idle += $Dt;
        }
        else {
            $segment[$c_tuple][Ride::IDLE] = 0;
            $segment[$c_tuple][Ride::DISTANCE] = $Ds;
            $this->ride->Ds += $Ds;
            $this->ride->Dt += $Dt;
        }

        return $segment[$c_tuple];
    }
}

/**
 * Calculates the great-circle distance between two points, with
 * the Haversine formula.
 * @param float $latitudeFrom Latitude of start point in [deg decimal]
 * @param float $longitudeFrom Longitude of start point in [deg decimal]
 * @param float $latitudeTo Latitude of target point in [deg decimal]
 * @param float $longitudeTo Longitude of target point in [deg decimal]
 * @param float $earthRadius Mean earth radius in [km]
 * @return float Distance between points in [km] (same as earthRadius)
 */
function haversineGreatCircleDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = RideFilter::EARTH_RADIUS)
{
    // convert from degree to radian
    $latFrom = deg2rad($latitudeFrom);
    $lngFrom = deg2rad($longitudeFrom);
    $latTo   = deg2rad($latitudeTo);
    $lngTo   = deg2rad($longitudeTo);

    $latDelta = $latTo - $latFrom;
    $lngDelta = $lngTo - $lngFrom;

    $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
             cos($latFrom) * cos($latTo) * pow(sin($lngDelta / 2), 2)));
    return $angle * $earthRadius;
}
