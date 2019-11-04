<?php
	//-- force content compression for browsers that supports it.
	$a = ob_start("ob_gzhandler");

	// header('Access-Control-Allow-Origin: *');
	// header('Access-Control-Allow-Methods: GET, POST');
	// header("Access-Control-Allow-Headers: X-Requested-With");

	//-- inclui a biblioteca portal.php (contém as funções básicas do sistema)
	require_once 'config/defines.php';

	require_once 'lib/vendor/autoload.php';

	require_once 'core/src/portal.php';

	// verifica se foi informado parâmetro para a página,
	// senão assume a página indexcontroller
	$pagina = isset($_GET['p']) ? $_GET['p'] : 'index';

	// verifica se foi informado parâmetro para o método,
	// senão assume o método index. Este método deve estar definido na classe (página)Controller
	$metodo = isset($_GET['op']) ? $_GET['op'] : 'index';

	// variavel auxiliar para compor o nome da classe controller
	$controller  = ucfirst(strtolower($pagina)).'Controller';

	// instancia um objeto do tipo (página)Controller
	$objpage = new $controller;

	//-- chama o método da classe utilizando a função eval
	eval('$objpage->'.$metodo.'();');
