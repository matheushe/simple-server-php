<?php

class IndexController extends Controller{

	public function index(){
		echo 'Bem vindo ao sistema '.SYSNAME.' v' .SYSVERSION.', você não está logado!';
	}
	
}