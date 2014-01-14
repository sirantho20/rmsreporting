<?php

include 'lib/reportCore.php';

    
    
    $date = filter_input(INPUT_POST, 'report-date');
    $split = explode('to', $date);
    $from = $split[0];
    $to = $split[1];
    //echo $from.'<br />'.$to;die();
    $tenant = filter_input(INPUT_POST, 'tenant');
    
    if( isset($from) && isset($to) && isset($tenant) )
    {
        $obj = new reportCore($from, $to, $tenant); 
        $obj->dumpCSV();
    }

