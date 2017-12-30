<?php
namespace Entity;

use Entity\Ride;

class RideEstimateSaver
{
    const FILE_NAME = 'ride_estimated';
    // how much estimated rides results saved in file in one time.
    const EST_SIZE = 5;

    private $filename = 'test.csv';
    private $ride_estimated = [];

    private static $instance;

    /**
     * pattern singleton
     * in this way may be injected to Ride object
     * @param array $ride_estimated
     */
    private function __construct($ride_estimated = null)
    {
        if (isset($ride_estimated) && !empty($ride_estimated)) {
            $this->ride_estimated = $ride_estimated;
        }

        // if file exists remove them.
        $full_path = realpath('./data') . '/' . $this->filename;
        if (file_exists($full_path)) {
             unlink($full_path);
        }
        return $this;
    }

    public function getRideEstimateSaver()
    {
        if (empty(self::$instance)) {
            self::$instance = new RideEstimateSaver();
        }
        return self::$instance;

    }

    public function setFieName($filename)
    {
        $this->filename = $filename;
        return $this;
    }

    public function rideEstimationAdd($ride_estimated)
    {
        $this->ride_estimated[] = $ride_estimated;
        if ($this->getEstimationSize() > self::EST_SIZE ) {
            $this->rideEstimationSave();
        }
        return $this;
    }

    public function getEstimationSize()
    {
        return count($this->ride_estimated);
    }

    public function rideEstimationSave()
    {
        if (empty($this->ride_estimated)) return;

        $dir = realpath('./data');
        $handle = fopen($dir . '/' . $this->filename,'a');

        foreach($this->ride_estimated as $key => $r_est) {
            fputcsv($handle,$r_est);
        }

        fclose($handle);

        $this->ride_estimated = [];
        return;
    }

}
