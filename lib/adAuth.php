<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of adAuth
 *
 * @author aafetsrom
 */
class adAuth {
    //put your code here
    protected $hostname = '10.2.0.1';
    protected $ldap;

    public function __construct() 
    {
        $this->ldap = ldap_connect($this->hostname);
    }
    
    public function authenticate($username, $password)
    {
        if(ldap_bind($this->ldap, "hta\\$username", $password))
        {
            return true;
        }
        else 
        {
            return false;
        }
    }
}


