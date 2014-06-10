<?php
require 'vendor/autoload.php';
//error_reporting(E_ERROR | E_PARSE);
set_time_limit(0);
session_start();

class reportCore 
{
public $username = 'samems_gh_helios';
public $pw = 'sam1029';
public $dbname = 'sam_ems_gh_helios';
public $hostname = /*'htg-db-01\ops'*/'10.3.0.11';
public $table = 'vw_HTGRMSreportBaseview';
private $start_date = '2013-12-01 00:00:00';
private $end_date = '2013-12-31 23:59:59';
private $tenant = 'Vodafone';
public $pdo;
private $output_file_name = 'dump/';
private $data_headers = array();

public function __construct($start_date = '', $end_date = '', $tenant = '', $options = '') 
{
    if(is_array($options))
    {
        extract($options);
        if(isset($dbname))
        {
            $this->dbname = $dbname;
        }
    }

    $this->start_date = $start_date.' 00:00:00';
    $this->end_date = $end_date.' 23:59:59';
    $this->tenant = $tenant;
    
    $this->output_file_name = 'dump/'.$_SESSION['user'].'_'.$this->tenant.'_'.date('YmdHis').'.csv';
    
    //create pdo connection based on os
    if( strtoupper(substr(PHP_OS, 0, 3)) =='LIN' )
    {
    $this->pdo = new PDO(
            "dblib:dbname=$this->dbname;host=htgops;charset=utf8",
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

public function migrateData()
{
    $tbl_dll = "CREATE TABLE if not exists $this->table (
       SAMUnitName text
      ,SAMLocation text
      ,EMUUnitName text
      ,ReadingDate numeric
      ,Location1 text
      ,Location2 text
      ,Location3 text
      ,Location4 text
      ,Location5 text
      ,KWH1 numeric
      ,KWH2 numeric
      ,KWH3 numeric
      ,KWH4 numeric
      ,KWH5 numeric
      ,KWH6 numeric
      ,Location6 text
      ,CT1Conf numeric
      ,CT2Conf numeric
      ,CT3Conf numeric
      ,CT4Conf numeric
      ,CT5Conf numeric
      ,CT6Conf numeric
      ,ServiceStateText text)";
    
    $sqlite = new PDO("sqlite:objects/db.sq3", "", "", array(PDO::ATTR_PERSISTENT => TRUE));
    $sqlite->exec("drop table if exists $this->table");
    $sqlite->exec($tbl_dll);
    
    //get sql data
    $qr = "select * from $this->table where ".$this->queryConstraints();
    $data = $this->getData($qr);
    $first = $data[0];
    $fields = array_keys($first);
    
    $fh = @fopen('dump/tmp.csv', 'w');
    @fputcsv($fh, $fields);
    foreach ( $data as $record)
    {
        @fputcsv($fh, $record);
    }
    
    fclose($fh);
    
    $this->import_csv_to_sqlite($sqlite, 'dump/tmp.csv', array('table'=>$this->table));
    
}

public function import_csv_to_sqlite(&$pdo, $csv_path, $options = array())
{
	$delimiter = @$options['delimiter'];
        $table = @$options['table'];
        $fields = @$options['fields'];
	
	if (($csv_handle = fopen($csv_path, "r")) === FALSE)
        {
		throw new Exception('Cannot open CSV file');
        }
		
	if(!isset($delimiter))
        {
		$delimiter = ',';
        }
		
	if(!isset($table))
        {
		$table = preg_replace("/[^A-Z0-9]/i", '', basename($csv_path));
        }
	
	if(!isset($fields)){
		$fields = array_map(function ($field){
			return strtolower(preg_replace("/[^A-Z0-9]/i", '', $field));
		}, fgetcsv($csv_handle, 0, $delimiter));
	}
	
	$create_fields_str = join(', ', array_map(function ($field){
		return "$field TEXT NULL";
	}, $fields));
	
	$pdo->beginTransaction();
	
	$create_table_sql = "CREATE TABLE IF NOT EXISTS $table ($create_fields_str)";
	$pdo->exec($create_table_sql);
 
	$insert_fields_str = join(', ', $fields);
	$insert_values_str = join(', ', array_fill(0, count($fields),  '?'));
	$insert_sql = "INSERT INTO $table ($insert_fields_str) VALUES ($insert_values_str)";
	$insert_sth = $pdo->prepare($insert_sql);
	
	$inserted_rows = 0;
	while (($data = fgetcsv($csv_handle, 0, $delimiter)) !== FALSE) {
		$insert_sth->execute($data);
		$inserted_rows++;
	}
	
	$pdo->commit();
	
	fclose($csv_handle);
	
	return array(
			'table' => $table,
			'fields' => $fields,
			'insert' => $insert_sth,
			'inserted_rows' => $inserted_rows
		);
 
}
public function createViewObject()
{
    $qr = file_get_contents('objects/baseview.sql');
    //echo $qr; die();
    return $this->getData($qr);
}

public function getData($qr)
{
    ini_set('memory_limit', '500M');
    
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
    fclose($fh);
    
    $this->downloadCSV($fh);
    $msg = Swift_Message::newInstance();
    $msg->setSubject('Power report-'.$this->tenant)
            ->setFrom('sirantho20@gmail.com')
            ->setTo('aafetsrom@htghana.com')
            ->setBody('Please find attached your requested report')
            ->attach(Swift_Attachment::fromPath($this->output_file_name));
    $transport = Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, "ssl");
    $transport->setUsername('sirantho20@gmail.com')
            ->setPassword('afTONY19833');
    
    $mail = new Swift_Mailer($transport);
    $mail->send($msg);
    
    
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
    //echo $content;
    readfile($fileName);
    
    //unlink($fileName);
}


}


