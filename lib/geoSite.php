<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of geoSite
 *
 * @author tony
 */
require_once 'reportCore.php';
class geoSite extends reportCore {
    //put your code here
    public $site_data = '';
    public $fact_data;
    public $search_data;
    public $dbname = 'ops';
    
    public function __construct( $search_file='', $fact_file='', $options='') 
    {
        
        $this->fact_data = $this->csv_to_array($fact_file);
        $this->search_data = $this->csv_to_array($search_file);
        $options = array('dbname'=>'ops');
        parent::__construct($start_date='', $end_date='', $tenant='', $options);

        
        $this->table = 'geoSiteDetails';
        $this->site_data = $this->getData('select * from '.$this->table);
        //print_r($this->site_data);die();
    }
    
    function csv_to_array($filename='', $delimiter=',')
    {
        if(!file_exists($filename) || !is_readable($filename))
            return FALSE;

        $header = NULL;
        $data = array();
        if (($handle = fopen($filename, 'r')) !== FALSE)
        {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE)
            {
                if(!$header)
                    $header = $row;
                else
                    $data[] = array_combine($header, $row);
            }
            fclose($handle);
        }
        return $data;
    }
    
    public function populateResults()
    {
        $out = array();
        foreach( $this->search_data as $data)
        {
            $content = '<strong>'.$data['site_name'].'</strong>, ';
            $content .= '<b>Grid Availability:</b> '.$this->getAverageGridAvailability(array($data['longitude'], $data['latitude'])).' %';
            
            $out[] = array($data['latitude'], $data['longitude'], $data['site_name'], $content);
        }
        
        return $out;
    }
    
    public function populateSites()
    {
        $out = array();
        foreach( $this->site_data as $data)
        {
//            $content = '<strong>'.$data['site_name'].'</strong>, ';
//            $content .= '<b>Grid Uptime:</b> '.$this->getAverageGridAvailability(array($data['longitude'], $data['latitude'])).' %';
            $cont = str_replace(' ', '_', $data['site_name']);
            $out[] = array($data['latitude'], $data['longitude'], $data['site_id'],$data['city_town']);
        }
       //print_r($out);die();
        return $out;
    }

    /**
     * 
     * @param array $point Array of longitude and latitude of a point
     * @return integer A percent of Grid available
     */
    public function getAverageGridAvailability($point)
    {
        $vals = array();
        $close = $this->getClosestSites($point, 5, 'K');
        foreach ($close as $site => $distance)
        {
            
            $vals[] = $this->getGensetRunHrs($site);
        }
        
        $average = array_sum($vals) / count($vals);
        
        return round(((744 - $average) / 744) * 100, 0);
    }
    
    public function getGensetRunHrs($site_id)
    {
        $hrs = '';
        
        foreach ($this->fact_data as $data)
        {
            if($data['site_id'] == $site_id)
            {
                $hrs = $data['run_hours'];
            }
        }
        
        return $hrs;
    }

    /**
     * Get nearest sites to a point designated by a longitide and latitude
     * @param array $point An array of ongitude and latitude of a point
     * @param integer $num Number of sites to return
     * @param string $unit Unit of distance to be returned. Available units are: K=>kilometers, M=>Miles, N=>Nautical Miles
     * @return Array Associative array of site_id => distance where distance is defined by 
     */
    public function getClosestSites($point, $num, $unit)
    {
        $long = $point[0];
        $lat = $point[1];
        
        $output = array();
        
        foreach ($this->site_data as $row)
        {
            $lng = $row['longitude'];
            $ltd = $row['latitude'];
            $site_id = $row['site_id'];
            $distance = $this->distance($lat, $long, $ltd, $lng, $unit);
            
            $output[$site_id] = $distance;
        }
        asort($output);
        
        return array_slice($output, 0, $num, true);
    }
    /**
     * Calculate the distance between two points on the earth's surface
     * @param type $lat1
     * @param type $lon1
     * @param type $lat2
     * @param type $lon2
     * @param type $unit
     * @return type
     */
    function distance($lat1, $lon1, $lat2, $lon2, $unit) 
    {

      $theta = $lon1 - $lon2;
      $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
      $dist = acos($dist);
      $dist = rad2deg($dist);
      $miles = $dist * 60 * 1.1515;
      $unit = strtoupper($unit);

      if ($unit == "K") {
        return ($miles * 1.609344);
      } else if ($unit == "N") {
          return ($miles * 0.8684);
        } else {
            return $miles;
          }
    }
    /**
     * Calculate the distance between two points on the earth's surface
     * @param type $latitudeFrom
     * @param type $longitudeFrom
     * @param type $latitudeTo
     * @param type $longitudeTo
     * @param type $earthRadius
     * @return type
     */
    public function vincentyGreatCircleDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000)
    {
      // convert from degrees to radians
      $latFrom = deg2rad($latitudeFrom);
      $lonFrom = deg2rad($longitudeFrom);
      $latTo = deg2rad($latitudeTo);
      $lonTo = deg2rad($longitudeTo);

      $lonDelta = $lonTo - $lonFrom;
      $a = pow(cos($latTo) * sin($lonDelta), 2) + pow(cos($latFrom) * sin($latTo) - sin($latFrom) * cos($latTo) * cos($lonDelta), 2);
      $b = sin($latFrom) * sin($latTo) + cos($latFrom) * cos($latTo) * cos($lonDelta);

      $angle = atan2(sqrt($a), $b);
      return $angle * $earthRadius;
    }
    
    
    
}
