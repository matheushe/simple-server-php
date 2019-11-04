<?php
	
	define('SYSNAME','ServerHost');
	define('DEBUG',true);

	$config = parse_ini_file(__DIR__.'/sys.ini');

	define('DIRPAGE',"http".(isset($_SERVER['HTTPS']) ? 's' : '')."://{$_SERVER['HTTP_HOST']}/{$config['pagina_interna']}");
	define('DIRREQ',"{$_SERVER['DOCUMENT_ROOT']}".(substr($_SERVER['DOCUMENT_ROOT'],-1)=="/" ? "" : "/")."{$config['pagina_interna']}");
	// die(DIRREQ);
	define('DIRIMG',DIRPAGE.'app/view/img/');
	define('DIRICO',DIRPAGE.'app/view/img/icons/');

	define('AJAX',isset($_GET['ajax']));
	
	//-- BANCO DE DADOS
	
	// MYSQL

	define('MYSQL_DBNAME'  ,'db_controller');

	//-- CONNECTION WITH DATABASES

	define('A_STRCONN', serialize(array(
										'controller'			=> "mysqli://root:root@".$config['db_server']."/db_controller",
								)));

	//-- email
	define('SMTP_SERVER','10.72.1.67');		
	define('SMTP_PORT',25);
	define('SMTP_ACCOUNT','');
	define('SMTP_PASS','');

	//-- PORTAL
	define('DS', DIRECTORY_SEPARATOR); 

	define('FOLDERS', 'array("app","custom")');
	define('SUBFOLDERS','array("model","controller")');
	
	define('MAX_FILE_SIZE', 128); //em Mb
	
	ini_set("memory_limit",MAX_FILE_SIZE."M");
	ini_set('display_errors',E_ALL); //mostra os erros do php

	header("Content-Type: text/html;  charset=UTF-8",true);
	date_default_timezone_set('America/Sao_Paulo');

	setlocale(LC_ALL, "pt_BR.UTF-8");
	//setlocale(LC_NUMERIC, 'en_US');

	//if(function_exists('xdebug_disable')) { xdebug_disable(); }
