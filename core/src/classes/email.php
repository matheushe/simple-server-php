<?php

require_once 'lib/smtp/smtp.php';

class Email extends SMTP{

	public $to = array();
	public $msg;
	public $assunto;
	public $has_email_added = false;
	public $headers;
	public $try_send_interno = false; // flag para verificar se tentou

	public function __construct(){

		parent::__construct();
		
		$this->Delivery('relay');
		$this->Relay(SMTP_SERVER, SMTP_ACCOUNT,SMTP_PASS, SMTP_PORT, 'login', false);
		$this->TimeOut(10);

		$this->From(SMTP_ACCOUNT, SYSNAME);

		$this->replyto(SISTEMA_PROPRIETARIO_EMAIL, SISTEMA_PROPRIETARIO_NOME);
	}
	
	//Adiciona email no campo "Responder para" removendo os anteriores
	public function ReplyTo($email,$name = '')
	{
		$this->delheader('Reply-To');
		$this->addheader('Reply-To', $name . '<'.$email.'>');
	}

	//Remover todos os "responder para"
	public function DelReplyTo()
	{
		$this->delheader('Reply-To');
	}

	//Adiciona email no campo "Responder para"
	public function AddReplyTo($email,$name = '')
	{
		$this->addheader('Reply-To', $name . '<'.$email.'>');
	}

	/**
	 * Adiciona emails (atraves de grupo) ao destinatario responder o email
	 * para inserir apenas um destinatario utilizar AddReplyTo.
	 * Autor: Matheus Henrique
	 * AddReplyToGroup('Analista') -> remove o remetente padrao (informatica).
	 * AddReplyToGroup('Analista', false) -> mantem o remetente padrao (informatica).
	*/
	public function AddReplyToGroup($grp,$rmv = true, $global = true)
	{
		if($rmv) $this->delheader('Reply-To');

		if($global)
			$aTo = Permissao::getEmails($grp, false, true);
		else
			$aTo = Permissao::getEmails($grp);

		foreach($aTo as $a)
			$this->addheader('Reply-To',$a);
	}
	
	//Ferifica se o email informado e valido
	public function isEmail($addr)
	{
		$addr=trim($addr);
		return FUNC::is_mail($addr) && !empty($addr);
	}

	//Adiciona os email no campo destinatario do email. 
	//Uso interno
	public function from($adrr, $name = '')
	{
		//-- tratamento para email interno
		if(strpos($adrr,".interno")!==false) {
			alert('Não é possível enviar e-mail de destinatário interno: '.$adrr,false);
			return;
		}
		parent::from($adrr,$name);
	}

	//Adiciona os emails no campo destinatario do email
	//Conta com varias tratativas.
	//!!! Utilizar esta funcao para adicionar os emails !!!
	public function addto($adrr, $name = '', $pass  = false)
	{
		if(is_array($adrr))
			return;

		if(empty($adrr))
			return;

		if(!isEmail($adrr))
			return;

		$adrr = trim($adrr);

		//-- tratamento para email interno
		if(strpos($adrr,".interno")!==false) {
			alert('Não é possível enviar e-mail para destinatário interno: '.$adrr,false);
			$this->try_send_interno = true;
			return;
		}

		//Valida para nao enviar email para contas externas quando em ambiente dev
		if(!$pass && !IsProducao()) {
			echo 'AMBIENTE DEV: E-mail externo adicionado: '.$adrr . '<br>';
			return;
		}			

		//-- tratamento para nao inserir duplicado
		if(!empty($adrr) && !in_array($adrr,$this->to)) {
			$this->to[] = $adrr;
			parent::addTo($adrr,$name);	

			//-- flag para definir que existe pelo menos um email adicionado
			$this->has_email_added=true;
		}	
	}

	// Adcionar destinatarios por meio de grupos de usuarios
	//Ao inves de deixar fixo os email no codigo da rotina, utilize grupos :)
	public function addToGrp($grp, $global = true)
	{
		if($global)
			$aTo = Permissao::getEmails($grp, false, true);
		else
			$aTo = Permissao::getEmails($grp);

		//adiona os destinatários de e-mail        
		foreach($aTo as $a){
			$this->addto($a);
		}

	}

	/**
	 * Definir Prioridade no E-mail;
	 * Caso seja Necessário Marcar o email como alta prioridade bata utilizar esta funcao.
	 * Exemplos:
	 * defPrioridade()  -> Alta Prioridade
	 * defPrioridade(3)  -> Prioridade Normal
	 * defPrioridade(5)  -> Baixa Prioridade
	 * Autor: Matheus Henrique 11/03/2019
	*/
	public function defPrioridade($level = 1)
	{
		if($level == 1 || $level == 3 || $level ==5)
			$this->Priority($level);
		else
			return '';
	}

	/**
	 * Adicionar destinatario para receber copia do email;
	 * Caso seja Necessário adicionar um detinatario para receber uma copia dos emails e suas respostas. (CC)
	 * Exemplos:
	 * addCCopia('matheusha@whbbrasil.com.br','Matheus Henrique Rodrigues')
	 * addCCopia(null,null, 'grupo') -> Conjunto de emails
	 * Analista: Matheus Henrique 11/03/2019
	*/
	public function addCCopia($adrr, $name = '', $grp = false)
	{		
		if($grp){
			$aTo = Permissao::getEmails($grp);
			foreach($aTo as $a){
				$adrr = trim($a);

				//-- tratamento para nao inserir duplicado
				if(!empty($adrr) && !in_array($adrr,$this->to)){
					$this->to[] = $adrr;
					parent::addCC($adrr);	
				}
			}
		}else{
			$adrr = trim($adrr);

			//-- tratamento para nao inserir duplicado
			if(!empty($adrr) && !in_array($adrr,$this->to)){
				$this->to[] = $adrr;
				parent::addCC($adrr,$name);	
			}
		}
	}

	/**
	 *  Adicionar destinatario para receber copia oculta do email;
	 * Caso seja Necessário adicionar um detinatario para receber uma copia oculta - sem que os detinatarios saibam destes remetentes- dos emails e suas respostas. (CCO)
	 * Exemplos:
	 * addCCOculta('matheusha@whbbrasil.com.br') -> UM UNICO EMAIL
	 * addCCOculta(NULL, 'grupo') -> Conjunto de emails
	 * Analista: Matheus Henrique 11/03/2019
	*/
	public function addCCOculta($adrr, $grp = false)
	{
		if($grp){
			$aTo = Permissao::getEmails($grp);
			foreach($aTo as $a){
				$adrr = trim($a);

				//-- tratamento para nao inserir duplicado
				if(!empty($adrr) && !in_array($adrr,$this->to)){
					$this->to[] = $adrr;
					parent::addbcc($adrr);	
				}

			}
		}else{
			$adrr = trim($adrr);

			//-- tratamento para nao inserir duplicado
			if(!empty($adrr) && !in_array($adrr,$this->to)){
				$this->to[] = $adrr;
				parent::addbcc($adrr);	
			}
		}
	}

	/** 
	 * Solicita Confirmacao de Recebimento;
	 * Caso seja Necessário solicitar a confirmacao de recebimento de um email
	 * !!!! Aparentemente so funciona com alguns servicos de email !!!!
	 * Exemplos:
	 * slcRecebimento('matheusha@whbbrasil.com.br') -> UM UNICO EMAIL
	 * slcRecebimento(null, 'grupo') -> Conjunto de emails		
	 * Analista: Matheus Henrique 11/03/2019
	*/
	public function slcRecebimento($adrr = false, $grp = false)
	{
		if($grp){
			$aTo = Permissao::getEmails($grp);
			foreach($aTo as $a){
				$adrr = trim($a);
					parent::addheader('Return-Receipt-To',$adrr);
			}
		}else{	
			if($adrr)
				parent::addheader('Return-Receipt-To',$adrr);
			else
				return;
		}
	}

	/** 
	 * Solicita Confirmacao de leitura;
	 * Caso seja Necessário solicitar a confirmacao de leitura de um email
	 * Exemplos:
	 * slcLeitura('matheusha@whbbrasil.com.br') -> UM UNICO EMAIL
	 * slcLeitura(NULL, 'grupo') -> Conjunto de emails
	 * Autor: Matheus Henrique 11/03/2019
	*/
	public function slcLeitura($adrr = false, $grp = false)
	{
		if($grp){
			$aTo = Permissao::getEmails($grp);
			foreach($aTo as $a){
				$adrr = trim($a);
					parent::addheader('Disposition-Notification-To',$adrr);
			}
		}else{
			if($adrr)
				parent::addheader('Disposition-Notification-To',$adrr);
			else
				return;
		}
	}

	//Dispara o email, informe sempre o assunto
	public function send($assunto)
	{
		try{
			if($this->has_email_added) {
				$this->isDev();
				return parent::send($assunto);
			}				

			//-- se nao adicionou nenhum e-mail 
			//-- e também não foi tentativa de envio para e-mail interno
			//-- pode ser falta de tratamento no fonte por parte do analista
			elseif(!$this->try_send_interno){
			
				$e=new Email;

				if(isset($_SESSION['usr_login'],$_SESSION['usr_email']) && Permissao::verifica('analista') && isEmail($_SESSION['usr_email']))
					$e->addTo($_SESSION['usr_email']);
				else
					$e->addToGrp('email_destinatario');

				$e->html('usuario: '.(isset($_SESSION['usr_login'])  ? $_SESSION['usr_login']  : '-').'<br>'.
								 'filial: ' .(isset($_SESSION['usr_filial']) ? $_SESSION['usr_filial'] : '-').'<br>'.
								 'ramal: '  .(isset($_SESSION['usr_ramal'])  ? $_SESSION['usr_ramal']  : '-').'<br>'.
								 'programa: '.$_GET['p'].'-'.$_GET['op'].'<br>'.
								 'assunto:  '.$assunto.'<br>'.
								 'contexto: '.print_r($this,true));
						 
				$e->send('*** EMAIL ENVIADO SEM DESTINATARIO VIA ' . SYSNAME. ' ***');
			}
		}catch(Exception $e){
			throw new Exception($e->getMessage());
		}
	}

    /**
     * Adciona um arquivo no email como anexo
     * as demais chamadas são iguais da classe Email
     * $pathFile - passar o caminho do arquivo
     * $nameFile - nome do arquivo que vai sair no e-email
     * $type = tipo do arquivo
     */
	public function addFile($pathFile, $nameFile= 'Anexo', $type = 'autodetect')
    {
        $this->attachfile($pathFile, $nameFile, $type, $disposition = 'attachment', $encoding = 'base64');
	}
	

	/**
	 * Verifica se está no ambiente dev e adciona uma mensagem que 
	 * o e-mail foi enviado do ambiente desenvolvimento
	 */
	private function isDev()
	{
		if (!isProducao()) {
			// $html = $this->_ahtml[2]; // recupera o corpo do email
			$html = "<span style='background-color: #FFEB3B; color: black;'>ATENÇÃO! EMAIL ENVIADO ATRAVÉS DO AMBIENTE DE DESENVOLVIMENTO. FAVOR DESCONSIDERAR! </span> <br>" . $this->_ahtml[2]; // concatena a mensagem
			self::html($html); // inclui novamente o corpo de email
		}
	}

	/**
	 * public function sendEmailSimples($assunto, $msg, $destinatario)
	 * Função para envio de simples emails de informacoes
	 * Pode ser utilizado para enviar um simples contador ou algum dado simplorio
	 * !!! Nao utilizar para rotinas muito elaboradas, foi pensada para envios simples !!!
	 * Exemplo de uso:
	 * sendEmailSimples('QUA029', "Corrigido $qtd", 'matheusha@whbbrasil.com.br');
	 * Matheus Henrique -- 03/05/2019
	 */
	public function sendEmailSimples($assunto, $msg, $destinatario)
	{
		if(!is_array($destinatario))
			$this->addto($destinatario);			
		else{
			foreach ($destinatario as $key)
				$this->addto($key);
		}

		$this->html($msg .'<br>');
		$this->send($assunto);
	}
}