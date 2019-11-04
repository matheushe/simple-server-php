<?php

/**
 * funcao autoload
 * funcao mágica do php para carregar classes automaticamente
 * mais informacoes: http://php.net/manual/en/language.oop5.autoload.php
 */

spl_autoload_register(function($className) { 

	if(strtolower($className)=='email'){
		require_once 'core/src/classes/email.php';
		return;
	}

	eval('$folders = '.FOLDERS.';');
	
	eval('$subfolders = '.SUBFOLDERS.';');

	$msg = '';

	foreach($folders as $folder){
		
		if(autoload_search($folder,$className))
			return;
		
		foreach($subfolders as $sub){
			if(autoload_search($folder.DS.$sub,$className))
				return;
		}
	}
	
	if(autoload_search('core'.DS.'src'.DS.'classes',$className))
		return;

	//-- se nao conseguir instanciar
	$m = new Model();
	$m->tabela = 'sys008';
	$extend = $m->getone("str_extend","str_nome = '$className'");

	$extend = explode("\\",$extend);
	$extend = array_pop($extend);

	$m->__destruct();

	if(!empty($extend)){
		//-- cria uma classe dinamicamente sem conteúdo
		eval('class '.ucfirst(strtolower($className)).' extends '.ucfirst(strtolower($extend)).'{}');

	}else

	throw new Exception("CLASSE NAO ENCONTRADA: " . $className . $msg);
});


function autoload_search($fld,$arq)
{
	$file = $fld.DS;

	if (file_exists($file.strtolower($arq).'.php')){
		require_once($file.strtolower($arq).'.php');
		return true;
	}
	return false;
}

/**
 * Funcao utilizada para debugar variáveis na execução dos scripts php
 */
function ver($var,$exit=true)
{
	$a = debug_backtrace();
	$memory = round(((memory_get_peak_usage(true) / 1024) / 1024), 2). 'Mb';
	echo '<pre>
	<small>memory:'. $memory .' # '.$a[0]['file'] . (isset($a[1]['function']) ? ' -> ' . $a[1]['function'] : null) . ': ' . $a[0]['line']. '</small><hr>
	<font color="#cc0000">';
		print_r( $var );
	echo '</font>
	</pre>';
	
	if($exit) exit;
}

/**
 * Trata os textos para não permitir inserção de códigos 
 */
function sanitizar($str,$mysql=true)
{
	$str = str_replace("'","&#39;",$str);
	$str = str_replace("\"","&quot;",$str);
	$str = str_replace("<","&lt;",$str);	
	$str = str_replace(">","&gt;",$str);	

	if($mysql){
		$str = str_replace('\\',"\\\\",$str);
		$str = str_replace('\'',"\\'",$str);
	}

	return $str;
}

function desanitizar($str)
{
	$str = str_replace("&#39;" ,"'" ,$str);
	$str = str_replace("&quot;","\"",$str);
	$str = str_replace("&lt;"  ,"<" ,$str);	
	$str = str_replace("&gt;"  ,">" ,$str);	

	return $str;
}

/**
 * Passa data de "AAAA-MM-DD" para "DD/MM/AAAA"
 */
function dtoc($data)
{
	if ($data == '') return '';
	$data = explode('-', $data);
	return $data[2].'/'.$data[1].'/'.$data[0];
}

/**
* Passa data de "YYYY-MM-DD H:i:s" para "DD/MM/AAAA H:i:s"
*/
function dateTimetoc($datetime)
{
	$dataHora = dtoc(substr($datetime, 0, 10));
	$dataHora .= substr($datetime, 10, 19);
	return $dataHora;
}

/**
 * Passa data de "DD/MM/AAAA" para "AAAA-MM-DD" *
 */
function ctod($data)
{
	if ($data == '') return '';
	$dttrat = explode("/",$data);
	return $dttrat[2]."-".$dttrat[1]."-".$dttrat[0];
}


//-- Passa data de "AAAA-MM-DD" para "AAAAMMDD"
function dtomsd($data)
{
	return str_replace('-','',$data);
}

//-- Passa data de "DD/MM/AAAA" para "AAAAMMDD"
function ctomsd($data)
{
	return substr($data,6,4).substr($data,3,2).substr($data,0,2);
}

//-- Passa data de "AAAAMMDD" para "DD/MM/AAAA"
function msdtoc($data)
{
	//recebe o parâmetro e armazena em um array separado por -
	return substr($data,6,2).'/'.substr($data,4,2).'/'.substr($data,0,4);
}

function msdtod($data)
{
	$data = substr($data,0,4).'-'.substr($data,4,2).'-'.substr($data,6,2);
	return $data;
}

function ms_desformata_data($data)
{
	$data = substr($data,0,4).'-'.substr($data,4,2).'-'.substr($data,6,2);
	return $data;
}

function data_to_msdata($data)
{
	$data = substr($data,6,4).substr($data,3,2).substr($data,0,2);
	return $data;
}

//passa a data do formato DD/MM/YYYY para DD/MM/YY
function year2digits($data)
{
	if ($data == '') return '';
	$aux = explode("/", $data);
	$dia = $aux[0];
	$mes = $aux[1];
	$ano = substr($aux[2], -2);
	$data = $dia."/".$mes."/".$ano;
	return $data;
}

/* Retorna o trimestre do mes informado
*	retornaTrimeste(2) => 1
*	retornaTrimeste(2, true) => 1º Trimestre
*	retornaTrimeste(2, true, 3) => 1º Trim
* Matheus Henrique 18/02/2019
*/
function retornaTrimeste($mes, $formatado = false, $letras = false)
{
	$tri = 0;
	if($mes >= 1 || $mes <= 3){
		$tri = 1;
	}elseif($mes >=4 || $mes <= 6){
		$tri = 2;
	}elseif($mes >= 7 || $mes <= 9){
		$tri = 3;
	}elseif($mes >= 10 || $mes <= 12){
		$tri = 4;
	}
	if($formatado && !$letras)
		return $tri . 'º Trimestre';

	if($letras)
		return $tri . 'º ' . substr('Trimestre', 0, $letras);

	return $tri;
}

/**
 * Retorna um array com as datas de feriados nacionais (federais), !!! nao retorna os pontos facultativos !!!
 * caso necessario utilizar in_array para verificar datas.
 * Exemplos:
 * 	daysHolidays(null, false, false) || daysHolidays('2019', false, false)
 * 		Retorno->  [0] => 2019-01-01; [1] => 2019-03-04 ....
 * 	daysHolidays(null, false, true) || daysHolidays('2019', false, true)
 * 		Retorno-> [0] => 01/01/2019; [1] => 04/03/2019 ....
 *	daysHolidays() --> Hoje e feriado?;
 * 		Retorno-> true ou false;
 * Matheus Henrique
 * Data 18/04/2019
 */
function daysHolidays($ano = null, $isDay = true, $dtoc = false)
{
	// Verifica se informou um ano, caso nao tenha informado pega o ano atual
    if($ano === null)
		$ano = intval(date('Y'));
	
	// Calcular datas com base na Pascoa
	$pascoa     = easter_date($ano);	// Limite de 1970 ou após 2037 da easter_date PHP consulta http://www.php.net/manual/pt_BR/function.easter-date.php
    $dia_pascoa = date('j', $pascoa);	// Dia da pascoa para a data informada
    $mes_pascoa = date('n', $pascoa);	// Mes da pascoa para a data informada
	$ano_pascoa = date('Y', $pascoa);	// Ano da pascoa apenas para padronizacao
	
    $feriados = array(
		// mktime(0, 0, 0, 1,  1,   $ano)
		// 					Mes, Dia, $ano

		// Datas Fixas dos feriados Nacionais Basileiros
        mktime(0, 0, 0, 1,  1,   $ano), // Confraternização Universal - Lei nº 662, de 06/04/49
        mktime(0, 0, 0, 4,  21,  $ano), // Tiradentes - Lei nº 662, de 06/04/49
        mktime(0, 0, 0, 5,  1,   $ano), // Dia do Trabalhador - Lei nº 662, de 06/04/49
        mktime(0, 0, 0, 9,  7,   $ano), // Dia da Independºncia - Lei nº 662, de 06/04/49
        mktime(0, 0, 0, 10,  12, $ano), // N. S. Aparecida - Lei nº 6802, de 30/06/80
        mktime(0, 0, 0, 11,  2,  $ano), // Todos os santos - Lei nº 662, de 06/04/49
        mktime(0, 0, 0, 11, 15,  $ano), // Proclamação da republica - Lei nº 662, de 06/04/49
		mktime(0, 0, 0, 12, 25,  $ano), // Natal - Lei nº 662, de 06/04/49
		
        // Calculo de feriados que se baseam no dia da pascoa
        mktime(0, 0, 0, $mes_pascoa, $dia_pascoa - 48,  $ano_pascoa),// 2ºferia Carnaval
        mktime(0, 0, 0, $mes_pascoa, $dia_pascoa - 47,  $ano_pascoa),// 3ºferia Carnaval 
        mktime(0, 0, 0, $mes_pascoa, $dia_pascoa - 2 ,  $ano_pascoa),// 6ºfeira Santa  
        mktime(0, 0, 0, $mes_pascoa, $dia_pascoa     ,  $ano_pascoa),// Pascoa
        mktime(0, 0, 0, $mes_pascoa, $dia_pascoa + 60,  $ano_pascoa),// Corpus Cirist
	);

	// Organiza as datas
	sort($feriados);
	
	// Cria um array com as datas e verifica se deseja passar um converter para a data (dtoc)
	for ($x=0;$x<sizeof($feriados);$x++)
			$datas[] = $dtoc ? dtoc(date('Y-m-d',$feriados[$x])) : date('Y-m-d',$feriados[$x]);
	
	// Caso deseja obter se a data atual (hoje) é um dos feriados nacionais.
	if($isDay)
		return in_array(date('Y-m-d'), $datas);

	return $datas;
}
	
/**
 * Retorna quando é sabado ou domingo
 * Informa se a data ou dia atual e - true - um fim de semana (sab, dom) ou nao - false -.
 * Exemplo de uso:
 * 	itsWeekend('14-04-2019') -> Data Especifica
 * 		Retorno -> false
 * 	itsWeekend() -> Dia atual
 * 		Retorno -> True
 * Matheus Henrique
 * 14/04/2019
 */
 function itsWeekend($date = '')
 {
	//  date("N", mktime(0,0,0,$mes,$dia,$ano)
	if($date != '') {
		$diasemana = 0;			#Apenas por garantia =)
		
		// Faz um explode da data informada e cria um array na seguinte extrutura: data[0] = dia, data[1] = mes, data[2] = ano
		$data = explode('-', $date);
		
		// checkdate(mes, ano, dia) verifica se os valores informados formam uma data valida e retorna um true ou false
		isset($data[1]) ? $isDate = checkdate($data[1], $data[2], $data[0]) : $isDate = false;

		// Verifica se é uma data valida
		if($isDate){
			// A magica acontece aqui, ele monta a data em timestamp e utiliza o date(n) para retornar a data da semana
			$diasemana = date("N", mktime(0,0,0,$data[1],$data[2],$data[0]));		
		}else{
			// Caso ele nao consiga montar a primeira data, ele tenta montar novamente utilizando o padrao de data Brasileira
			$data = explode('/', $date);	//data[0] = dia, data[1] = mes, data[2] = ano
			$isDate = checkdate($data[1], $data[0], $data[2]);
			$diasemana = date("N", mktime(0,0,0,$data[1],$data[0],$data[2]));
		}
			// Sabado		// Domingo
		if($diasemana == 6 || $diasemana == 7)
			return true;
	}else{
		if(Date('d') == 6 || Date('d') == 7)
			return true;
	}
	return false;
 }


/**
 * RETIRA OS ACENTOS DAS PALAVRAS 
 */
function tiraAcento($texto)
{
	$trocarIsso = array('à','á','â','ã','ä','å','ç','è','é','ê','ë','ì','í','î','ï','ñ','ò','ó','ô','õ','ö','ù','ü','ú','ÿ','À','Á','Â','Ã','Ä','Å','Ç','È','É','Ê','Ë','Ì','Í','Î','Ï','Ñ','Ò','Ó','Ô','Õ','Ö','O','Ù','Ü','Ú','µm',"'");

	$porIsso = array('a','a','a','a','a','a','c','e','e','e','e','i','i','i','i','n','o','o','o','o','o','u','u','u','y','A','A','A','A','A','A','C','E','E','E','E','I','I','I','I','N','O','O','O','O','O','O','U','U','U','um',' ');
	$titletext = str_replace($trocarIsso, $porIsso, $texto);
	return $titletext;
}

/**
 * RETIRA OS CARACTERES ESPECIAIS -- Matheus Henrique 21/03/2019
 * CASO NECESSARIO UTILIZAR EM CONJUNTO COM tiraAcento() ;
 * Exemplo: tiraEspeciais('Camarão ou a 2º opção?, Apenas Qual o $?')
 * retorno 'Camarão-ou-a-2_-opção_,-Apenas-Qual-o __'
 */
function tiraEspeciais($texto, $espaco = false)
{
	$trocarIsso = array(' ','-','(',')',',',';',':','|','!','"','#','$','%','&','/','=','?','~','^','>','<','º','ª',"'");
	
	$porIsso 	= array('-','_','_','_','_','_','_','_','_','_','_','_','_','_','_','_','_','_','_','_','_','_','_','-');
	
	if($espaco)
		$porIsso 	= array(' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',' ');
	
	$titletext 	= str_replace($trocarIsso, $porIsso, $texto);
	return $titletext;
}

function tempodiff($data1,$hora1,$data2,$hora2)
{ 
	if(strlen($hora1)==5) $hora1 .= ':00';
	if(strlen($hora2)==5) $hora2 .= ':00';
	
	$i = explode(":",$hora1); 
	$j = explode("-",$data1); 
	$k = explode(":",$hora2); 
	$l = explode("-",$data2); 

	$tempo1 = mktime($i[0],$i[1],$i[2],$j[1],$j[2],$j[0]); 
	$tempo2 = mktime($k[0],$k[1],$k[2],$l[1],$l[2],$l[0]); 

	$calculo = (($tempo2 - $tempo1)/60)/60;
	$calculo2 = ceil((($tempo2 - $tempo1)/60));
	$tempo["hora_total"] = $calculo;  
	
//	$tempo["anos"] = ($calculo-($calculo%(365*24)))/(365*24); 
//	$tempo["meses"] = ($calculo-($calculo%(30*24)))/(30*24); 
//	$tempo["semanas"] = ($calculo-($calculo%(7*24)))/(7*24); 
	$tempo["dias"] = ($calculo-($calculo%24))/24; 
	$tempo["horas"] = $calculo%24;
	$tempo["segundos"] = $calculo * 60 * 60;  
//	$tempo["minutos"] = $calculo2;

	return $tempo; 
}

function ultimodia($mes,$ano)
{
	$dt = mktime(0,0,0,$mes+1,0,$ano);
	return date('d',$dt);
}

function dtultimodia($dt)
{
	$dt = mktime(0,0,0,month($dt)+1,0,year($dt));
	return date('Y-m-d',$dt);
}

function weekofyear($dia,$mes,$ano)
{
	$dt = mktime(0,0,0,$mes,$dia+1,$ano);
	$week = (int) date('W',$dt);
	
	$dtv = mktime(0,0,0,1,1,$ano);
	if(date('W',$dtv)=='53')
		return (++$week==54 ? 1 : $week);
	//elseif(date('W',$dtv)=='52')
	//	return (++$week==53 ? 1 : $week);
	else
		return $week;
}

function dow($data)
{
	return dayofweek(substr($data,8,2),substr($data,5,2),substr($data,0,4));
}

function dayofweek($dia,$mes,$ano)
{
	$dt = mktime(0,0,0,$mes,$dia,$ano);
	return date('w',$dt)+1;
}


function mesextenso($mes,$numcaracter=0,$lower = false)
{
	switch ($mes){
		case 1: $mes  = "JANEIRO"; break;
		case 2: $mes  = "FEVEREIRO"; break;
		case 3: $mes  = "MARÇO"; break;
		case 4: $mes  = "ABRIL"; break;
		case 5: $mes  = "MAIO"; break;
		case 6: $mes  = "JUNHO"; break;
		case 7: $mes  = "JULHO"; break;
		case 8: $mes  = "AGOSTO"; break;
		case 9: $mes  = "SETEMBRO"; break;
		case 10: $mes = "OUTUBRO"; break;
		case 11: $mes = "NOVEMBRO"; break;
		case 12: $mes = "DEZEMBRO"; break;
	}
	
	if($lower) $mes = strtolower($mes);
	
	return $numcaracter==0 ? $mes : substr($mes,0,$numcaracter);
}

function mesExtensoToNum($mesExtenso)
{
	$mes = strtoupper($mesExtenso); 
	
	switch ($mes){
		case "JANEIRO": return "01" ; break;
		case "FEVEREIRO": return "02" ; break;
		case "MARÇO": return "03" ; break;
		case "ABRIL": return "04" ; break;
		case "MAIO": return "05" ; break;
		case "JUNHO": return "06" ; break;
		case "JULHO": return "07" ; break;
		case "AGOSTO": return "08" ; break;
		case "SETEMBRO": return "09" ; break;
		case "OUTUBRO": return "10" ; break;
		case "NOVEMBRO": return "11" ; break;
		case "DEZEMBRO": return "12" ; break;
	}
}

function diaextenso($dia)
{	
	switch ($dia){
		case 1: $dia = "domingo"; break;
		case 2: $dia = "segunda-feira"; break;
		case 3: $dia = "terça-feira"; break;
		case 4: $dia = "quarta-feira"; break;
		case 5: $dia = "quinta-feira"; break;
		case 6: $dia = "sexta-feira"; break;
		case 7: $dia = "sábado"; break;
	}
	return $dia;
}

function getWeekDays($ano)
{	
	$di = mktime(0,0,0,1,1,$ano);
	$df = mktime(0,0,0,12,31,$ano);
	
	$asem = array();
	$sem = 1;
	while($di <= $df){
		$dow = dayofweek(date('d',$di),date('m',$di),date('Y',$di));
		
		$d1 = $di;
		$d2 = $di + 24 * 60 * 60 * (7 - $dow);

		$di = tscalc($d2,1);
				
		$asem[$sem++] = array($d1,$d2);

	}
	return $asem;
}

function tscalc($ts,$ndias)
{
	return mktime(date("H",$ts),date("i",$ts),date("s",$ts),date("n",$ts),date("j",$ts)+$ndias,date("Y",$ts));	
}

function dtcalc($dt,$ndias,$nmeses=0,$nanos=0,$formato='Y-m-d')
{
	$dt = explode("-",$dt);

	$ts = mktime(0,0,0,$dt[1],$dt[2],$dt[0]); 

	$mk = mktime(date("H",$ts),date("i",$ts),date("s",$ts),date("n",$ts)+$nmeses,date("j",$ts)+$ndias,date("Y",$ts)+$nanos);
	
	return date($formato,$mk);
}

function imgContents($imagem)
{
	// start buffering
	ob_start();
	// output jpeg (or any other chosen) format & quality
	imagepng($imagem);
	// capture output to string
	$contents = ob_get_contents();
	// end capture
	ob_end_clean();
	
	return $contents;
}

function horaToInt($strhora)
{	
	$int  = (float) substr($strhora,0,2);
	$int += (float) substr($strhora,3,2) / 60;
	$int += (float) substr($strhora,6,2) / 60 / 60;
	
	return $int;
}

function intToHora($inthora)
{
	return segToHora($inthora * 60 * 60);
}

function month($dt)
{	
	if(strpos($dt,'/')!==false)
		$dt = ctomsd($dt);

	return substr(str_replace('-','',$dt),4,2);
}

function year($dt)
{
	if(strpos($dt,'/')!==false)
		$dt = ctomsd($dt);

	return substr(str_replace('-','',$dt),0,4);
}

function horaToSeg($hora)
{
	if(strlen(trim($hora))==5)
		$hora =	trim($hora) . ':00';

	$hora = explode(':',$hora);
	$seg = $hora[2] + ($hora[1] * 60) + ($hora[0]*60 *60);

	return $seg;	
}

function segToHora($seg)
{
	if($seg < 0){
		$prefix = '-';
		$seg = str_replace('-','',$seg);
	}else
	$prefix = '';
	
	$h = str_pad( floor( $seg / 60 / 60) , 2 , '0', STR_PAD_LEFT);
	$m = str_pad( ($seg / 60) % 60 , 2 , '0', STR_PAD_LEFT);
	$s = str_pad( ($seg % 60) , 2 , '0', STR_PAD_LEFT); 

	return $prefix . $h.':'.$m.':'.$s;	
}

function segToIntHora($seg)
{
	if($seg < 0){
		$prefix = '-';
		$seg = str_replace('-','',$seg);
	}else
	$prefix = '';

	return $prefix . ($seg / 60 / 60);
}

/**
 * Calculos de Porcentagem %
 * Matheus Henrique 20/05/2019
 * 
 * 
  * Funçao de porcentagem: Quanto é X% de N?
  * exemplo: "Quanto é 17% de 127?"
  * porcentagem_xn(17, 127)
  */
function porcentagem_xn ( $porcentagem, $total )
{
	return ( $porcentagem / 100 ) * ($total == 0 ? 1 : $total);
}

/**
 * Função de porcentagem: N é X% de N
 * exemplo: "2.42 é X% de 22?".
 * porcentagem_nx(2.42, 22)
*/
function porcentagem_nx ( $parcial, $total )
{
    return ( $parcial * 100 ) / ($total == 0 ? 1 : $total);
}

/**
 * Função de porcentagem: N é N% de X
 * valor parcial e a porcentagem, qual o valor total?
 * porcentagem_nnx ( 2.42, 11 )
 */
function porcentagem_nnx ( $parcial, $porcentagem )
{
    return ( $parcial / $porcentagem ) * 100;
}


function monthyear($dt)
{
	return substr($dt,5,2).substr($dt,0,4);	
}


function alert($msg,$back=true,$url='')
{	
	$js = 'alert("'.$msg.'");' . 
	($back ? 'window.history.back();' : '') . 	
	(!empty($url) ? "window.location = '$url';" : '');

	echo View::js('',$js);
	if($back || !empty($url)) exit;
}

function swal($msg,$back=true,$url='')
{
	$js = '$(function(){ swal("'.$msg.'").then(function(){'. (
	$back ? 'window.history.back();' : '' ).(
	!empty($url) ? "window.location = '{$url}';" : '') .
	'}); });';

	//-- cria header js e css
	$header = implode("\n",$_SESSION['inc_js_files']) . "\n\n";
	$header .= implode("\n",$_SESSION['inc_css_files']);

	//-- libera memoria
	unset($_SESSION['inc_js_files'],$_SESSION['inc_css_files']);

	echo View::get_html('html.html', array("%HEAD%"      => $header,
		"%CON%"     => View::js('',$js),
		"%SYSNAME%" => SYSNAME,
		"%TIT%"     => SYSNAME,));
	if($back || !empty($url)) exit;
}

function swalConfirm($pergunta, $texto="", $urlAcaoPositiva, $urlAcaoNegativa, $icon = 'warning')
{
	$js = '
	swal({
		title: "'.$pergunta.'",
		text: "'.$texto.'",
		icon: "'.$icon.'",
		buttons: true,
		dangerMode: true,
		})
		.then((ok) => {
			if (ok) {
				wl("'.$urlAcaoPositiva.'");
				} else {
					wl("'.$urlAcaoNegativa.'");
				}
			});';

	//-- cria header js e css
			$header = implode("\n",$_SESSION['inc_js_files']) . "\n\n";
			$header .= implode("\n",$_SESSION['inc_css_files']);

	//-- libera memoria
			unset($_SESSION['inc_js_files'],$_SESSION['inc_css_files']);

			echo View::get_html('html.html', array("%HEAD%"      => $header,
				"%CON%"     => View::js('',$js),
				"%SYSNAME%" => SYSNAME,
				"%TIT%"     => SYSNAME,));
	// if($back || !empty($url)) exit;
}

function confirm($msg)
{
	$confirmS = '&confirm=yes';
	$confirmN = '&confirm=no';
	$url = $_SERVER ['REQUEST_URI'];
	$js = "if (confirm('$msg') ){ 
		window.location = '$url' " . $confirmS . "
		} else{
			window.location = '$url' " . $confirmN . "
		}";
		echo View::js('',$js);
		exit;
}

function array_orderby()
{
	$args = func_get_args();
	$data = array_shift($args);
	foreach ($args as $n => $field) {
		if (is_string($field)) {
			$tmp = array();
			foreach ($data as $key => $row)
				$tmp[$key] = $row[$field];
			$args[$n] = $tmp;
		}
	}
	$args[] = &$data;
	call_user_func_array('array_multisort', $args);
	return array_pop($args);
}

function isGet()
{
	$args = func_get_args();
	foreach($args as $get){
		if(!isset($_GET[$get])){
			alert('Parâmetro GET não encontrado: '.$get,true);
			exit;
		}
	}
}

function verifPost($url='',$alert=true)
{
	$url = empty($url) ? '?p='.($_GET['p'] ?? 'home') : $url;
	if($_SERVER['REQUEST_METHOD']!='POST'){
		if($alert)
			alert('Sessão Expirada! Teremos que recomeçar.',empty($url),$url);
		else
			return false;
	}elseif(!$alert){
		return true;
	}
}

function verifSession($var,$url='',$alert=true)
{
	$url = empty($url) ? '?p='.($_GET['p'] ?? 'home') : $url;

	if(!isset($_SESSION[$var])){

		if($alert)
			alert('Sessão Expirada!',empty($url),$url);
		else
			return false;

	}else{
		return true;
	}
}

function arrToTable($arr,$cab=array(),$ptotal=array())
{
	$h = '<table border="1" class="table table-bordered table-condensed" style="border-collapse:collapse" cellpadding="3">';

	if(!empty($cab)){
		$h .= '<tr>';
		foreach($cab as $c){
			if(is_array($c))
				$h .= '<th '.(isset($c[1]) ? 'align="'.$c[1].'"' : '').' bgcolor="#99CCFF">'.$c[0].'</th>';
			else
				$h .= '<th bgcolor="#99CCFF">'.$c.'</th>';
		}
		$h .= '</tr>';
	}

	$corsim = 0;
	$style = array();
	$total = array();

	foreach($arr as $a){
		$h .= '<tr>';

		$x=0;
		foreach($a as $cpo){

			if(isset($cab[$x][3])) $style[] = 'mso-number-format:'. $cab[$x][3];
			if(isset($cab[$x][4])) $style[] = $cab[$x][4];

			$h .= '<td align="'.(!empty($cab) && isset($cab[$x][1]) ? $cab[$x][1] : 'left').'" ';
			$h .= !empty($style) ? ' style="'.implode(';',$style).'" ' : '';
			$h .= '>';

			if( isset($cab[$x]) && is_array($cab[$x]) && isset($cab[$x][2]))
				eval( '$h .= '.str_replace('%VAR%','$cpo',$cab[$x][2]).';');
			else
				$h .= $cpo;

			$h .= '</td>';


			if(in_array($x,$ptotal)){
				if(!isset($total[$x])) $total[$x]=0;	
				$total[$x]+=$cpo;
			}else
			$total[$x] = '';

			$x++;			
			$style = [];
		}
		$h .= '</tr>';
		$corsim++;
	}
//-- total
	if(!empty($ptotal) && !empty($total)){
		$h .= '<tr style="background:#efefef">';
		$colspan=0;
		$label = true;
		for($x=0;$x<sizeof($total);$x++){

			if($total[$x]<>''){
				if($colspan>0){
					$h .= '<td colspan="'.$colspan.'">'.($label ? 'Total:' : '&nbsp;').'</td>';
					$label = false;
					$colspan=-1;
				}

				$h .= '<td align="'.(!empty($cab) ? $cab[$x][1] : 'left').'" ' .
				(!empty($style) ? ' style="'.implode(';',$style).'" ' : '') . '>';

				if( isset($cab[$x]) && is_array($cab[$x]) && isset($cab[$x][2]))
					eval( '$h .= '.str_replace('%VAR%','$total[$x]',$cab[$x][2]).';');
				else
					$h .= $total[$x];

				$h .='</td>';
			}
			$colspan++;
		}
		$h .= '</tr>';
	}
	$h .= '</table>';
	return $h;
}

function return_bytes($val)
{
	$val = trim($val);
	$last = strtolower(substr($val, -1));
	if($last == 'g')
		$val = (int) $val*1024*1024*1024;
	if($last == 'm')
		$val = (int) $val*1024*1024;
	if($last == 'k')
		$val = (int) $val*1024;
	return $val;
}

function formatBytes($bytes, $precision = 2) { 
    $units = array('B', 'KB', 'MB', 'GB', 'TB'); 

    $bytes = max($bytes, 0); 
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
    $pow = min($pow, count($units) - 1); 

    // Uncomment one of the following alternatives
    $bytes /= pow(1024, $pow);
    // $bytes /= (1 << (10 * $pow)); 

    return round($bytes, $precision) . ' ' . $units[$pow]; 
} 

function vtxt($string)
{  
	$vtext='';
	for($i=0;$i<strlen($string);$i++)
		$vtext .= substr($string,$i,1)."<br />";   
	return $vtext; 
}

function getInt($str)
{
	preg_match("/([0-9]+[\.,]?)+/",$str,$matches);
	return $matches[0];
}

function xml2array($arquivo,$type='file')
{
	if($type=='url'){
		$curl_handle=curl_init();
		curl_setopt($curl_handle, CURLOPT_URL,$arquivo);
		curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
		curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl_handle, CURLOPT_USERAGENT, 'Your application name');

		$arq = curl_exec($curl_handle);

		if(curl_errno($curl_handle)){
			echo 'Curl error: ' . curl_error($curl_handle);
		}

		curl_close($curl_handle);			

		$arq = utf8_decode($arq);

		if(substr($arq,0,1)!='<') return $arq;


		try{	
			if(!@$xml = simplexml_load_string($arq))
				throw new Exception('erro');
		}catch(Exception $e){
			return $arq;	
		}

	}elseif($type=='file'){
		try{
			if(! @$xml = simplexml_load_file($arquivo))
				throw new Exception('erro');
		}catch(Exception $e){
			throw new Exception('Não foi possível carregar o arquivo xml');
			exit;
		}
	}elseif($type=='string'){
		try{	
			@$xml = simplexml_load_string($arquivo);
		}catch(Exception $e){
			throw new Exception('Não foi possível carregar o arquivo xml');
			exit;
		}
	}

	if($xml){
		$vals = array();
		$vals = recurseXML($xml);
		return $vals;
	}else{
		libxml_use_internal_errors(true);

		$er = '';
		foreach(libxml_get_errors() as $error)
			$er .= $error->message . '<br>';

		return $er;
	}
}

function recurseXML($xml)
{
	$arr = array();
	foreach ($xml->children() as $r){
		$t = array();
		$atr = $r->attributes();
		if($atr->Status=='Hidden') continue;        
		if(count($r->children()) == 0)
			$arr[$r->getName()] = utf8_decode(strval($r));
		else
			$arr[$r->getName()][] = recurseXML($r);
	}
	return $arr;
}

function isMobile()
{
	$useragent=$_SERVER['HTTP_USER_AGENT'];
	return (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4)));
}

function mergeArrays()
{
	$result=array();
	$params=func_get_args();
	if ($params) foreach ($params as $param) {
		foreach ($param as $v) $result[]=$v;
	}
	$result=array_unique($result);
	return $result;
}

function isEmail($e)
{
	if(strpos($e,".interno")!==false) return false;

	return filter_var($e, FILTER_VALIDATE_EMAIL);
}

function array_map_recursive($function, $arr)
{
	$result = array();
	foreach ($arr as $key => $val)
		$result[$key] = (is_array($val) ? array_map_recursive($function, $val) : $function($val));
	
	return $result;
}

function portal_gethost()
{
	$_SESSION['usr_host'] = gethostbyaddr($_SERVER['REMOTE_ADDR']);
	return $_SESSION['usr_host'];
}

function write($file,$content)
{
	$fp = fopen($file, 'a');
	fwrite($fp, $content);
	fclose($fp);
}

function utf8_converter($array)
{
	array_walk_recursive($array, function(&$item, $key){
		if(!mb_detect_encoding($item, 'utf-8', true)){
			$item = utf8_encode($item);
		}
	});
	return $array;
}

/**
 * function sortArrayByKey(&$array,$key,$string = false,$asc = true)
 * Ordena array por uma chave
 * sortArrayByKey($seuArray,"name",true); //Por uma string (ordem ascendente)
 * sortArrayByKey($seuArray,"name",true,false); //Por uma string (ordem descendente)
 * sortArrayByKey($seuArray,"id"); //Por numero sort (ordem ascendente)
 * sortArrayByKey($seuArray,"count",false,false); //Por numero sort (ordem descendente)
 * Matheus Henrique 31/05/2019
 */
function sortArrayByKey(&$array,$key,$string = false,$asc = true){
    if($string){
        usort($array,function ($a, $b) use(&$key,&$asc)
        {
            if($asc)    return strcmp(strtolower($a{$key}), strtolower($b{$key}));
            else        return strcmp(strtolower($b{$key}), strtolower($a{$key}));
        });
    }else{
        usort($array,function ($a, $b) use(&$key,&$asc)
        {
            if($a[$key] == $b{$key}){return 0;}
            if($asc) return ($a{$key} < $b{$key}) ? -1 : 1;
            else     return ($a{$key} > $b{$key}) ? -1 : 1;

        });
    }
}


function formatField($val, $mascara)
{
	$maskared = '';
	$k = 0;
	for($i = 0; $i<=strlen($mascara)-1; $i++)
	{
		if($mascara[$i] == '#')
		{
			if(isset($val[$k]))
				$maskared .= $val[$k++];
		}
		else
		{
			if(isset($mascara[$i]))
				$maskared .= $mascara[$i];
		}
	}
	return $maskared;
}

/**
 * Funcao para realizar o bloqueio de acesso apartir do navegador Internet Explorer, sempre redirecionando para a home.
 * -> Sugestao de uso: if($_GET['opc']=='inclui'){bloqueiaIE(); #code...}
 * Analista: Matheus Henrique Rodrigues
 * Data: 01/08/2018
 */
function bloqueiaIE()
{
	$navegador_usado = $_SERVER['HTTP_USER_AGENT'];
	if(strrpos($navegador_usado, 'MSIE') || strrpos($navegador_usado, 'Trident')){
		alert("A versão do seu navegador não é compatível com os recursos. Por favor utilize Google Chrome ou Mozilla Firefox",null,'?p=home');
	}
}

/**
 * Função que retorna um array com valor sem reptição de dados 
 * baseado na $key [coluna] informada
 */
function super_unique($array,$key)
{
	$temp_array = [];
	foreach ($array as &$v) {
		if (!isset($temp_array[$v[$key]]))
			$temp_array[$v[$key]] =& $v;
	}
	$array = array_values($temp_array);
	return $array;
}

function toVal($val)
{
	return str_replace(",",".",str_replace(".","" ,$val));
}

/**
 * Função para verificar se o o sistema esta rodando no ambiente de produção
 */
function isProducao()
{
	$producao = ['https://onservices.com.br/', 'https://www.onservices.com.br/','https://api.onservices.com.br/'];

	if (in_array(DIRPAGE, $producao)) {
		return true;
	}
	return false;
}

/**
 * Função para abstrair o retorno de JSON com header correto
 * Caso precise utiliza a função array_map_recursive basta informar o segundo parametro como true
 */
function arrToJson(Array $arr, $recursive = false)
{
	ob_end_clean();// limpa todo o "lixo" de saida
	if ($recursive) {
		$arr = array_map_recursive('utf8_encode', $arr);
	}else{
		$arr = array_map('utf8_encode', $arr);
	}

	header('Content-Type: application/json');
	echo json_encode($arr);
	ob_end_flush(); // desativa o buffer de saida
}

function getPost($array = false){
	if($array){
		return json_decode(file_get_contents('php://input'),true);
	}
	return file_get_contents('php://input');
}

/**
 * Exibe todas as configuracoes padroes do sistema
 */
function configSYS(){
	ver('SYSNAME -> ' . SYSNAME,0);
	ver('SYSVERSION -> ' . SYSVERSION,0);
	ver('DEBUG -> ' . DEBUG,0);
	ver('SISTEMA_PROPRIETARIO_NOME -> ' . SISTEMA_PROPRIETARIO_NOME,0);
	ver('SISTEMA_PROPRIETARIO_EMAIL -> ' . SISTEMA_PROPRIETARIO_EMAIL,0);
	ver('DIRPAGE -> ' . DIRPAGE,0);
	ver('DIRREQ -> ' . DIRREQ,0);
	ver('IMGCORE -> ' . IMGCORE,0);
	ver('IMGAPP -> ' . IMGAPP,0);
	ver('PUBLICO -> ' . PUBLICO,0);
	ver('AJAX -> ' . AJAX,0);
	ver('MYSQL_DBNAME -> ' . MYSQL_DBNAME,0);
	ver('A_STRCONN -> ' . A_STRCONN,0);
	ver('SMTP_SERVER -> ' . SMTP_SERVER,0);
	ver('SMTP_PORT -> ' . SMTP_PORT,0);
	ver('SMTP_ACCOUNT -> ' . SMTP_ACCOUNT,0);
	ver('SMTP_PASS -> ' . SMTP_PASS,0);
	ver('DS -> ' . DS,0);
	ver('FOLDERS -> ' . FOLDERS,0);
	ver('SUBFOLDERS -> ' . SUBFOLDERS,0);
	ver('MAX_FILE_SIZE -> ' . MAX_FILE_SIZE,0);
	ver('date_default_timezone_get -> ' . date_default_timezone_get(),0);
	ver($_SERVER);
}