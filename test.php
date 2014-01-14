<?php
set_time_limit(0);
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include 'lib/ad/SimpleLDAP.class.php';


$ldap = new SimpleLDAP('10.3.0.1', 389);
$ldap->dn = 'ou=users,dc=hta,dc=local';
print_r($ldap->auth('hta\aafetsrom', '!!AFtony19833'));
