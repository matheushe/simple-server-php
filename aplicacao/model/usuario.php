<?php

class Usuario extends Model{

	public $field_renders = array('str_bloq' => 'field_render_str_bloq');
	public $cabec_renders = array('str_bloq' => 'cabec_render_str_bloq');	
	
	public function field_render_str_bloq($row){
		return Componenteview::icon($row['str_bloq']=='S' ? 'legenda/red.png' : 'legenda/green.png');
	}
	
	public function cabec_render_str_bloq($str_tit){
		return '';
	}
	
	public function autentica(){
		if(AUTH=='ad')
			return $this->authad();
		else
			return $this->authbd();
	}
	
	public function authbd(){
		
		$where = " str_login = '".$this->dados['str_login']."'
				 AND str_senha = md5('".$this->dados['str_senha']."')";
		
		$this->get('id',$where);
		$this->result();
	
		return ($this->num_rows > 0 && $this->id);

	}
		
	/**
	 * FAZ A AUTENTICAÇÃO DO USUÁRIO ATRAVÉS DO ACTIVE DIRECTORY
	 */
	public function authad(){
		//require_once("util/myadLDAP.php");

		$objLDAP = new myadLDAP($_POST['emp']);		

		return ($objLDAP->authenticate($this->dados['str_login'], $this->dados['str_senha']));
	}

	public static function retLogin($id){
		$usr = new Usuario();
		return $usr->db->GetOne(" SELECT str_login FROM usuario WHERE id = $id ");;
	}

	public function recLogin($login,$lauto=false,$emp=''){

		if(empty($emp))
			$emp = $_SESSION['usr_filial'] ?? '';

		//trata para não filtrar filial default no get
		$modo_antigo = $this->modo;
		$this->modo = 'S';

		$this->get('*',['str_login=? and str_filial=?',[$login,$emp]]);

		$this->result();
		$this->modo = $modo_antigo;


		//-- se modo de autenticacao = Active Directory
		//-- busca tambem as informacoes do servidor
		if(AUTH=='ad'){

			//require_once(($lauto ? ROOT.DS : '') . "util/myadLDAP.php");		
				
			$objLDAP = new myadLDAP($emp);
										
			//retorna um array com os dados do usuário

			/* 			
			telephonenumber
			displayname
			memberof
			department
			primarygroupid
			samaccountname
			mail
			*/
			$aInfo = array('telephonenumber','displayname','department','mail','description','useraccountcontrol');

			if($emp=='TRO')
				$aInfo[] = 'physicaldeliveryofficename';
			
			$dados_usuario = $objLDAP->user()->info($login,$aInfo);
			
			/*
			if($login=='joaofr' || $login=='informatica'){
				
				$dados_usuario = $objLDAP->user()->info($login,array('*'));
				ver($dados_usuario);
				//$aa = $objLDAP->user()->infoCollection($login);
				//ver($aa);
			}*/
				
			
			//if(isset($_SESSION['usr_login']) && $_SESSION['usr_login']=="informatica")
			//	ver($dados_usuario);

			if(is_array($dados_usuario) && isset($dados_usuario[0])){
				$this->dados_result['str_login'] = strtolower($login);
				if(array_key_exists('displayname',$dados_usuario[0]))
					$this->dados_result['str_nome']  = utf8_decode($dados_usuario[0]['displayname'][0]);
				else
					echo $login.'--- sem nome no ad!!! <br>';
				
				if(array_key_exists('department',$dados_usuario[0]))
					$this->dados_result['str_setor'] = utf8_decode($dados_usuario[0]['department'][0]);

				if(array_key_exists('physicaldeliveryofficename',$dados_usuario[0]))
					$this->dados_result['str_setor'] = utf8_decode($dados_usuario[0]['physicaldeliveryofficename'][0]);

				if(array_key_exists('description',$dados_usuario[0]))
					$this->dados_result['str_cargo'] = utf8_decode($dados_usuario[0]['description'][0]);
				
				if(array_key_exists('mail',$dados_usuario[0]))
					$this->dados_result['str_email'] = $dados_usuario[0]['mail'][0];
				
				if(array_key_exists('telephonenumber',$dados_usuario[0]))
					$this->dados_result['str_ramal'] = $dados_usuario[0]['telephonenumber'][0];	

			}

		}

		//ver($this->dados_result);
	}
	
	public function getNumPesquisas(){
	
		$sql  = "SELECT COUNT(*) FROM chamado 
						 WHERE str_relator = '".$this->str_login."'
				 AND str_statuspesquisa = 'P' 
				 AND str_status = 'E'";
	
		try{
			return $this->db->GetOne($sql);
		}catch(Exception $e){
			throw new Exception($e->getMessage());
		}
	
	}
	
	public function getIdbyLogin($login,$emp=''){
		
		if(empty($emp)){
			if(!isset($_SESSION['usr_filial']))
				die('erro, falta sessão usuario_filial');
			else
				$emp=$_SESSION['usr_filial'];
		}
		
		return $this->db->GetOne(" SELECT id FROM usuario WHERE str_login = '$login' and str_filial='".$emp."'");
	}
	/**
	 * thiago tozzi schonrock
	 */
	public function getCCbyLogin($login,$emp='')
	{
		if(empty($emp))
		{
			if(!isset($_SESSION['usr_filial']))
				die('erro, falta sessão usuario_filial');
			else
				$emp=$_SESSION['usr_filial'];
		}
		
		return $this->db->GetOne(" SELECT str_cc FROM usuario WHERE str_login = '$login' and str_filial='".$emp."'");
	}

	public function getNomebyLogin($login,$emp='')
	{
		if(empty($emp))
		{
			if(!isset($_SESSION['usr_filial']))
				die('erro, falta sessão usuario_filial');
			else
				$emp=$_SESSION['usr_filial'];
		}
		
		return $this->db->GetOne(" SELECT str_nome FROM usuario WHERE str_login = '$login' and str_filial='".$emp."'");
	}

	/**
	 * Matheus Henrique
	 * Recupera o Login por meio da matricula
	 * !!! NEM TODOS OS FUNCIONARIOS TEM LOGIN !!!
	 */
	public function getLoginbyMatr($matr,$emp='')
	{
		if(empty($emp))
		{
			if(!isset($_SESSION['usr_filial']))
				die('erro, falta sessão usuario_filial');
			else
				$emp=$_SESSION['usr_filial'];
		}
		
		return $this->db->GetOne(" SELECT str_login FROM usuario WHERE str_matr like '%$matr' and str_filial='".$emp."'");
	}
	
	public function getComputador(){
		$sql = " SELECT id FROM computador WHERE str_usuario = '".strtolower($_SESSION['usr_login'])."'";
		return $this->db->GetOne($sql);
	}
	
	/*
	public static function isAnalista($login='')
	{

		if(empty($login) && isset($_SESSION['usr_login']))
			$login = $_SESSION['usr_login'];

		if(IS_WHB)
		{
			$usr = new Usuario();
			$analista = $usr->getone('str_analista',"str_login='$login'");

			return $analista=='S';
		}
	}
	*/
	
	public static function getAcessos($id_usuario){
		
		//-- pega os acessos especificos
		$s6 = new Sys006();
		
		$s6->get('id_sys002',"id_usuario='".$id_usuario."'");
			
		$ac1 = array();
		while($s6->result())
			$ac1[] = $s6->id_sys002;
		
		//-- pega os acessos dos grupos
		$s11 = new Sys011();
		$ac2 = $s11->getAcessos($id_usuario);
		
		//-- une os dois arrays eliminando repetidos
		return array_unique(array_merge($ac1,$ac2));
		
	}
	
	public function cabec($rs=null){

		$c = parent::cabec($rs);

		if($_SESSION['usr_filial']=='WHB')
			$c['str_cc']['str_consulta'] = 'CTTFN0';
		elseif($_SESSION['usr_filial']=='ITE')
			$c['str_cc']['str_consulta'] = 'CTTIT0';
		elseif($_SESSION['usr_filial']=='ZAI')
			$c['str_cc']['str_consulta'] = 'CTTZR0';
		elseif($_SESSION['usr_filial']=='TRO')
			$c['str_cc']['str_consulta'] = 'CTTTR0';
		
		return $c;

	}

	public function getCamposAltInfo(){
		
		//-- camops do usuario
		$sql = "SELECT * FROM ".MYSQL_DBNAME.".sys001 WHERE str_tab = '".$this->tabela."'
						AND str_campo IN ('str_emp','str_matr','str_nome','str_ramal','str_fax','str_cel','str_setor','str_cargo','str_cargoing') order by str_ordem";

		$rs = $this->db->Execute($sql);
		$cab2 = $this->cabec($rs);

		//-- campos da aprov. de informações
		/*
		$sql = "SELECT * FROM ".MYSQL_DBNAME.".sys001 WHERE str_tab = 'whb0059'
						AND str_campo IN ('blb_arquivo') order by str_ordem";

		$rs = $this->db->Execute($sql);
		$cab3 = $this->cabec($rs);
		$cab3['blb_arquivo']['str_obr']='S';
		
		//-- campos da aprov. de informações
		$sql = "SELECT * FROM ".MYSQL_DBNAME.".sys001 WHERE str_tab = 'adm016'
						AND str_campo IN ('str_cartao','str_carimbo','str_sc') order by str_ordem";

		$rs = $this->db->Execute($sql);
		$cab4 = $this->cabec($rs);
		
		$cab4['str_carimbo']['str_onchange'] = 'fCarimbo(this)';
		$cab4['str_sc']['str_obr'] = 'S';

		*/
		//-- ordena
		$cab = array('str_emp'   => $cab2['str_emp']   ,
					 'str_matr'  => $cab2['str_matr']  ,
					 'str_nome'  => $cab2['str_nome']  ,
					 'str_cargo' => $cab2['str_cargo'] ,
					 'str_cargoing' => $cab2['str_cargoing'] ,
					 'str_setor' => $cab2['str_setor'] ,
					 'str_ramal' => $cab2['str_ramal'] ,
					 'str_fax'   => $cab2['str_fax'],
					 'str_cel'   => $cab2['str_cel'],
//					 'str_cartao' => $cab4['str_cartao'],
//					 'str_carimbo' => $cab4['str_carimbo'],
//					 'blb_arquivo' => $cab3['blb_arquivo'],
//					 'str_sc'      => $cab4['str_sc']
						);
		
		$arr= array();

		$emp=$this->str_emp;

				if(!empty($emp) && strlen($emp)==5){
			
						$cab['str_matr']['str_tipo'] = 'C';//alterando o campo para ComboBox
						$cab['str_matr']['str_altera'] = 'S';//definir campo como 'Alteravel'
		
						if($emp=='FN001'){
								$cab['str_matr']['str_consulta'] = 'SRAFN0_FN';
						}elseif($emp=='FN002'){
								$cab['str_matr']['str_consulta'] = 'SRAFN0_PE';
						}elseif($emp=='IT001'){
								$cab['str_matr']['str_consulta'] = 'SRAIT0';
						}elseif($emp=='NH001'){
								$cab['str_matr']['str_consulta'] = 'SRANH0';
						}elseif($emp=='ZRA01'){
								$cab['str_matr']['str_consulta'] = 'ZRAFN0';				
						}elseif($emp=='TR001'){
								$cab['str_matr']['str_consulta'] = 'SRATR0';				
				  		}elseif($emp=='ZR001'){
				  				$cab['str_matr']['str_consulta'] = 'SRAZR0';
						}elseif($emp=='OUTRO'){
								$cab['str_matr']['str_consulta'] = 'usuario_outros';
						}
				}else{
			$cab['str_matr']['str_altera'] = 'N';
		}

		$cab['str_emp']['str_titulo'] = 'Empresa Contratante';
		
		foreach($cab as $c){
			
			if(in_array($c['str_campo'],array('str_matr','str_ramal','str_cargo','str_emp')))
				$c['str_obr'] = 'S';

			if(in_array($c['str_campo'],array('str_carimbo','str_cartao')))
				$c['str_titulo'] = 'Solicitar '.$c['str_titulo'];
				
			if($c['str_campo']!='str_matr')
				$c['str_altera']   = 'S';
				
			$c['str_form']   = 'S';
			$c['int_tabindex'] = 0;
			
			$arr[$c['str_campo']] = $c;
		
		}
	
		if(isset($_SESSION['usr_filial']) && $_SESSION['usr_filial']=="ITE"){
			$arr['str_fax']['str_obr'] = '';
		}
	

		return $arr;
	}

	public function dadosMatJson($mat,$emp){
		$this->recMat($mat,$emp);
		echo json_encode(array_map('utf8_encode',$this->dados_result));
	}
	

	public function recMat($mat,$emp){

			$terceiros=false;
			if($emp=='ZRA01'){
				$terceiros=true;
				$emp='FN001';
			}

			$this->get('*',"str_matr='".$mat."' and str_emp='".$emp."'");

			$this->result();
		
			if($emp == 'OUTRO'){
										
				$sql = "SELECT str_nome as RA_NOME, str_setor as DESCCC, str_cc as RA_CC, '' AS RA_NASC, '' AS RA_ADMISSA, str_cargo as CARGO,'' AS CPF "
						. "FROM usuario_outros " 
						. "WHERE id ='".$mat."' "
						. "AND str_filial ='".$_SESSION['usr_filial']."'";
										
				$row = $this->ExecQuery($sql,'row');
						
			}else{
										
				if($emp=='IT001')
					$mp = new ModelItesapar();
				elseif($emp=='ZR001')
					$mp = new ModelZaire();
				elseif($emp=='TR001')
					$mp = new ModelTroy();
				else
					$mp = new ModelProtheus();
										
				if($terceiros){
								$sql = "SELECT ZRA_CCUSTO AS RA_CC, ZRA_SITFOL AS RA_SITFOLH, ZRA_DTNASC AS RA_NASC, ZRA_INICIO AS RA_ADMISSA, ZRA_NOME AS RA_NOME, ZRA_SITFOL AS RA_SITFOLH,
																(SELECT CTT_DESC01 FROM CTT".substr($emp,0,3)." WHERE CTT_CUSTO = ZRA_CCUSTO AND D_E_L_E_T_ = ' ' and CTT_FILIAL = ZRA_FILIAL) AS DESCCC,
																(SELECT QAC_DESC FROM QAC".substr($emp,0,3)." WHERE QAC_FUNCAO = ZRA_CODFUN AND D_E_L_E_T_ = ' ') AS CARGO,
					ZRA_CPFCGC AS CPF
																FROM ZRA".substr($emp,0,3)." 
																WHERE ZRA_MAT='".$mat."'
																AND ZRA_FILIAL = '".substr($emp,3,2)."' 
																AND D_E_L_E_T_ = ' ' ";

				}else{
					$sql = "SELECT RA_CC, RA_NASC, RA_ADMISSA, RA_NOME, RA_SITFOLH,
									(SELECT  CTT_DESC01 FROM CTT".substr($emp,0,3)." WHERE CTT_CUSTO = RA_CC AND D_E_L_E_T_ = ' ' 
									 AND RA_FILIAL=CTT_FILIAL) AS DESCCC, " . 
		//					" (SELECT Q3_DESCSUM FROM SQ3".substr($emp,0,3)." WHERE Q3_CARGO = RA_CARGO AND D_E_L_E_T_ = ' ') AS CARGO " 

															"(SELECT TOP 1 RJ_DESC  FROM SRJ".substr($emp,0,3)." WHERE RJ_FUNCAO = RA_CODFUNC AND D_E_L_E_T_ = ' ') AS CARGO,
										RA_CIC AS CPF
															FROM SRA".substr($emp,0,3)." 
															WHERE RA_MAT='".$mat."'
															AND RA_FILIAL = '".substr($emp,3,2)."' 
															AND D_E_L_E_T_ = ' ' ";
			}

			//if(isset($_SESSION['usr_login']) && $_SESSION['usr_login']=='joaofr')
			//	ver($sql);

			$row = $mp->ExecQuery($sql,'row');
		}

		//echo $sql;
		
		if(!empty($row) && sizeof($row)>0){
			$this->dados_result['str_matr']   = $mat;
			//$this->dados_result['dat_nasc']   = $this->dados['dat_nasc'] = (!empty($row['RA_NASC'])? msdtod($row['RA_NASC']) : '');
			//$this->dados_result['dat_admi']   = $this->dados['dat_admi'] = (!empty($row['RA_ADMISSA']) ? msdtod($row['RA_ADMISSA']) : '');
			$this->dados_result['str_cc']     = $row['RA_CC'];
			$this->dados_result['str_desccc'] = $row['DESCCC'];
			$this->dados_result['str_cargofolha'] = $row['CARGO'];
			$this->dados_result['str_nomefolha'] = $row['RA_NOME'];	
			$this->dados_result['str_emp']     = $emp;
			$this->dados_result['str_cpf']     = $row['CPF'];
		}else{
			$this->dados_result['str_matr']   = '';
			//$this->dados['dat_nasc']          = '';
			//$this->dados['dat_admi']          = '';
			$this->dados_result['str_cc']     = '';
			$this->dados_result['str_desccc'] = '';
			$this->dados_result['str_cargofolha'] = '';
			$this->dados_result['str_nomefolha'] = '';	
			$this->dados_result['str_emp']     = '';
			$this->dados_result['str_cpf']     = '';
		}

		return $this->dados_result;
		
	}

	public static function getEmp($emp,$protheus=false){
		
		$sql = "SELECT CONCAT(str_abrev,str_filialprotheus) as filial,str_nome,str_nome2 
						FROM sys015
						UNION SELECT 'ZRA01','TERCEIROS / PJ','TERCEIROS / PJ'
						UNION SELECT 'OUTRO','OUTROS','OUTROS'";

		$s15 = new sys015;
		
		$all = $s15->ExecQuery($sql,'all');

		$arr = array();
		foreach ($all as $val) {
			$arr[$val['filial']] = $val['str_nome'.($protheus ? '2' : '')];
		}

		return array_key_exists($emp,$arr) ? $arr[$emp] : '';
	}

	public function getFullName($login){
					$this->get('str_nome',"str_login = '" . $login . "' ");
					while( $this->result() )
							return $this->str_nome;
	}
/**
 * adicionado para pegar emails
 * author Thiago Tozzi
 * date 2016-10-07
 * @param  [type] $login [string]
 * @return [type]        [string]
 */
	public static function getEmail($login, $global = false)
	{	
		$usr = new Usuario();
		if($global)
			$usr->modo = 'S';
			
		$usr->get('str_email',"str_login = '" . $login . "' ");
		while( $usr->result() )
				return $usr->str_email;
		return '';
	}


}