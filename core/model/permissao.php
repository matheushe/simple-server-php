<?php

class Permissao
{
	/**
	 * retorna uma lista de email de todos pertencentes ao grupo
	 * date 2016-10-06
	 * @return [type] [description]
	 */
	public static function getEmails($grupo,$ignore_interno=false, $global = false)
	{
		$p = new Syspermissao;
		if($global)
			$p->modo = 'S';

		$r = $p->getall("str_login","str_grupo = '$grupo'");
		$em = array();

		foreach ($r as $k)
		{
			$t = trim(Usuario::getEmail($k['str_login'], $global));
			if ($t != '' && !empty($t) &&  isEmail($t)){
				$em[] = strtolower($t);
			}
		}
		return $em;
	}

	/**
	 * Verifica se o usuário esta na lista no grupo
	 * date 2016-10-06
	 * @return [type] [description]
	 */
	public static function verifica($grupo, $login="",$global=false)
	{

		//-- se nao passar o login verifica o usuario logado
		if(empty($login)){

			if(!isset($_SESSION['usr_login']) || empty($_SESSION['usr_login']))
				return false;

			$login = $_SESSION['usr_login'];

		}

		$p = new syspermissao;
		if($global)
			$p->modo = 'S'; // faz com que os metodos get() do model não filtre filial

		$r = $p->getAll("id","str_grupo = '$grupo' and str_login = '$login'");

		return count($r) > 0;
	}
	
	/**
	 * retorna todos os logins daquele grupo
	 * date 2016-10-07
	 * @param  [type] $grupo [description]
	 * @return [type]        [description]
	 */
	public static function getMembros($grupo, $global = false){
		$p = new syspermissao;
		if($global)
			$p->modo = 'S';
		return $p->getall("str_login",["str_grupo=?",[$grupo]]);
	}

	/**
	 * retorna todos os logins daquele grupo de todas as filiais
	 * date 2018-11-21
	 * @param  [type] $grupo [description]
	 * @return [type]        [description]
	 */
	public static function getFullMembers($grupo){
		$p = new syspermissao;
		$p->modo='S';
		return $p->getall('str_filial,str_login',['str_grupo=?',[$grupo]]);
	}
}