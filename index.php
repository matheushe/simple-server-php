<?php
	//-- force content compression for browsers that supports it.
	$a = ob_start("ob_gzhandler");

	// header('Access-Control-Allow-Origin: *');
	// header('Access-Control-Allow-Methods: GET, POST');
	// header("Access-Control-Allow-Headers: X-Requested-With");

	//-- inclui a biblioteca portal.php (cont�m as fun��es b�sicas do sistema)
	require_once 'config/defines.php';

	require_once 'lib/vendor/autoload.php';

	require_once 'app/src/portal.php';

	// verifica se foi informado par�metro para a p�gina,
	// sen�o assume a p�gina indexcontroller
	$pagina = isset($_GET['p']) ? $_GET['p'] : 'index';

	// verifica se foi informado par�metro para o m�todo,
	// sen�o assume o m�todo index. Este m�todo deve estar definido na classe (p�gina)Controller
	$metodo = isset($_GET['op']) ? $_GET['op'] : 'index';

	// variavel auxiliar para compor o nome da classe controller
	$controller  = ucfirst(strtolower($pagina)).'Controller';

	// instancia um objeto do tipo (p�gina)Controller
	$objpage = new $controller;

	//-- chama o m�todo da classe utilizando a fun��o eval
	eval('$objpage->'.$metodo.'();');
