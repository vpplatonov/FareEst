<?php
namespace Entity;

use Entity\Ride;
use Entity\RideFareEstimation;
use Entity\RideFilter;

class RideLoader
{
    const FILE_PATTERN = '/^(.*)\.csv/i';
    const DELIMITER = ',';

    private $file_name = '';
    private $file_seek_pointer = 0;
    private $file_num_lines = 0;
    private $file_index = 0;
    private $file_dir = '';

    public function __construct($filename)
    {
        if (empty($filename)) {
            $this->takeThemAll();
        }

        return $this;
    }

    public function setFieName($filename)
    {
        $this->filename = $filename;
    }

    public function takeThemAll()
    {
        $dir = realpath('./data');
        $file_array = file_scan_directory($dir, self::FILE_PATTERN);

        if (empty($file_array)) {
            throw new \Exeption('invalid file extension ' . $file_ext);
        }
        $this->file_dir = $dir;
        $this->file_name = $file_array;
    }

    /**
     * generate Ride object from file.
     *
     * return @ride Entity\Ride
     */
    public function getRide()
    {
        $id = 0;
        $tuples = [];
        $file = '';

        if (is_array($this->file_name)) {
            reset($this->file_name);
            $file = current($this->file_name); //[$this->file_index];
  //          echo $file . "\n";
        }
        else {
            $file = $this->file_name;
        }

        $handle = fopen($this->file_dir . '/' . $file,'r');

        if ($handle) {

            if ($this->file_seek_pointer != 0) {
                fseek($handle,$this->file_seek_pointer);
            }
            while ($line = fgetcsv($handle,128,self::DELIMITER)) {

                if ($id != 0 && $line[0] != $id ) {
                    break;
                }
                else {
                    $id = $line[0];
                }
                array_push($tuples, $line);

            }

            if (feof($handle)) {
                array_shift($this->file_name);
                $this->file_seek_pointer = 0;
            }
            else {
                $this->file_seek_pointer = ftell($handle);
            }

            fclose($handle);

            if (empty($tuples) && $this->file_seek_pointer == 0) {
                // next line moved to module in catch exception.
                $file_saver = RideEstimateSaver::getRideEstimateSaver();
                $file_saver->rideEstimationSave();
                // dont return null or false - because Exception present in Ride::__construct
                // return false;
            }
        }
        else {
            throw new \Exeption('error opening file for reading ' . $file );
        }

        // Exception present in Ride::__construct
        $ride = new Ride($id,
                         $tuples,
                         new RideFilter(),
                         new RideFareEstimation());
        $ride->setFileSaver(RideEstimateSaver::getRideEstimateSaver());
        return $ride;
    }
}

function file_scan_directory($dir,$pattern)
{
    $file_array = scandir($dir);
    foreach ($file_array as $key => $file) {
        preg_match($pattern,$file,$matches);
        if (!isset($matches[0])) {
            unset ($file_array[$key]);
        }
    }

    return  $file_array;
}
