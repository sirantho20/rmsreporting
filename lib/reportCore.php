<?php
error_reporting(E_ERROR | E_PARSE);
set_time_limit(0);

class reportCore 
{
public $username = 'sa';
public $pw = 'AFtony19833';
public $dbname = 'inala_dump';
public $hostname = /*'htg-db-01\ops'*/'10.3.0.5';
private $table = 'vw_HTGRMSreportBaseview';
private $start_date = '2013-12-01 00:00:00';
private $end_date = '2013-12-31 23:59:59';
private $tenant = 'Vodafone';
private $pdo;
private $output_file_name = 'dump/';
private $data_headers = array();

public function __construct($start_date = '', $end_date = '', $tenant = '') 
{
    $this->start_date = $start_date.' 00:00:00';
    $this->end_date = $end_date.' 23:59:59';
    $this->tenant = $tenant;
    
    $this->output_file_name = 'dump/'.$this->tenant.date('YmdHis').'.csv';
    
    //create pdo connection based on os
    if( strtoupper(substr(PHP_OS, 0, 3)) =='LIN' )
    {
    $this->pdo = new PDO(
            "dblib:host = htgops ; dbname=$this->dbname",
            "$this->username",
            "$this->pw"
            );
    }
    elseif ( strtoupper(substr(PHP_OS, 0, 3)) =='WIN' ) 
    {
        $this->pdo = new PDO(
            "sqlsrv:server=$this->hostname\ops ; Database=$this->dbname",
            "$this->username",
            "$this->pw",
            array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                 )
            );
    }
    else 
    {
        $this->pdo = new PDO(
            "dblib:host=$this->hostname ; dbname=$this->dbname",
            "$this->username",
            "$this->pw"
            );
    }
    
}

public function createViewObject()
{
    $qr = file_get_contents('objects/baseview.sql');
    //echo $qr; die();
    return $this->getData($qr);
}

public function getData($qr)
{
    
    $qr = $this->pdo->prepare( $qr);
    //die($qr->queryString);
    $qr->execute();
    
    return $qr->fetchAll(PDO::FETCH_ASSOC);

}

public function generateReport()
{
    $result = array();
    
    foreach ( $this->getUniqueSites() as $record)
    {
        $site_id = $record['SAMUnitName'];
        $emu = $this->getEMU($site_id);
        $start_date = $this->getDateLocation($site_id, $emu, 'START');
        $end_date = $this->getDateLocation($site_id, $emu, 'END');
        
        $start_reading1 = $this->bigQuery($site_id, $emu, $start_date);
        $start_reading = $start_reading1[0];
        
        $end_reading1 = $this->bigQuery($site_id, $emu, $end_date);
        $end_reading = $end_reading1[0];
        //print_r($start_reading);die();
        //client phases
       
        $phases = array();
        if ($start_reading['Location1'] == $this->tenant)
        {
            array_push($phases, 1);
        }
        if( $start_reading['Location2'] == $this->tenant)
        {
            array_push($phases, 2);
        }
        if( $start_reading['Location3'] == $this->tenant)
        {
            array_push($phases, 3);
        }
        if( $start_reading['Location4'] == $this->tenant)
        {
            array_push($phases, 4);
        }
        if( $start_reading['Location5'] == $this->tenant)
        {
            array_push($phases, 5);
        }
        if( $start_reading['Location6'] == $this->tenant)
        {
            array_push($phases, 6);
        }
        
        $phase1_kwh = 0;
        $phase2_kwh = 0;
        $phase3_kwh = 0;
        $site_phase = 0;
        
        foreach ($phases as $phase)
        {
            $loc = 'KWH'.$phase;
            
            if( $phase == 1 || $phase == 4)
            {
                $site_phase = 1;
                $phase1_kwh += $end_reading[$loc] - $start_reading[$loc];
            }
            elseif( $phase == 2 || $phase == 5)
            {
                $site_phase = 2;
                $phase2_kwh += $end_reading[$loc] - $start_reading[$loc];
            }
            elseif( $phase == 3 || $phase == 6)
            {
                $site_phase = 3;
                $phase3_kwh += $end_reading[$loc] - $start_reading[$loc];
            }
            
        }
        
        $output = array(
            'site_id' => $start_reading['SAMUnitName'],
            'site_name'=>$start_reading['SAMLocation'],
            'tenant'=>$this->tenant,
            'phase1_kwh'=>$phase1_kwh,
            'phase2_kwh'=>$phase2_kwh,
            'phase3_kwh'=>$phase3_kwh,
            'total_kwh' => $phase1_kwh + $phase2_kwh + $phase3_kwh,
            
        );
        
        $result[] = $output;
        
        //break;
    }
    //Load Data headers;
    $record1 = $result[0];
    $this->data_headers = array_keys($record1);
    return $result;
}

public function bigQuery($site, $emu, $date)
{
    $qr = "select * from $this->table where SAMUnitName = '$site' and EMUUnitName = '$emu' and readingdate = '$date' and ".$this->queryConstraints();
    return $this->getData($qr);
}

public function getDateLocation($site, $emu, $pos)
{
    if ( $pos =='END')
    {
        $qr = "select MAX(ReadingDate) readingdate from $this->table where SAMUnitName = '$site' and EMUUnitName = '$emu' and ".$this->queryConstraints();
    }
    elseif ( $pos == 'START' )
    {
         $qr = "select MIN(ReadingDate) readingdate from $this->table where SAMUnitName = '$site' and EMUUnitName = '$emu' and ".$this->queryConstraints();
    }
    
    $re = $this->getData($qr);
    
    return $re[0]['readingdate'];
    
}
public function getUniqueSites()
{
    $qr = "select distinct(SAMUnitName) from ".$this->table.' where'.$this->queryConstraints();
    return $this->getData($qr);
}
public function getEMU($site)
{
    $qr = "select DISTINCT EMUUnitName from ".$this->table.' where SAMUnitName = '.$site.' and '.$this->queryConstraints();
    $re = $this->getData($qr);
    return $re[0]['EMUUnitName'];
}

public function queryConstraints()
{
    return " ReadingDate between '$this->start_date' and '$this->end_date' and (location1 = '$this->tenant' or location4 = '$this->tenant')";
}

public function execInBackground($cmd) {
    if (substr(php_uname(), 0, 7) == "Windows"){
        pclose(popen("start /B ". $cmd, "r")); 
    }
    else {
        exec($cmd . " > /dev/null &");  
    }
} 

public function dumpCSV()
{
    $data = $this->generateReport();
    
    $fh = @fopen($this->output_file_name, 'w');
    @fputcsv($fh, $this->data_headers);
    foreach ( $data as $record)
    {
        @fputcsv($fh, $record);
    }
    $this->downloadCSV($fh);
    fclose($fh);
}

public function downloadCSV($content)
{
    $fileName = $this->output_file_name;

    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename=' . $fileName);
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    ob_clean();
    flush();
    
    readfile($fileName);
    
    unlink($fileName);
}


}


