<?php
set_time_limit(0);
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include ('lib/reportCore.php');

$obj = new reportCore('2013-12-01','2013-12-31');
$obj->migrateData();
//print_r($obj->getUniqueSites());
