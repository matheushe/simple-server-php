<?php

abstract class Controller
{

    /**
    * Construtor da classe
    * @return void
    */
    public function __construct() 
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST');
        header('Access-Control-Allow-Headers: Content-Type');

        session_start();

        if(isset($_GET['emp']))
            $_SESSION['usr_filial'] = $_GET['emp'];
        else{
            arrToJson(['erro'=> true, 'msg' => 'empresa vazia']);
            die;
        }
    }
}