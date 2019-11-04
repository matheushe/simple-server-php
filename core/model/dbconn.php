<?php

//-- classe singleton para conexao com a base de dados
class DbConn{

	// Guarda uma instância da classe
	private static $instance = array();
		
	// Um construtor privado; previne a criação direta do objeto
	private function __construct(){}

	// O método singleton 
	public static function singleton($db){
	
		if (!isset(self::$instance[$db])) {

			require_once DIRREQ."/lib/vendor/adodb/adodb-php/adodb.inc.php";
			require_once DIRREQ."/lib/vendor/adodb/adodb-php/adodb-exceptions.inc.php";

			$aStrConn = unserialize(A_STRCONN);
			
			try{
				self::$instance[$db] = NewADOConnection($aStrConn[$db]);
				self::$instance[$db]->SetFetchMode(ADODB_FETCH_ASSOC);
			}catch(Exception $e){
				
				echo '<div style="background:#f00;color:#fff;padding:5px">Desculpe, não foi possível conectar ao Banco de Dados <strong>'.strtoupper($db).'</strong>, pode estar temporariamente inoperante. Favor tentar dentro de alguns instantes!</div>';
				
				if(DEBUG)
					ver($e);
			}
		}
		return self::$instance[$db];
	}

	// Previne que o usuário clone a instância
	public function __clone(){
		trigger_error('Clone is not allowed.', E_USER_ERROR);
	}
}