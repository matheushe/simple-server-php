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

				

				// if(isset($_SESSION['usr_login']) && $_SESSION['usr_login']=="joaofr")
				// 	$aStrConn['protheus'] = "mssql://siga:siga@Protheus02/DB_Protheus";

				try{

					self::$instance[$db] = NewADOConnection($aStrConn[$db]);
					self::$instance[$db]->SetFetchMode(ADODB_FETCH_ASSOC);

					if($db=='protheus'){
						self::$instance[$db]->SetTransactionMode('SERIALIZABLE');
					
						
						
						
					/*
					if(isset($_SESSION['usr_login']) && $_SESSION['usr_login']=='joaofr'){

						setlocale( LC_ALL, 'pt_BR.iso-8859-1', 'portuguese' );
						
						$all = self::$instance[$db]->GetAll("SELECT TOP 40 B1_DESC FROM SB1FN0");
						foreach($all as $a)
							echo $a['B1_DESC'].'<br>';

						exit;
					}
*/

					}elseif($db=='secullum'){
						self::$instance[$db]->SetTransactionMode('READ COMMITTED');
					}


				}catch(Exception $e){

					$msg = '';

					//-- só mostra detalhes do erro porque o usuário é analista
					
					die('<div style="background:#f00;color:#fff;padding:5px">Desculpe, não foi possível conectar ao Banco de Dados <strong>'.strtoupper($db).'</strong>, pode estar temporariamente inoperante. Favor tentar dentro de alguns instantes!'.$msg.'</div>');
				}

			}

			return self::$instance[$db];

		}

		// Previne que o usuário clone a instância
		public function __clone(){
			trigger_error('Clone is not allowed.', E_USER_ERROR);
		}

}