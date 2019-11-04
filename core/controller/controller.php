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

        if(isset($_GET['emp']))
            $_SESSION['usr_filial'] = $_GET['emp'];
        else{
            arrToJson(['erro'=> true, 'msg' => 'empresa vazia']);
            die;
        }
    }
}