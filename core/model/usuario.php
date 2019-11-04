<?php

class Usuario extends Model{

	public function authbd(){
		
		$where = " str_login = '".$this->dados['str_login']."'
				 AND str_senha = md5('".$this->dados['str_senha']."')";
		
		$this->get('id',$where);
		$this->result();
	
		return ($this->num_rows > 0 && $this->id);
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

	
	public function getCCbyLogin($login,$emp='')
	{
		if(empty($emp))
		{
			if(!isset($_SESSION['usr_filial']))
				die('erro, falta sessão usr_filial');
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
				die('erro, falta sessão usr_filial');
			else
				$emp=$_SESSION['usr_filial'];
		}
		
		return $this->db->GetOne(" SELECT str_nome FROM usuario WHERE str_login = '$login' and str_filial='".$emp."'");
	}

	/**
	 * Matheus Henrique
	 * Recupera o Login por meio da matricula
	 */
	public function getLoginbyMatr($matr,$emp='')
	{
		if(empty($emp))
		{
			if(!isset($_SESSION['usr_filial']))
				die('erro, falta sessão usr_filial');
			else
				$emp=$_SESSION['usr_filial'];
		}
		
		return $this->db->GetOne(" SELECT str_login FROM usuario WHERE str_matr like '%$matr' and str_filial='".$emp."'");
	}
	
	public function getComputador(){
		$sql = " SELECT id FROM computador WHERE str_usuario = '".strtolower($_SESSION['usr_login'])."'";
		return $this->db->GetOne($sql);
	}
	
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

	public function getFullName($login){
					$this->get('str_nome',"str_login = '" . $login . "' ");
					while( $this->result() )
							return $this->str_nome;
	}

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