<?php
	
	define('SYSNAME','ServerHost');
	define('SYSVERSION','1.201.3');
	define('DEBUG',true);
	define('SISTEMA_PROPRIETARIO_NOME','MATHEUS HENRIQUE RODRIGUES');
	define('SISTEMA_PROPRIETARIO_EMAIL', 'matheus_henriquealves@hotmail.com');

	$config = parse_ini_file(__DIR__.'/sys.ini');

	define('DIRPAGE',"http".(isset($_SERVER['HTTPS']) ? 's' : '')."://{$_SERVER['HTTP_HOST']}/{$config['pagina_interna']}");
	define('DIRREQ',"{$_SERVER['DOCUMENT_ROOT']}".(substr($_SERVER['DOCUMENT_ROOT'],-1)=="/" ? "" : "/")."{$config['pagina_interna']}");

	define('IMGCORE',DIRPAGE.'core/view/img/');
	define('IMGAPP',DIRPAGE.'aplicacao/view/img/');
	define('PUBLICO',DIRPAGE.'publico/');

	define('AJAX',isset($_GET['ajax']));
	
	//-- BANCO DE DADOS
	
	define('MYSQL_DBNAME'  ,'db_controller');
	//-- CONNECTION WITH DATABASES
	define('A_STRCONN', serialize(array('controller' => "mysqli://root:root@".$config['db_server']."/db_controller")));

	//-- email
	define('SMTP_SERVER','127.0.0.1');
	define('SMTP_PORT',25);
	define('SMTP_ACCOUNT','');
	define('SMTP_PASS','');

	//-- Aplicacao
	define('DS', DIRECTORY_SEPARATOR); 

	define('FOLDERS', 'array("core","aplicacao")');
	define('SUBFOLDERS','array("model","controller")');
	
	define('MAX_FILE_SIZE', 128); //em Mb
	
	ini_set("memory_limit",MAX_FILE_SIZE."M");
	ini_set('display_errors',E_ALL); //mostra os erros do php

	header("Content-Type: text/html;  charset=UTF-8",true);
	date_default_timezone_set('America/Sao_Paulo');

	setlocale(LC_ALL, "pt_BR.UTF-8");