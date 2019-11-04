<?php

class audproController extends controller{

    /**
     * Metodo Contruct
     * Funcao de contrucao do controller, tudo o que estiver aqui e compartilhado entre as outras funcoes
     * Matheus Henrique
     */

    /**
     * Apenas para espantar curiosos
     */
    public function index()
    {
        echo 'Não a nada aqui pra voce';
    }

    /**
     * Retorna todas as auditorias pendente de realizacao
     * Tambem verifica se ha alguma nao conformidade para os produto
     */
    public function retornaAllProd()
    {
        $tbl_cad_audit = new tbl_cad_audit; //Cadastro de Auditorias 
        $tbl_nconform = new tbl_nconform; //Cadastro de Nao conformidades

        //Buscas as nao conformidades
        $nconform = $tbl_nconform->getall('id_qua115', '(str_status = "" or str_status is null) and dat_data = "' . Date('Y-m-d') . '"', null, 'id_qua115');

        //Busca todos os produtos
        $dado = $tbl_cad_audit->getall("*");
        $nc = [];

        //Monta um array com as chaves sendo o id_qua115 das nao conformidades
        foreach ($nconform as $key => $value)
            $nc[$value['id_qua115']] = [];

        //Atribui nao conformidade
        foreach ($dado as $key => $value){           
            $dado[$key]['naoConform'] = (array_key_exists($value['id'], $nc) ? 'S' : 'N');
            $dado[$key]['str_descprod'] = utf8_decode($value['str_descprod']);
        }

        $tbl_cad_audit->__destruct();
        $tbl_nconform->__destruct();

        arrToJson($dado, true);
    }

    /**
     * Retorna os padroes dos produtos a serem auditados, requer o numero do cadastro da auditoria
     */
    public function retornaDetalhesProd()
    {
        // $detalhes = new tbl_cad_padrao;
        $detalhes = new tbl_cad_padrao;

        $dado = $detalhes->getall('*', 'id_qua115 = ' . $_GET['id'] . ' and id NOT IN(SELECT id_padrao FROM tbl_audit_result where dat_data = "'.Date('Y-m-d').'")') ;
        foreach ($dado as $key => $value) {
            $dado[$key]['str_caracteristica'] = utf8_decode($value['str_caracteristica']);
        }
        $detalhes->__destruct();
        
        arrToJson($dado, true);
    }

    /**
     * Funcao marota
     * aqui a magica acontece, recebe todo o post e processa
     * Aqui salva os dados da auditoria, os dados dos resultados e das nao conformidades
     * Se voce tem amor a sua vida, nao faca nada aqui!
     * Distribui as cargas para cada funcao espeficica
     */
    public function setDetalhesProd()
    {
        //Busca os campos do $_POST
        $dados = getPost(true);

        //Separa os dados em suas devidas variaveis
        $audtArray = $dados['prod_array'];
        $semAudit = $dados['naoAudit'];
        $lote = $dados['num_lote'];
        $img = $dados['img_base64'];
        $user = $dados['dados_user'];
        $prod = $dados['prod'];
        
        //Trata falhas de processamento;
        $falha = false;

        if(empty($audtArray)){
            $trab[] = ['status' => 'array Vazio'];
            $falha = true;
        }elseif(!$semAudit['naoAuditou']){
            $trab[] = ['status' => 'array Completo'];

            //Processa os dados da auditoria
            $id = $this->procAudit($lote, $img, $prod, $user);

            //Processa os dados do Resultado
            if($id){
                $naoAudit = $this->procResult($audtArray, $user, $id);
                $retorno = ['detalProd' => $naoAudit, 'imagem' => $img, 'scannedData' => $lote, 'infoProd' => $prod];
                arrToJson($retorno, true);
            }else{
                $trab[] = ['status' => 'falha no procAudit'];
                $falha = true;
            }
        }

        if($semAudit['naoAuditou']){
            $this->naoAuditou($prod, $user, $semAudit);
            $retorno = ['Sucesso', 'Nao foi possivel auditar realizado com sucesso'];
            // arrToJson($retorno);
        }

        //Caso tenha alguma falha de processamento, retorna esse json (Espero nunca ter que cair nesta condicao)
        if($falha)
            arrToJson($trab);
    }

    /**
    * Processa os dados da auditoria
    */
    private function procAudit($lote, $img, $prod, $user)
    {
        // $audit = new Qua115_audit;
        $audit = new tbl_audit;

        //Verifica se ja existe esta auditoria em banco
        $r = $audit->getrow('id', "str_numserie = '{$lote['num_serie']}' and str_prod = '{$prod['str_codpro']}' and dat_data = '" . Date('Y-m-d') . "'" );
        
        //Se existe autaliza a hora e data, continua
        if(isset($r['id'])){
            $audit = new tbl_audit($r['id']);
            
            $d = null;            
            foreach ($audit->dados_result as $key => $value){ //Pega os valores atuais do banco para gerar um novo hash
                    if($key == 'str_hash') continue;
                        $d .= trim($value);
            }

            $audit->dat_data = Date('Y-m-d');
            $audit->str_hora = Date('H:i');
            $audit->str_auditor = $user['str_login'];

            foreach ($audit->dados as $key => $value) //Pega os novos dados inseridos para continuar na geracao de um novo hash
                        $d .= trim($value);

            $audit->str_hash = md5($d);

            $audit->update();
            return $r['id'];
        //Caso naoo exista, salva os dados necessários
        }else{
            $audit->str_prod = $prod['str_codpro'];
            $audit->str_descprod = trim(utf8_decode($prod['str_descprod']));
            $audit->str_numserie = $lote['num_serie'];
            $audit->str_lote = $lote['num_lote'];
            $audit->id_qua115 = $prod['id'];
            $audit->dat_data = Date('Y-m-d');
            $audit->str_hora = Date('H:i');
            $audit->str_auditor = $user['str_login'];

            //Inicio Gera HASH
            $d = null;
            foreach ($audit->dados as $key => $value)
                        $d .= $value;

            $audit->str_hash = md5($d);
            //Fim Gera HASH

            $audit->insert();
            //Busca o ultimo ID inserido
            $id = $audit->insert_id();

            //Magica de salvar arquivos em disco
            $caminho = $this->salvaArquivos('tbl_audit', $id, 'blb_fotoproduto', $img, "Auditoria-{$prod['str_codpro']}-".Date('d-m-Y').'.jpeg');
            //Instancia novamente para salvar o caminho da imagem
            $q115 = new tbl_audit($id);
            $q115->blb_fotoproduto = $caminho;
            $q115->update();

            return $id;
        }
    }

    private function naoAuditou($prod, $user, $motivo)
    {
        $audit = new tbl_audit;

        $audit->str_prod = $prod['str_codpro'];
        $audit->str_descprod = utf8_decode($prod['str_descprod']);
        $audit->dat_data = Date('Y-m-d');
        $audit->str_hora = Date('H:i');
        $audit->id_qua115 = $prod['id'];
        $audit->str_auditor = $user['str_login'];
        $audit->str_naoauditado = $motivo['motivo'] != '3' ? $motivo['motivo'] : $motivo['outros'];
        
        //Inicio Gera HASH
        $d = null;
        foreach ($audit->dados as $key => $value)
                    $d .= trim($value);
        $audit->str_hash = md5($d);
        //Fim Gera HASH

        $audit->insert();
    }

    /**
     * Aqui a magica de salvar arquivos
     * Rebece a string do base64 e cria um arquivo no servidor, salva seu caminho no campo para buscar o arquivo.
     * @rotina = Rotina principal Ex: tbl_audit
     * @id = Numero de identificacao Ex: 22
     * @campo = Campo ao qual pertence o arquivo Ex: blb_arquivo
     * @arquivo = O arquivo em base64 Ex: Vm9jZSBlIGN1cmlvc28=
     * @nome = nome a ser salvo o arquivo Ex: Arquivo.jpg
    */
    private function salvaArquivos($rotina, $id, $campo, $arquivo, $nome = '')
    {
        if(empty($arquivo)) return false;

        $arquivo = str_replace('data:image/jpeg;base64,', '', $arquivo['imagem']); //Remove as instrucoes desnecessarias do arquivo

        //Caminho onde ira salvar os aquivo
        $dir_tmp = "arq/anexos/$rotina/$id/$campo";
        //Nome do arquivo a ser salvo
        if($nome == '')
            $nome = "$rotina-".Date('d-m-Y').'.jpeg';
        //Caso nao exista o caminho, cria
        if(!is_dir($dir_tmp))
            mkdir($dir_tmp, 0777, true);
        
        //Aqui o Harry Houdini perde na magica, esta funcao insere o conteudo de uma string em um caminho que lhe e informado
        file_put_contents("$dir_tmp/$nome", base64_decode($arquivo));
        return "$dir_tmp/$nome";
    }

    /**
     * procResult
     * Processa o resultado das caracteristicas recebidas
     */
    private function procResult($audtArray, $user, $id)
    {
        $naoAudit = [];
        try {
            foreach ($audtArray as $value) {
                //Se nao estiver marcado nao foi auditado
                if($value['isChecked'] != 'true'){
                    // if($value['isChecked'] == '')
                    $value['isChecked'] = 0;
                    $value['str_caracteristica'] = utf8_decode($value['str_caracteristica']);
                    $naoAudit[] = $value;
                    continue;
                }
    
                $q15 = new tbl_audit_result;
                $q15->str_filial = $_GET['emp'];
                $q15->dat_data = Date('Y-m-d');
                $q15->str_hora = Date('H:i');
                $q15->id_qua115 = $value['id_qua115'];
                $q15->str_inspecao = $value['str_inspecao'];
                $q15->id_qua115_audit = $id;
                $q15->id_padrao = $value['id'];
                $q15->str_pc = $value['str_pc'];
                $q15->str_op = $value['str_op'];
                $q15->str_caracteristica = utf8_decode($value['str_caracteristica']);
                $q15->str_auditor = $user['str_login'];
                
                //Para inspecoes visuais
                if($value['str_inspecao'] == "1"){
                    if(isset($value['str_aprovado'])){
                        $q15->str_aprovado = $value['str_aprovado'];
                        $value['str_aprovado'] == 'N' ? $this->geraNaoConform($value, $user['str_login'], $id) : null;
                    }else{
                        $q15->str_aprovado = 'N';
                        $this->geraNaoConform($value, $user['str_login'], $id);
                    }
                }
                else{
                    //Caso Nao seja uma inspecao visual
                    $q15->flo_min = $value['flo_min'];
                    $q15->flo_max = $value['flo_max'];
                    $q15->flo_obtido = $value['flo_obtido'];
    
                    //Caso o valor obtido esteja entre os valores minimos e maximos, esta aprovado
                    if(($value['flo_obtido'] >= $value['flo_min']) && ($value['flo_obtido'] <= $value['flo_max']))
                        $q15->str_aprovado = 'S';
                    else{
                        $q15->str_aprovado = 'N';
                        $this->geraNaoConform($value, $user['str_login'], $id);
                    }
                }

                //Inicio Gera HASH
                $d = null;
                foreach ($q15->dados as $campo => $valor)
                            $d .= trim($valor);
                $q15->str_hash = md5($d);
                //Fim gera HASH

                $q15->insert();
            }
            return $naoAudit;
        } catch (\Throwable $th) {
            ver($th);
            return false;
        }        
    }

    /**
     * Salva as nao conformidades pegas na auditoria
     */
    private function geraNaoConform($value, $login, $id_audit)
    {
        $q115 = new tbl_nconform;
        $q115->id_qua115_audit = $id_audit;
        $q115->str_pc = $value['str_pc'];
        $q115->str_op = $value['str_op'];
        $q115->id_qua115 = $value['id_qua115'];
        $q115->id_padrao = $value['id'];
        $q115->dat_data = Date('Y-m-d');
        $q115->flo_min = $value['flo_min'];
        $q115->flo_max = $value['flo_max'];
        $q115->flo_obtido = $value['flo_obtido'];
        $q115->str_inspecao = $value['str_inspecao'];
        $q115->insert();
    }

    /**
     * public function procNaoConform()
     * Processa as nao conformidades informadas pelo auditor 
     * Recebe diretamente os dados do sistema
     * Matheus Henrique
     * 09/10/2019
     */
    public function procNaoConform()
    {
        $dados = getPost(true);

        if(empty($dados))   echo json_encode(['erro'=> 'procNaoConform vazio qua115controllerX260']);

        $naoInform = [];
        foreach ($dados as $key => $value) {

            //Se nao estiver marcado nao foi informado
            if($value['isChecked'] != 'true') continue;

            $qnc = new tbl_nconform($value['id_nconform']);
            $qnc->str_disposicao = utf8_decode($value['str_disposicao']);
            $qnc->str_responsavel = utf8_decode($value['str_responsavel']);
            $qnc->str_solucao = utf8_decode($value['str_solucao']);
            $qnc->dat_prazo = $value['dat_prazo'];
            $qnc->str_status = 'R';
            
            $qnc->blb_arquivo = $this->salvaArquivos('tbl_nconform', $value['id_nconform'], 'blb_arquivo', $value['imagem'], "NaoConforme-".trim($value['str_codpro'])."-".Date('d-m-Y').'.jpeg');
            
            //Gera o HASH das informacoes, so vai gerar os mesmos hash se nenhuma informacao for alterada.
            $d = null;
            foreach ($qnc->dados as $campo => $valor)
                        $d .= trim($value);

            $qnc->str_hash = md5($d);
            //Fim gera HASH

            $qnc->update();
        }
        echo arrToJson(['sucesso'=>'procNaoConform teve seus dados Processados com sucesso']);
        return true;
    }

    /**
    * public function retornaNaoConformidade()
    * Esta funcao tem duas funcoes, pode retornar todas as auditorias com nao conformidades pendentes ou retorna as nao conformidades de uma auditoria especifica
    * Caso tenha o parametro id_qua115 no get a mesma ira retornar os detalhes da nao conformidade da auditoria especifica
    * Caso o get esteja limpo, retorna as nao conformidades pendente de resposta
    * Matheus Henrique 
    * 09/10/2019
    */
    public function retornaNaoConformidade()
    {
        if(isset($_GET['id_qua115'])){
            $sql = "SELECT tbl_nconform.id AS 'id_nconform', tbl_nconform.str_disposicao, tbl_nconform.str_solucao, tbl_nconform.str_responsavel, tbl_nconform.dat_prazo, tbl_nconform.str_inspecao, tbl_nconform.flo_obtido,
            tbl_cad_padrao.id AS 'id_padrao', tbl_cad_padrao.str_caracteristica, tbl_cad_padrao.flo_min, tbl_cad_padrao.flo_max, tbl_cad_padrao.str_op, tbl_cad_padrao.str_ccritica, tbl_cad_padrao.str_pc,
            tbl_cad_audit.str_codcli, tbl_cad_audit.str_cliente, tbl_cad_audit.str_codpro, trim(tbl_cad_audit.str_descprod) as 'str_descprod', tbl_cad_audit.str_planta, tbl_cad_audit.id AS 'id_qua115'
            FROM tbl_nconform
            INNER JOIN tbl_cad_padrao ON tbl_nconform.id_padrao = tbl_cad_padrao.id
            INNER JOIN tbl_cad_audit ON tbl_nconform.id_qua115 = tbl_cad_audit.id
            WHERE (str_status = '' OR str_status IS NULL) AND tbl_nconform.id_qua115 = " . $_GET['id_qua115'];
        }else{
            $sql = "SELECT * FROM tbl_nconform INNER JOIN tbl_cad_padrao ON tbl_nconform.id_padrao = tbl_cad_padrao.id INNER JOIN tbl_cad_audit ON tbl_nconform.id_qua115 = tbl_cad_audit.id WHERE tbl_nconform.str_status != 'R'";
        }

        $q15 = new tbl_audit;
        $dados = $q15->execQuery($sql, 'all');
        $q15->__destruct();

        arrToJson($dados, true);
    }

    /**
     * public function auditoriasRealizadas()
     * Esta funcao possui duas funcoes, retornar as ultimas auditorias e retornar o resultado completo de uma auditoria especifica.
     * Caso nao tenha nenhum parametro no get, ela retorna todas as ultimas 10 auditorias realizadas, caso tenha o parametro id_qua115 no get, ela busca o resultado da mesma
     * Matheus Henrique 
     * 11/10/2019
     */
    public function auditoriasRealizadas()
    {
        $q15 = new tbl_audit;
        $q15->setDebug(1);
        if(isset($_GET['id_qua115'])){
            $audit = "SELECT * FROM tbl_audit where id = {$_GET['id_qua115']}";
            $audit = $q15->execQuery($audit, 'row');

            //Caso tenha o caminho do arquivo salvo, verifica se e um caminho valido, se for converte em base64
            if(!empty($audit['blb_fotoproduto']) && is_file($audit['blb_fotoproduto']))
                $audit['blb_fotoproduto'] = base64_encode(file_get_contents($audit['blb_fotoproduto']));
            
            // $result = "SELECT * FROM tbl_audit_result WHERE id_qua115 = {$_GET['id_qua115']} AND dat_data = '{$_GET['dat_data']}'";
            $result = "SELECT tbl_nconform.id,  tbl_nconform.id_qua115, tbl_nconform.str_disposicao,  tbl_nconform.str_solucao,  tbl_nconform.str_responsavel,  tbl_nconform.dat_prazo,  tbl_nconform.str_status,  tbl_nconform.id_rac,  tbl_nconform.blb_arquivo,  tbl_nconform.id_qua115_audit, tbl_audit_result.id, tbl_audit_result.str_caracteristica, tbl_audit_result.flo_obtido, tbl_audit_result.flo_min, tbl_audit_result.flo_max, tbl_audit_result.dat_data, tbl_audit_result.str_auditor, tbl_audit_result.str_hora, tbl_audit_result.id_qua115, tbl_audit_result.id_padrao, tbl_audit_result.id_qua115_audit, tbl_audit_result.str_aprovado, tbl_audit_result.str_pc, tbl_audit_result.str_inspecao, tbl_audit_result.str_op FROM tbl_audit_result left JOIN tbl_nconform on tbl_audit_result.id_padrao = tbl_nconform.id_padrao WHERE tbl_audit_result.id_qua115_audit = {$_GET['id_qua115']}";

            $result = $q15->execQuery($result, 'all');

            foreach ($result as $key => $value) {
                if(!empty($value['blb_arquivo']) && is_file($value['blb_arquivo']))
                $result[$key]['blb_arquivo'] = base64_encode(file_get_contents($value['blb_arquivo']));
            }

            $cadast = "SELECT * FROM tbl_audit WHERE id = " . $audit['id_qua115'];
            $cadast = $q15->execQuery($cadast, 'row');
            
            $dados= ['Cadastro' => $cadast, 'audit' => $audit, 'result' => $result];
            $q15->__destruct();
            arrToJson($dados, true);
        }
        else{
            $audit = $q15->getall('*', false, 'dat_data desc', null, '10');
            foreach ($audit as $key => $value) {
                $audit[$key]['str_descprod'] = utf8_decode($value['str_descprod']);
            }
            arrToJson($audit, true);
            $q15->__destruct();
        }
    }


    public function salvaAudit()
    {
        //Busca os campos do $_POST
        $dados = getPost(true);
        $produto = $dados['dadosProd'];
        $cliente = $dados['dadosCli'];
        $carac = $dados['caracProd'];

        $q115 = new tbl_cad_audit;

        foreach ($produto as $key => $value) {
            $q115->$key = $value;
        }
        $q115->str_planta = 1;

        foreach ($cliente as $key => $value) {
            $q115->$key = $value;
        }

        $q115->insert();
        $id_qua115 = $q115->insert_id();

        $q115 = new tbl_cad_padrao;
        foreach ($carac as $key => $value) {
            foreach ($value as $cam => $valor) {
                $q115->$cam = $valor;
            }
            $q115->str_ativo = 'S';
            $q115->id_qua115 = $id_qua115;
            $q115->insert();
        }
    }

    private function salvaTXT($string)
    {
        $txt = fopen('audprod.txt', 'a');
        fwrite($txt, $string);
        fclose($txt);
    }
}