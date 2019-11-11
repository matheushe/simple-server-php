<?php

abstract class Controller
{

    /**
    * Construtor da classe
    * @return void
    */
    public function __construct() 
    {
    	session_start();
    }

}
