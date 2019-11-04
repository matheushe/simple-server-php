<?php

class Model{

	/**
	* nome da tabela
	* @var string
	*/
	public $tabela; 

	public $id_field = 'id';

	/**
	* conexao com a base de dados
	* @var string
	*/
	protected $db;
	
	protected $datadb;
	
	/**
	* array com os dados usados para resultado
	* @var string[]
	*/
	public $dados_result;

	/**
	* array com os dados usados para insert e update
	* @var string[]
	*/
	public $dados;
	
	public $models = array();

	public $files = array();

	/**
	* array usado pelo adodb para pegar os resultados das consultas
	* @var string
	*/
	public $result;

	public $num_rows;
	
	//public $database;
	
	public $field_renders = array();
	
	public $cabec_renders = array();

	private $insert_id;
	
	public $modo = '';

	public $name_conn = '';

	public $brw_limit = 200;

	public $last_sql = '';

	/**
	* Construtor da classe
	* @param string $id O nome da tabela
	* @return void
	*/
	public function __construct($id=""){
		
		$this->conecta();

		//o nome da tabela no bd deve ser o mesmo da classe
		$this->setTabela(strtolower(get_class($this)));

		$this->setDatabase();
		
		$this->dados_result =
		$this->dados = array();
		
		if(!empty($id))
			$this->recupera($id);
	}

	/**
	* Destrutor da classe
	* @return void
	*/
	public function __destruct(){}

	public function __wakeup(){
			$this->conecta();
	}

	public function recupera($id){

		$this->get("*",is_array($id) ? implode(' and ',$id) : $this->id_field."='$id'");
		$this->result();
	}

	public function selectDb($dbname){
		$this->datadb->selectDb($dbname);
	}

	public function setDatabase(){
		$row = $this->db->GetRow("SELECT str_modo FROM ".MYSQL_DBNAME.".sys005 WHERE str_nome='".$this->tabela."'");
		
		if(!empty($row)){
			$this->modo = $row['str_modo'];
			//$this->datadb->database = $row['str_db'];
		}
	}

	public function dbtype(){
		return $this->datadb->databaseType;
	}
	
	public function getDbName(){
		return $this->datadb->database;
	}

	public function setDebug($value){
		$this->datadb->debug = $this->db->debug = $value;
	}

	/**
	 * funcao que faz a conexao com o banco de dados
	 */
	public function conecta(){

		//conecta com o banco de dados
		try {

			$this->db = DbConn::singleton(str_replace('db_','',MYSQL_DBNAME));

			//-- conexao auxiliar com base de dados
			if(!empty($this->name_conn))
				$this->datadb = DbConn::singleton($this->name_conn);
			else
				$this->datadb = $this->db;


		} catch (Exception $e) {
			die("Erro na conexao:".$e->getMessage());
		}

	}

	/**
	* Funcao que altera o valor da propriedade tabela
	* @param string $tabela Nome da tabela
	* @return void
	*/
	public function setTabela($tabela) {
		$this->tabela = strtolower($tabela);
	}
	
	/**
	* Funcao que retorna o ultimo id auto incrementavel inserido no banco
	* retorna falso se a funcao nao tem suporte
	*/
	public function insert_id(){
		return $this->insert_id;
	}
	
	/**
	* Funcao que monta a consulta sql para a busca dos dados
	* @param string[] $campos Array com o nome dos campos a serem buscados
	* @param string $where Parametros SQL para a pesquisa
	* @return void
	*/
	public function get($campos,$where=null,$order=null,$group=null,$limit=false,$ret='') {
		
		$campos = is_array($campos) ? $campos : explode(',',$campos);
		
		//monta o sql 
		$sql = "select " . implode(",",$campos) .
					 " from ". (!empty($this->datadb->database) ? $this->datadb->database.'.' : '') . $this->tabela;

		$dt = false;
		
		//-- use where as array is more securely
		//-- WHERE ARRAY array( "foo=? and bar=?" , [$valfoo,$valbar]);

		if(is_array($where))
			list($where,$dt) = $where;

		//--verifica se tabela é exclusiva ou compartilhada
		if (!empty($this->datadb->database) && $this->modo<>'S' && strpos($where,"str_filial='")===false) 
			$where = (($where) ? '('.$where.') AND ' : '') . (!empty($this->datadb->database) ? $this->datadb->database.'.' : '') . $this->tabela .".str_filial = '".$this->xFilial($this->tabela)."' ";

		if($where) $sql .= " where ".$where;
		if($group) $sql .= " group by ".$group;
		if($order) $sql .= " order by ".$order;
		if($limit) $sql .= " limit ".$limit;
		
		try{
			
			if($ret=='one')
				$this->result = $this->datadb->GetOne($sql,$dt);
			elseif($ret=='row')
				$this->result = $this->datadb->GetRow($sql,$dt);
			elseif($ret=='all')
				$this->result = $this->datadb->GetAll($sql,$dt);
			else
				$this->result = $this->datadb->Execute($sql,$dt);

			if($ret=='one' || $ret=='row')
				$this->num_rows = $this->result!==null ? 1 : 0;
			elseif($ret=='all')
				$this->num_rows = sizeof($this->result);
			else
				$this->num_rows = $this->result->RecordCount();
			
		}catch(Exception $e){
			$m = "Erro na consulta ao banco de dados:<br>Erro:".$e->getMessage().'<br>SQL:'.$sql.'<br><br>';
			foreach(@debug_backtrace() as $erro){
				$m.= $erro['file'].'('.$erro['line'].') Class: '.(isset($erro['class']) ? $erro['class'] : '-').' Function: '.$erro['function'].'<br>';
			}
			
			$m .= '<a href="javascript:history.go(-1)">Voltar</a>';
			
			die($m);
		}
	}
	
	public function getone($campos,$where=null,$order=null) {
		$this->get($campos,$where,$order,0,0,'one');
		return $this->result;
	}

	public function getrow($campos,$where=null,$order=null) {
		$this->get($campos,$where,$order,0,0,'row');
		return $this->result;
	}

	public function getall($campos,$where=null,$order=null,$group=null,$limit=false) {
		$this->get($campos,$where,$order,$group,$limit,'all');
		return $this->result;
	}

	public function count($where=null){
		return $this->getone('COUNT(*)',$where);
	}

	/**
	* Funcao que retorna um valor booleano indicando se ainda existem resultados
	* @return bool
	*/	
	public function result() {
		try {
			return ($this->dados_result = $this->result->FetchRow());
		} catch (Exception $e)	{
			die($e->getMessage());
		}
	}

	public function gotop() {
		try {
			$this->result->MoveFirst();
		} catch (Exception $e)	{
			die($e->getMessage());
		}
	}
	/**
	* Funcao que faz o insert dos dados na tabela
	* @return void
	*/
	public function insert($ignore=0) {

		// $this->str_filial = $this->xFilial($this->tabela);
		if(isset($_GET['emp']))
			$this->str_filial = $_GET['emp'];
		else
			$this->str_filial = '';

		//-- guarda limite de memoria padrao
		$mem = ini_get('memory_limit');

		//-- aumenta limite de memoria para evitar erro em caso de inclusao de anexo
		ini_set('memory_limit','2048M');

		$sql = "insert " . ($ignore ? " ignore " : "") . 
					 "into ".(!empty($this->datadb->database) ? $this->datadb->database.'.' : '').$this->tabela." (" . implode(",",array_keys($this->dados)) .
				 ") values ('" . implode("','",$this->dados) . "')";
		
		$this->tryCRUD($sql,'inclusão');
				
		if(in_array($this->dbtype(),array('mysql','mysqli')))
			$this->insert_id = $this->datadb->Insert_ID();
		elseif($this->dbtype()=='mssqlnative'){
			$r = $this->result->FetchRow();
			$this->insert_id = $r['R_E_C_N_O_'];
		}

		foreach($this->models as $m){
			$m->id_tab = $this->insert_id;
			$m->insert(); 
			$m->__destruct();
		}

		//-- limpa o array models
		$this->models = [];

		foreach($this->files as $destination => $file_tmp){

			$destination = DIRREQ.str_replace('{{id}}', $this->insert_id, $destination);

			$this->verifyCreateDir($destination);

			$moved = move_uploaded_file( $file_tmp , $destination );

		}

		$this->files = [];

		//-- retorna limite de memoria padrao
		ini_set('memory_limit',$mem);

		//if($save) $this->save();
		//if($destruct) $this->__destruct();
		
	}

	/**
	* Funcao que faz o update dos dados na tabela
	* @param string $where Parametros SQL para a alteracao
	* @return void
	*/
	public function update($where=null) {
		
		if(empty($this->dados)) return;
		
		$sql = "update ".(!empty($this->datadb->database) ? $this->datadb->database.'.' : '').$this->tabela." set ";

		$updates = array();

		foreach ($this->dados as $campo => $valor) {
			//$valor = mysql_real_escape_string($valor);
			
			$updates[] = "$campo = " . ($valor===null ? 'NULL' : "'$valor'");

		}
		
		$sql .= implode(',', $updates);

		$sql .= " where ". ($where ? stripslashes($where) : ' '.$this->id_field.' = '.$this->dados_result[$this->id_field]);

		$this->tryCRUD($sql,'atualização');
		
		if(isset($_GET['id'])){
			foreach($this->models as $m){
				if(!empty($m->dados[$this->id_field])){
					if(substr($m->dados[$this->id_field],0,4)=='exc_')
						$m->delete();
					else
						$m->update();
				}else{
					
					//if(!isset($m->dados['id_tab']) || empty($m->dados['id_tab']))
					//	$m->id_tab = $_GET['id'];

					$m->insert();
				}
				//$m->save();
				$m->__destruct();
			}
		}
		
		foreach($this->files as $destination => $file_tmp){

			// arquivo destino
			$destination = str_replace('{{id}}', $this->dados_result['id'], $destination);

			//-- veririfica se ja tem arquivo neste diretoorio
			//-- pois so deve existir um arquivo por diretorio
			$d = explode('/',$destination);
			//-- remove o arquivo que esta tentando inserir;
			array_pop($d); 
			$d = implode('/',$d);

			if(is_dir($d)){
				//-- 2 pois 0 = "." e 1 = ".."
				if(isset(scandir($d)[2]) && file_exists($d.'/'.scandir($d)[2])){
					unlink($d.'/'.scandir($d)[2]); // apaga arquivo
				}

				rmdir($d); // apaga diretorio campo (sys only one file per folder)
			}

			//-- if update or simple insert, not delete
			if($file_tmp!='sys-unlink'){
				$this->verifyCreateDir($destination);
				$moved = move_uploaded_file( $file_tmp , $destination );
			}

		}

		$this->files = [];
		
	
		// if($save) $this->save();
		// if($destruct) $this->__destruct();
	}

	/**
	* Funcao que faz a exclusao dos dados na tabela
	* @param string $where Parametros SQL para a exclusao
	* @return void
	*/
	public function delete($where=null) {

		// ids to delete
		$id_del = []; 

		//-- se for passado parametro where
		if($where){
			
			//-- faz a query para buscar os ID a serem deletados
			$sql = "SELECT id FROM ".(!empty($this->datadb->database) ? $this->datadb->database.'.' : '').$this->tabela .
					 " where " . stripslashes($where);

			//-- guarda os ID a serem deletados numa matriz
			$id_del = $this->datadb->getAll($sql);

		//-- senao for passado where, e, se o id a ser deletado tiver definido no array dados_result
		}elseif(isset($this->dados_result[$this->id_field])){

			//-- guarda o ID a ser deletado como matriz
			$id_del = array(array('id' => $this->id));
		}

		//-- comando para exclusao
		$sql = "delete from ".(!empty($this->datadb->database) ? $this->datadb->database.'.' : '').$this->tabela .
					 " where " . ($where ? stripslashes($where) : " ".$this->id_field." = ".$this->id);
		
		//-- executa exclusao
		$this->tryCRUD($sql,'exclusão');

		//-- percorre o(s) ID deletado(s)
		foreach($id_del as $del){
			//-- apaga possivel "Detalhe de Arquivo" da tabela sys016 - detalhes de arquivos
			$this->db->Execute("DELETE FROM sys016 WHERE str_tab='{$this->tabela}' AND id_tab='{$del['id']}'");

			if(is_dir(DIRREQ.'arq/anexos/'.$this->tabela.'/'.$del['id'])){
				//ver('root: '.DIRREQ.$root.'/Model/files/'.$this->tabela.'/'.$del['id'],0);
				$this->delTree(DIRREQ.'arq/anexos/'.$this->tabela.'/'.$del['id']);
				//exit;
			}

		}

	}

	protected function delTree($dir){

		//ver('dir: '.$dir,0);
	   $files = array_diff(scandir($dir), array('.','..')); 
    	foreach ($files as $file) { 
			//ver('dir/file: '.$file,0);
	      is_dir("$dir/$file") ? $this->delTree("$dir/$file") : unlink("$dir/$file"); 
    	} 

    	//ver('rmdir: '.$dir,0);

    	return rmdir($dir);
	}

	
	protected function tryCRUD($sql,$acao){  

		//$this->datadb->BeginTrans( );

		try { 
			$this->last_sql = $sql;
			$this->result = $this->datadb->Execute($sql);
			$this->num_rows = $this->datadb->Affected_Rows();
		
			$this->dados = array();

		} catch (Exception $e) {
			
			die('Erro na '.$acao.':<br>
				<div style="color:red">
				<details><summary>Erro:</summary>'.$e->getMessage().'</details>
				<details><summary>SQL:</summary>'.$sql.'</details><br>
				</div><br>');
		}
	}

	//-- verifica e cria a pasta destino do campo arquivo se precisar
	protected function verifyCreateDir($d){
		
		$d = explode("/", $d);
		
		$dirs = [];
		for($x=0;$x<=3;$x++){

			array_pop($d); 
			$dirs[] = implode("/",$d);
		}

		$dirs = array_reverse($dirs);
		
		foreach($dirs as $d){

			if(!is_dir($d))
				mkdir($d);
		}

	}

	/**
	 * Interceptador __set. Quando um valor eh alterado ele eh colocano no array de dados
	 * para ser usado em instrucoes DML (insert, update) 
	 */
	function __set($name,$value) {
		$this->dados[$name] = $value;
	}

	/**
	 * Inserceptador __get. Quando um valor eh solicitado eh entregue o valor
	 * do array de resultados das consultas
	 */
	function __get($name) {

		if($name != "dados_result")
			if(isset($this->dados_result[$name])){
				return $this->dados_result[$name];
			}else{
				return "";
			}
		else
			return $this->dados_result;
	}

	/**
	* Funcao que faz a confirmacao das operacoes
	* @return void
	*/
	
	public function busca($por,$oque,$como,$autocomplete=false){

		if(!$autocomplete){

			// verifica se existe filtro de usuario na tabela
			$where1 = !empty($_SESSION['brw_fil'][$this->tabela]) ? ' ( '.$_SESSION['brw_fil'][$this->tabela].' )' : '';
			
			//-- para saber o indice do registro buscado (se encontrado) buscamos primeiro 
			//-- o id_field e o campo buscado de todos os registros da tabela, respeitando a regra de performance acima
			$campos = array($this->tabela.'.'.$this->id_field . ' as id_field',
				(strpos($por, 'CONCAT(')===false ? $this->tabela.'.' : '').$por);


			//-- traz os registros da tabela em questao, respeitando as condicoes acima
			$xMat = $this->lista($campos,null,null,false,true,$where1,false);

			$ind_id_encontrado=0; // inicia contador auxiliar (indice do registro se encontrado)

			//-- percorre
			while($xM = $xMat->FetchRow()){
		
				$ind_id_encontrado++;

				//-- verifica se é o que está procurando
				if(($como=='igual' && $xM[$por]==$oque) ||
					 ($como=='contem' && !empty($oque) && strpos($xM[$por],$oque)!==false) ||
						strtoupper(substr($xM[$por],0,strlen(trim($oque))))==strtoupper(trim($oque))){

					//-- se encontrar, retorna o id e o indice (indice servirá para paginacao)
					return array($xM['id_field'],$ind_id_encontrado);
					break;
				}
				
			}

			//-- se nao encontrar
			return false;

		}else{

			//-- autocomplete
			if($por=='id' && $this->datadb->databaseType=='mssqlnative')
				$por = "R_E_C_N_O_";
				
			if(strpos(strtoupper($por),"CONCAT")===false)
				$por = $this->tabela.'.'.$por;
			
			//-- encontra o id do elemento buscado
			if($como=='igual')
				$where1 = " ".$por." = '".$oque."' ";
			else
				$where1 = " ".$por." like '".($como=='contem' ? '%' : '').$oque."%' ";

			return $this->lista($por,null,null,false,false,$where1,false);
		}
		
	}

	public function getBrwCpo(){

		$s1 = new sys001;
		$campos = $s1->getall("str_campo","str_tab='".$this->tabela."' and str_contexto = 'R' and str_tipo<>'A'");
		
		return array_map(array($this, 'retbrwcpo'),$campos);
	}


	public static function retbrwcpo($a){
		return $a['str_campo'];
	}

	public function lista($campos=NULL,$ord=NULL,$dir=NULL,$count=false,$busca=false,$where='',$limit=true,$xls=false){

		//$campos = array("*");

		if(!$campos && !$count)
			$campos = $this->getBrwCpo();

		$tabela = $this->tabela;
		$ord = $ord ? $ord : $this->id_field;
		
		return $this->proclista($campos,$tabela,$where,$ord,$dir,$count,$busca,$limit);
		
	}

	public function proclista($campos,$tabela,$where,$ord,$dir,$count,$busca,$limit=true,$group=""){

		$sql = "select ";
		
		if($count && !$busca)
			$sql .= " count(*) ";
		else
			$sql .= is_array($campos) ? implode(",",$campos) : $campos;
	
		$sql .= " from ".( ($this->datadb->databaseType=='mysqli' || $this->datadb->database=='vetorh') && !empty($this->datadb->database) ? $this->datadb->database.'.' : '') . $tabela;

		/**
		 * controla um possivel filtro na tabela
		 */

		if((isset($_GET['consulta']) && $_GET['consulta']==1) || (isset($_GET['op']) && $_GET['op']=='consulta')){

			if(isset($_SESSION['consulta_fil'][$this->tabela])){

				if( isset($_GET['controller']) && $_GET['controller'] == $_SESSION['consulta_fil'][$this->tabela][0] &&
					isset($_GET['cfield']) && $_GET['cfield'] == $_SESSION['consulta_fil'][$this->tabela][1] ){

					//-- aqui substituimos o conjunto de caracteres "[[$sys005->str_filconsulta]]" por branco ""
					//-- para não dar erro de sintaxe na execução da query.
					//-- este conjunto de caracteres pode ter sido atribuido ao filtro da sessao 
					//-- no fonte "portalcontroller.php" método "consulta()" 
					$where .= (!empty($where) ? ' AND ' : '') . '(' . str_replace('%str_filconsulta%',"",$_SESSION['consulta_fil'][$this->tabela][2]) . ')';
				}
			}
		}

		if(!isset($_SESSION['tab_fil']) || !is_array($_SESSION['tab_fil']))
			$_SESSION['tab_fil'] = array();
		$tab_fil = $_SESSION['tab_fil'];
		
		if(!empty($where) && !empty($tab_fil[$this->tabela]))
			$where .= ' and ' . $tab_fil[$this->tabela];
		elseif(empty($where) && !empty($tab_fil[$this->tabela]))
			$where = $tab_fil[$this->tabela];

		//if($_SESSION['usr_login'] == 'julianohb')
			//ver($tab_fil);

		/**
		 * controla o limite do select para paginacao dos resultados no browse
		 */
		if(!isset($_SESSION['brw_pag']) || !is_array($_SESSION['brw_pag']))
			$_SESSION['brw_pag'] = array();
			
		$brw_pag = $_SESSION['brw_pag'];
		if(!isset($brw_pag[$this->tabela]) || !is_numeric($brw_pag[$this->tabela]) || $brw_pag[$this->tabela]==0)
			$brw_pag[$this->tabela] = 1;

		$_SESSION['brw_pag'] = $brw_pag;

		/**
		 * controla a ordem da lista de resultados do browse
		 */
		if(!isset($_SESSION['brw_ord']) || !is_array($_SESSION['brw_ord']))
			$_SESSION['brw_ord'] = array();

		$brw_ord = $_SESSION['brw_ord'];

		if(!isset($brw_ord[$this->tabela]) || !is_array($brw_ord[$this->tabela])){
				$brw_ord[$this->tabela] = array($ord ? $ord : $this->id_field, $dir ? $dir : 'asc');
		}
		
		$_SESSION['brw_ord'] = $brw_ord;

		//--verifica se tabela é exclusiva ou compartilhada
	
		if ($this->datadb->database==MYSQL_DBNAME && $this->filtraFilial())
			$where .= (($where) ? ' AND ' : '') . $this->datadb->database.'.'. $this->tabela.".str_filial = '".$this->xFilial($this->tabela)."' ";

		$sql .= $where ? " where " . $where : "";

		if(!empty($group)){
			$sql .= " GROUP BY ".$group;
		}
		
		if(!$count){
			$sql .= ' order by ' . $brw_ord[$this->tabela][0] . ' ' . $brw_ord[$this->tabela][1] ; 

			if($this->datadb->databaseType=='mysqli' && $brw_ord[$this->tabela][0]<>$this->id_field)
				$sql .= ' , '.$this->tabela.'.'.$this->id_field.' asc ';

			if($this->datadb->databaseType=='mssqlnative'){
				if(!$busca && $limit)
					$sql .= " offset ".(($brw_pag[$this->tabela] * $this->brw_limit) - $this->brw_limit)." ROWS FETCH NEXT ".$this->brw_limit." ROWS ONLY ";
			}
		}

		try {

			if($count){
				$this->result = $this->datadb->GetOne($sql);
				return $this->result;
			}elseif($busca){
				return $this->datadb->Execute($sql);
			}elseif(!$limit){
				return $this->datadb->GetAll($sql);
			}else{

				$_SESSION['brw_sql'][$this->tabela] = $sql;

				if($this->datadb->databaseType=='mssqlnative'){
					return $this->datadb->getAll($sql);
				}else{
					$this->result = $this->datadb->SelectLimit($sql,$this->brw_limit,($brw_pag[$this->tabela] * $this->brw_limit) - $this->brw_limit);
					return $this->getArray($this->result);
				}
				
			}

		} catch (Exception $e) {
			die($sql . '<br><br><br>'.
			"Erro na listagem:".$e->getMessage());
		}

	}

	private function filtraFilial(){
		//-- E = Tabela Exclusiva deve filtrar filial
		//-- diferente de E seria C=Compartilhado e S=Não filtra filial 
		return $this->modo=='E';
	}

	public function getArray($rs){

		$arr = array();
		while($row = $rs->FetchRow())
			$arr[] = $row;

		return $arr;
	}

	public function comboDesc($cpo,$val=null,$par=false,$localdb=false){
						
		$sql = " SELECT str_sqlcombo FROM ".MYSQL_DBNAME.".".($par ? 'sys003' : 'sys001') ."
				 WHERE str_tab = '".$this->tabela."'
				 AND str_campo = '$cpo'";

		$sql = $localdb ? $this->db->GetOne($sql) : $this->datadb->GetOne($sql);

		//-- troca simbolos
		$sql = str_replace(';','UNION SELECT',$sql);
		
		$arr = $this->sqlcombo2arr($sql,$localdb);

		$cpo = !is_null($val) ? $val : (array_key_exists($cpo,$this->dados_result) ? $this->dados_result[$cpo] : '');

		return array_key_exists($cpo,$arr) ? $arr[$cpo] : '';
	}

	public function consultaDesc($cpo,$val='',$par=false,$localdb=false){

		$sys001 = new Sys001();
		$sys001->recByTabCpo($this->tabela,$cpo);

		$sys005 = new Sys005();
		$sys005->recByTab($sys001->str_consulta);

		$model = new $sys001->str_consulta;
		$fil = desanitizar($sys005->str_filconsulta);

		$model->get(desanitizar($sys005->str_retconsulta),
			$fil . (!empty($fil)? ' AND ' : '') . desanitizar($sys005->str_chavconsulta) .  "='" . $this->dados_result[$cpo] . "'");
		$model->result();
		return  $model->dados_result[desanitizar($sys005->str_retconsulta)];
	}

	/**
	 * Esta funcao é utilizada durante todo o sistema
	 * ela faz uma consulta na tabela sys001
	 * e traz todos os campos da tabela solicitada
	 * contendo todos as características de cada campo
	 */
	public function cabec($rs=null){

		if($rs==NULL){
			$rs = $this->db->Execute("SELECT * FROM ".MYSQL_DBNAME.".sys001 WHERE str_tab = '".$this->tabela."' ORDER BY str_ordem");
		}
		
		$cab = array();
		while($row = $rs->FetchRow()){

			// se for do tipo combobox / multitags
			if($row['str_tipo']=='C' || $row['str_tipo']=='G'){
				
				if(!empty($row['str_sqlcombo']) && empty($row['str_consulta'])){
					
					$row['str_sqlcombo'] = $this->sqlcombo2arr($row['str_sqlcombo']);

					//-- remove opcao em branco
					if($row['str_tipo']=='G')
						unset($row['str_sqlcombo']['']);

				}else{
					$row['str_sqlcombo'] = array();

					if(!empty($row['str_consulta'])){

						//aqui pegamos o cadastro de tabelas (sys005) e recuperamos a tabela da consulta
						$sys005 = new Sys005();
						$sys005->recByTab($row['str_consulta']);

						//com o cadastro da tabela da consulta recuperado, descobrimos qual o campo de retorno da consulta
						//que esta cadastrado na tabela sys005 no campo str_retconsulta
						$sys001 = new Sys001();
						$sys001->recByTabCpo($row['str_consulta'],$sys005->str_retconsulta);
										
						//aqui setamos o tamanho do nosso campo igual ao tamanho do campo de retorno da consulta
						$row['int_tamform'] = $sys001->int_tamform;
						$row['int_tam'] = $sys001->int_tam;
						$row['str_chavconsulta'] = desanitizar($sys005->str_chavconsulta);
						$row['str_retconsulta'] = desanitizar($sys005->str_retconsulta);
						$row['str_filconsulta'] = desanitizar($sys005->str_filconsulta);

					}
				}
			}		
			
			$cab[$row['str_campo']] = $row;
		}

		return $cab;
	}
	
	public function sqlcombo2arr($sql,$localdb=false){

		try{
			
			if(substr($sql,0,6)=='local:'){
				$localdb=1;
				$sql = substr($sql,6);
			}
			
			//-- troca variaveis no sqlcombo
			preg_match_all('/{[^}]*}/',$sql,$matches);

			if ( !empty($matches[0])) {
				foreach ( $matches[0] as $var ) {
					eval('$value = '.substr($var,1,-1).';');
					$sql = str_replace($var,$value,$sql);
				}
			}

			//-- troca simbolos
			//-- para diminuir o comando, permite-se utilizar o ponto e vírgula (;) 
			//-- este será substituído por UNION SELECT
			$sql = str_replace(';','UNION SELECT',$sql);

			//-- SE NAO FOR UM SELECT "FROM" ALGUMA TABELA
			//-- predefine os nomes dos campos valor e descricao
			//-- nao precisa para base de dados local (mariadb ou mysql) apenas para mssqlnative
			if(strpos(strtolower($sql), "from")===false){

				//-- select base com o nome dos campos
				$sql = "SELECT '' AS VALUE, '' AS DESCR UNION ".$sql;
				//inicializa o array de retorno vazio, pois a primeira posicao em branco virá do select base
				$arr = array(); 

			}else{
				//-- inicializa o array de retorno com a primeira posição em branco
				$arr = array('' => ''); 
			}

			//try{
				$all = ($localdb ? $this->db->getAll($sql) : $this->datadb->getAll($sql));
			//}catch(Exception $e){
		//		throw new Exception('ERRO: '.$e->getMessage());
		//	}

			if(!empty($all)){
				$ak = array_keys($all[key($all)]);

				if(sizeof($ak)==2)
					foreach($all as $k => $a)
						$arr[$a[$ak[0]]] = $a[$ak[1]];
			}
			
			return $arr;
			
		}catch(Exception $e){
			echo $e->getMessage();
			return array(''=>'erro na origem dos dados');
		}
	}

	//-- retorna o array de campos que aparecerão no form
	public function cabecForm($opc){

		if($opc=='revisa')
			$opc='inclui';

		//-- filtra os campos que estao nas abas(tabs) que nao serao exibidas 
		//-- de acordo com a configuração de exibição do cadastro da aba
		$tabidx = $this->getTabs($opc);

		//-- traz os campos do form das abas(tabs) de acordo com a opc.
		$sql = " SELECT 'sys001' as str_from, sys001.* FROM ".MYSQL_DBNAME.".sys001 WHERE str_tab = ? AND str_form = 'S'";

		if(sizeof($tabidx))
			$sql .= " AND int_tabindex in (".implode(',', array_keys($tabidx)).")";

		$sql .= " ORDER BY int_tabindex, str_ordem";

		$rs = $this->db->Execute($sql,[$this->tabela]);	
		
		return $rs->_numOfRows ? $this->cabec($rs) : array();
		
	}	

	//-- retorna o array de campos que serao validados na submissao do form
	public function cabecSubmit($opc){
		return $this->cabecForm($opc);
	}
	
	//-- retorna a filial
	public function xFilial($tab=''){
		
		if($tab<>'' && $tab<>$this->tabela)
			$m = $this->db->GetOne("SELECT str_modo FROM ".MYSQL_DBNAME.".sys005 WHERE str_nome='".$tab."'");
		else
			$m = $this->modo;

		if(in_array($m,array('E','S')) && isset($_SESSION['usr_filial']))
			return $_SESSION['usr_filial'];
		else
			return '';
	}

	/**
	 * Esta funcao traz as pastas do formulario
	 */
	public function getTabs($opc){
		/*
		$arr = $this->db->getAll("SELECT * FROM ".MYSQL_DBNAME.".sys004 
				 WHERE str_tabela = ? ORDER BY str_ordem, id asc",[$this->tabela]);
		
		return empty($arr) ? array(Sys004::getGenericTab()) : $arr;
		*/

		$s4=new Sys004();
		$s4->get('*',['str_tabela=?',[$this->tabela]],"str_ordem,id");

		$tabidx = [];
		$i=0;
		while($s4->result()){
			if($s4->dados_result['str_'.$opc]=='S'){
				//echo $s4->str_folder;
				$tabidx[$i] = $s4->dados_result;
			}
			$i++;
		}

		return empty($tabidx) ? array(Sys004::getGenericTab()) : $tabidx;

	}
				
	public function setFetch(){
			$this->datadb->SetFetchMode(ADODB_FETCH_ASSOC);
	}

	//-- verifica se no cadasro da tabela está marcado a opção de banco de arquivos
	public function issetBancoArq(){
		return $this->db->GetOne("SELECT str_formfiles FROM ".MYSQL_DBNAME.".sys005 WHERE str_nome = '".$this->tabela."'")=='S';
	}
	
	public static function getParam($tab,$model=''){

		$model = empty($model) ? new Model : new $model;
			
		$rs = $model->db->Execute("SELECT 'sys003' as str_from, sys003.* FROM ".MYSQL_DBNAME.".sys003 
									 WHERE str_tab = '$tab'
									 AND str_form = 'S'
									 order by str_ordem");

		return $model->cabec($rs);

	}
	
	public function getModo(){
		return $this->db->GetOne("SELECT str_modo FROM ".MYSQL_DBNAME.".sys005 WHERE str_nome='".$this->tabela."'");
	}
	
	public function getRetConsulta(){
		return $this->db->GetOne("SELECT str_retconsulta FROM ".MYSQL_DBNAME.".sys005 WHERE str_nome='".$this->tabela."'");
	}

	public function getSqlComboDesc($tab,$cpo,$val,$sys='sys001'){

		$sql = $this->db->GetOne("SELECT str_sqlcombo FROM ".MYSQL_DBNAME.".".$sys." 
									 WHERE str_tab = '$tab'
									 AND str_campo='$cpo'");

		return $this->datadb->GetOne("SELECT x2 FROM (SELECT 'x1','x2' UNION $sql) DBX WHERE x1='$val'");

	}

	public function rollback(){
		$this->datadb->RollbackTrans();
	}
		
	public function beginTrans(){
		$this->datadb->BeginTrans();	
	}
	
	public function save() {
		$this->datadb->CommitTrans( );
	}

	public function commit() {
		$this->datadb->CommitTrans( );
	}
	
	public function ExecQuery($sql,$get=''){
		
		if(!empty($get)){
			if($get=='one'){
				$rs = $this->datadb->GetOne($sql);
				$this->num_rows = $rs ? 1 : 0;
			}elseif($get=='row'){
				$rs = $this->datadb->GetRow($sql);
				$this->num_rows = $rs ? 1 : 0;
			}elseif($get=='all'){
				$rs = $this->datadb->GetAll($sql);
				$this->num_rows = sizeof($rs);
			}
		}else{
			$rs = $this->datadb->Execute($sql);
			$this->num_rows = $this->datadb->Affected_Rows();
		}
		
		return $rs; 
	}
	
	public function getNextId($id){
		return $this->getone('min('.$this->id_field.') as id',"'".$this->id_field."'>'".$id."'");
	}
	
	public function getPrevId($id){
		return $this->getone('max('.$this->id_field.') as id',"'".$this->id_field."'<'".$id."'");
	}
}