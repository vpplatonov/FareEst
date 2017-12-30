<?php

use Entity\Ride;
use Entity\RideFilter;
use Entity\RideFareEstimation;

class RideDataTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Ride
     */
    private $ride;

    private static $tuples = [
            [1,37.966660,23.728308,1405594957],
            [1,37.966627,23.728263,1405594966],
            [1,37.966625,23.728263,1405594974]
    ];

    public function setUp()
    {
        $this->ride = new Ride(1,
                               $tuples = static::$tuples,
                               new RideFilter(),
                               new RideFareEstimation());
    }

    public function tearDown(){

    }

    public function testRide()
    {
        $this->assertEquals(2, $this->ride->getNumSegments());
        $this->assertEquals(0, $this->ride->Ds);
        $this->assertEquals(0, $this->ride->Dt);
        $this->assertEquals(0, $this->ride->Idle);
    }

    /**
     * @expectedException Exception
     */
    public function testRideExeption()
    {
        // generate fail test
        // $ride = new Ride(1,$tuples = static::$tuples);

        // genetate Exception
        $ride = new Ride(2,
                         [static::$tuples[0]],
                         new RideFilter(),
                         new RideFareEstimation());

        // must never execute
        $this->fail("Non full segment exeption waiting");
    }

    public function testRideSortTuples()
    {
        $tuples = [static::$tuples[1],static::$tuples[0]];
        $ride = new Ride(3,
                         $tuples,
                         new RideFilter(),
                         new RideFareEstimation());

        $segment = $ride->getNextSegment();
        $c_tuple = 0;
        $n_tuple = 1;

        $this->assertTrue($segment[$n_tuple][Ride::TIMESTAMP] - $segment[$c_tuple][Ride::TIMESTAMP] > 0,
                          'Don\'t Sorted array after SORT test Excepted');
    }

    public function testRideFilter()
    {
        $ride = clone($this->ride);
        $ride->rideProcess();

        $this->assertEquals(1, $ride->getNumSegments());

        $ride->resetRide();
        $tuple = $ride->getNextTuple();

        $this->assertEquals(static::$tuples[0][Ride::TIMESTAMP],$tuple[Ride::TIMESTAMP]);
        $this->assertTrue(isset($tuple[Ride::IDLE]),'Expect add calculated IDLE value to Ride obj');
        $this->assertTrue(isset($tuple[Ride::DISTANCE]),'Expect add calculated DISTANCE value to Ride obj');

    }

    /**
     * @expectedException Exception
     */
    public function testRideEstimationExeption()
    {
        $ride = clone($this->ride);

        // If don't use filter before - expect to Exception

        // calculate estimation
        $rideEstimated = new RideFareEstimation();
        $rideEstimated->fareEstimation($ride);

        // must never execute
        $this->fail("Not full segment exeption waiting");

    }

    public function testRideEstimationResult()
    {
        $ride = clone($this->ride);
        $Re = $ride->rideProcess();

        $this->assertEquals(RideFareEstimation::TOTAL_FARE_ATLEAST,$Re);

    }
}
