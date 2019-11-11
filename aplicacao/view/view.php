<?php

class View{
	
	public static function imprime($html,$dados=array(),$tit='Portal WHB',$imp=false){

		$header = '';

		$header = implode("\n",$_SESSION['inc_js_files']) . "\n\n" .
					implode("\n",$_SESSION['inc_css_files']);
		
			$html  = View::get_html('html.html',
					 array("%HEAD%" => $header,
								 "%CON%" => View::ret_html($html,$dados),
								 "%TIT%" => $tit));
		
		if($imp)
			$html .= View::js('','$(function(){ window.print(); });');
			
		$html .= '<style>body,html{ overflow:auto }</style>';

		echo $html;
		
	}
	
	public function show(){
		echo $this->html();
	}

	public static function getUrl($nome,$subfolder){
		
		$msg=  $nome . ' - ';
		
		eval('$fld = '.FOLDERS.';');
		foreach($fld as $f){
			$msg .='--------procurando em: '.$f.DS.'view'.DS.$subfolder.DS . '<br>';
			if(file_exists($f.DS.'view'.DS.$subfolder.DS.$nome)){
				$msg .= 'encontrado em: ' . $f.DS.'view'.DS.$subfolder.DS . '<br>';
				return $f.DS.'view'.DS.$subfolder.DS.$nome;
			}
		}
		$msg .= 'não encontrado <br>';
		
		echo $msg;
		
		return '';

	}

	public static function get_php($url,$dados=array()){

		$url .= substr($url,-4)!='.php' ? '.php' : '';
		
		$msg=  $url . ' - ';
		
		eval('$fld = '.FOLDERS.';');
		foreach($fld as $f){
			$msg .='--------procurando em: '.$f.DS.'view'.DS.'html'.DS . '<br>';
			if(file_exists($f.DS.'view'.DS.'html'.DS.$url)){
				$msg .= 'encontrado em: ' . $f.DS.'view'.DS.'html'.DS . '<br>';

				ob_start();
				require $f.DS.'view'.DS.'html'.DS.$url;
				$contents = ob_get_contents();
				ob_end_clean();
				return View::ret_html($contents,$dados);
			}
		}
		$msg .= 'não encontrado <br>';
		
		echo $msg;
		
		return '';
	
	}

	public static function get_html($url,$dados=array()){
		
		$url .= (substr($url,-5)!='.html' && substr($url,-4)!='.htm') ? '.html' : '';
		
		$msg=  $url . ' - ';
		eval('$fld = '.FOLDERS.';');
		foreach($fld as $f){
			$msg .='--------procurando em: '.$f.DS.'view'.DS.'html'.DS . '<br>';
			if(file_exists($f.DS.'view'.DS.'html'.DS.$url)){
				$msg .= 'encontrado em: ' . $f.DS.'view'.DS.'html'.DS . '<br>';
				return View::ret_html(View::get_file($f.DS.'view'.DS.'html'.DS.$url),$dados);
			}
		}
		$msg .= 'não encontrado <br>';
		
		echo $msg;
		
		return '';
	}
	 
	public static function get_css($url,$dados=array()){

		$msg=  $url . ' - ';
		eval('$fld = '.FOLDERS.';');
		foreach($fld as $f){
			$msg .='--------procurando em: '.$f.DS.'view'.DS.'css'.DS . '<br>';
			if(file_exists($f.DS.'view'.DS.'css'.DS.$url)){
				$msg .= 'encontrado em: ' . $f.DS.'view'.DS.'css'.DS . '<br>';
				return View::ret_html(View::get_file($f.DS.'view'.DS.'css'.DS.$url),$dados);
			}
		}
		$msg .= 'não encontrado <br>';
		
		echo $msg;
		
		return '';

	}

	public static function get_js($url,$dados=array()){
		
		$url .= (substr($url,-3)!='.js' ? '.js' : '');
		
		$msg=  $url . ' - ';
		eval('$fld = '.FOLDERS.';');
		foreach($fld as $f){
			$msg .='--------procurando em: '.$f.DS.'view'.DS.'js'.DS . '<br>';
			if(file_exists($f.DS.'view'.DS.'js'.DS.$url)){
				$msg .= 'encontrado em: ' . $f.DS.'view'.DS.'js'.DS . '<br>';
				return View::ret_html(View::get_file($f.DS.'view'.DS.'js'.DS.$url),$dados);
			}
		}
		$msg .= 'não encontrado <br>';
		
		echo $msg;
		
		return '';
	}

	
	public static function js($src='',$con=''/*,$min=true*/){
		
		if((empty($src) && empty($con))) return '';

		$js = '<script type="text/javascript"';

		if(!empty($src)){
			$js .= ' src="';
			$js .= $src.(strpos($src,'?')===false ? '?' : '&') . 'nocache='.date('YmdHis').'"';
		}

		$js .= '>';

		if(!empty($con))
			$js .= $con;

		$js .= '</script>';

		return $js;
	}	 

	public static function addJs($url/*,$ajax=0*/){

		$js = View::js($url);

		if(!empty($js) && isset($_SESSION['inc_js_files']) && !in_array($js,$_SESSION['inc_js_files'])){
			$_SESSION['inc_js_files'][] = $js;
			
			return $js;
		}

		return false;
	}

	public static function css($src='',$media=''){
		
		if((empty($src))) return '';

		$css = '<link type="text/css" rel="stylesheet" ';

		if(!empty($src)){
			$css .= ' href="';
			$css .= $src.(strpos($src,'?')===false ? '?' : '&') . 'nocache='.date('YmdHis').'"';
		}

		$css .= (!empty($media) ? ' media="'.$media.'"' : '' ).' />';

		return $css;
		
	}	 

	public static function addCss($url,$ajax=0){

		$css = View::css($url,'');
		
		if(!empty($css) && isset($_SESSION['inc_css_files']) && !in_array($css,$_SESSION['inc_css_files'])){
			$_SESSION['inc_css_files'][] = $css;

			if($ajax && AJAX)
				echo $css;
			
			return $css;
		}

		return false;
	}
	
	public static function get_icon($url){

		$urlfull = "app/view/img/icons/".$url;
			
		if(!file_exists($urlfull) || empty($url)){
			$urlfull = "app/view/img/icons/default.png";
		}
		
		return $urlfull;
	}

	public static function get_file($url,$dados=array()){

		if(file_exists($url)){
			$file = View::ret_html(fread(fopen($url,"r"),filesize($url)),$dados);
		}else{
				trigger_error('Arquivo não encontrado: '.$url);
			$file = "";
		}

		return $file;
	}

	/**
	 * escreve o cabecalho html
	 */
	public static function cabec($titulo){
		echo  View::get_html("cabec",array("%TIT%" => $titulo));
	}
	
	public static function msg($msg,$tipo,$but=array(),$msg2=''){//true,$click="",$bt1=null){
		
		//-- $but == false #does not display any but
		if(is_bool($but) && !$but)
			$bt_html = '';

		//-- $but can be an URL to redirect onclick
		elseif(is_string($but))
			$but = array(new Button("Voltar","voltar.png",$but,"button","V",100,40));
		
		//-- array of buttons, list of options
		if(is_array($but)){
			if(empty($but))
				$but[] = new Button("Voltar","voltar.png","window.history.back()","button","V",100,40);

			$bt_html = '';
			foreach($but as $b)
				$bt_html .= $b->html();
		}
		
		$aimg = array('alert'   => array('danger'  , '32/error'   ),
					  'error'   => array('danger'  , '32/error'   ),
					  'warning' => array('warning' , '32/error'   ),
					  'info'    => array('info'    , '32/info'    ),
					  'success' => array('success' , '32/success' ),
					  'confirm' => array('warning' , '32/confirm' ));

		$t=new Tela();
		
		$t->conteudo = View::get_html("mensagem.html",
										array("%IMG%" => View::get_icon($aimg[$tipo][1].'.png'),
													"%MSG%" => $msg,
													"%MS2%" => $msg2,
													"%BTN%" => $bt_html,
													"%ALR%" => $aimg[$tipo][0],
													"%ID%"  => $t->id));

		return $t->html();
	
	}
	
	public static function ret_html($html,$dados=array()){
		
		foreach($dados as $key => $d)
			$html = str_replace($key,$d,$html);

		return $html;
	}	

	public function echo_html($html,$dados=array()){
		echo View::ret_html($html,$dados);	
	}
	
	public static function get_flash($src,$width,$height,$vars='',$wmode="opaque"){
		return View::get_html('flash.html',
			 array('%WIDTH%'  => $width,
						 '%HEIGHT%' => $height,
						 '%SRC%'    => $src,
						 '%VARS%'   => $vars,
						 '%WMODE%'  => $wmode));
		
	}

	//-- joaofr
	//-- 20/07/2018
	//-- método que retorna a tag html do icone
	//-- aceita como parametro:
	//-- * o nome do arquivo .png (ex: 'default.png' || 'ok.png' || 'voltar.png', etc...)
	//-- * nomes de icones font-awesome ou glyphicon (ex: 'fa fa-desktop' || 'glyphicon flyphicon-star')
	public static function icon($img,$width='',$height='',$alt=''){

		$color = '';
		if(strpos($img,'#')!==false){
			$color = substr($img,strpos($img,'#'),7);
			$img=str_replace($color,'',$img);
		}

		if(strpos($img,' glyphicon-')>-1 || strpos($img,' fa-')>-1){
			return '<span class="'.$img.'" '.(!empty($color) ? 'style="color:'.$color.'" ' : '').'aria-hidden="true"></span> ';
		}else{
			return '<img src="'.View::get_icon($img).'"' . 
			($width  ? ' width="' .$width .'"' : '') . 
			($height ? ' height="'.$height.'"' : '') .
			($alt    ? ' alt="'   .$alt   .'"' : '') . '/>';
		}
	}

}