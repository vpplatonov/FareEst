<?php
namespace Entity;

use Entity\RideFareEstimationAwareInterface as FareEstimation;
use Entity\RideFilterAwareInterface as Filter;
use Entity\RideEstimateSaver;

class Ride
{
    const LAT = 1;
    const LNG = 2;
    const TIMESTAMP = 3;
    const DISTANCE  = 4;
    const IDLE      = 5;

    private $id_ride = null;
    private $tuples = [];
    private $c_segment = 0;
    private $missed_tuple = 0;

    /**
     * @var FareEstimation
     */
    private $ride_fare_estimation;

    /**
     * @var Filter
     */
    private $ride_filter;

    /**
     * @var RideEstimateSaver
     */
    private $file_saver;

    public $Ds = 0;
    public $Dt = 0;
    public $Idle = 0;

    public function __construct($id_ride,
                                array $tuples,
                                Filter $fl,
                                FareEstimation $fe)
    {
        $this->id_ride = $id_ride;
        $this->tuples = $tuples;

        if ($this->getNumSegments() < 1) {
            throw new \Exception("must be one segment in tuples at list");
        }

        try {
            $this->testTuples();
        }
        catch (\Exception $e) {
            echo $e->getMessage() . ' in Ride with id: ' . $id_ride . "\n";
            $this->prepareTuples();
        }

        $this->setRideFilter($fl);
        $this->setRideFareEstimation($fe);

        return $this;
    }

    /**
     * Use pattern IoC
     * @param FareEstimation $fe
     * @return \Entity\RideFareEstimationAwareInterface
     */
    public function setRideFareEstimation(FareEstimation $fe)
    {
        if ($this->ride_fare_estimation == null)
        {
            $this->ride_fare_estimation = $fe;
        }
        return $this->ride_fare_estimation;
    }

    /**
     * @param Filter $fl
     * @return \Entity\RideFilterAwareInterface
     */
    public function setRideFilter(Filter $fl)
    {
        if ($this->ride_filter == null)
        {
            $this->ride_filter = $fl;
        }
        return $this->ride_filter;
    }

    public function setFileSaver(RideEstimateSaver $fs)
    {
        // Always the same obj - dont ned to check it.
        $this->file_saver = $fs;
    }

    public function rideProcess()
    {
        $this->ride_filter->filterSegments($this);
        $Re = $this->ride_fare_estimation->fareEstimation($this);

        // Help exclude from test process.
        if ($this->file_saver instanceof RideEstimateSaver)
        {
            $this->file_saver->rideEstimationAdd([$this->id_ride,round($Re,2)]);
        }
        return $Re;
    }

    public function prepareTuples()
    {
        $sort_array = [];
        foreach ($this->tuples as $key => $tuple )
        {
            $sort_array[] = $tuple[self::TIMESTAMP];
        }
        array_multisort($this->tuples,SORT_ASC,SORT_NUMERIC,$sort_array);
    }

    /**
     * @test
     * @throws \Exception
     */
    private function testTuples()
    {
        $prev_times = 0;
        foreach ($this->tuples as $key => $tuple ) {
            if ($tuple[self::TIMESTAMP] < $prev_times) {
                throw new \Exception("tuple did't ASC timestamp sort: prev " . $prev_times . " curr: " . $tuple[self::TIMESTAMP]);
            }
            $prev_times = $tuple[self::TIMESTAMP];
        }

        if (($Dt = $this->tuples[count($this->tuples) - 1][Ride::TIMESTAMP] - $this->tuples[0][Ride::TIMESTAMP]) / 3600 > 24) {
            throw new \Exception('very long Ride ' . $Dt);
        }
    }

    public function getNextSegment()
    {
        $prev_tuple_idx = 1;
        $this->c_segment++;
        if ($this->c_segment > $this->getNumSegments() + $this->missed_tuple) {
            $this->c_segment--;
            return null;
        }
        while (!isset($this->tuples[$this->c_segment - $prev_tuple_idx])) {
            $prev_tuple_idx++;
        }

        return [$this->tuples[$this->c_segment - $prev_tuple_idx],$this->tuples[$this->c_segment]];
    }

    public function getNextTuple()
    {
        if ($this->c_segment >= $this->getNumSegments() + $this->missed_tuple) {
            return null;
        }

        $tuple = $this->tuples[$this->c_segment];

        $next_tuple_idx = 1;
        while (!isset($this->tuples[$this->c_segment + $next_tuple_idx]) &&
               ($this->c_segment + $next_tuple_idx < $this->getNumSegments() + $this->missed_tuple )) {
            $next_tuple_idx++;
        }
        $this->c_segment += $next_tuple_idx;
        return $tuple;
    }

    public function repareSegment()
    {
        unset($this->tuples[$this->c_segment]);
        $this->missed_tuple++;
        return $this;
    }

    public function setTupleParam($tuple_param)
    {
        $prev_tuple_idx = 1;

        while (!isset($this->tuples[$this->c_segment - $prev_tuple_idx])) {
            $prev_tuple_idx++;
        }
        $this->tuples[$this->c_segment - $prev_tuple_idx] += $tuple_param;

        return $this;
    }

    public function getNumSegments()
    {
        return count($this->tuples) - 1;
    }

    public function resetRide()
    {
        $this->c_segment = 0;
        return $this;
    }

    public function getRideId()
    {
        return $this->id_ride;
    }

}
