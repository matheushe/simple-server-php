<?php


class usuariocontroller extends controller{

    public function index()
    {
        echo 'USUARIO';
    }

    public function autenticaUser()
    {
        $dados = getPost(true);

        $user = $dados['str_login'];
        $senha = base64_decode($dados['str_senha']);

        $sucesso = [''];
    
        if(empty($user) || empty($senha)){
            $sucesso = ['erro' => true, 'msg' =>'usuario e/ou senha nao informados'];
        }

        $dados = $this->authBD($user, $senha);
        
        if(isset($dados['id'])){
            $sucesso = ['autenticado' => 'true', 'msg' =>'usuario autenticado', 'dados' => $dados];
        }
        else{
            $sucesso = ['sucesso' => false, 'msg' =>'usuario e/ou senha incorretos'];

        }
        
        arrToJson($sucesso, true);
    }

    private function authBD($user, $senha)
    {
        $sql = "SELECT id, str_nome, str_login, str_setor, str_email, str_auditor, str_empresa  FROM tbl_user WHERE str_login = '$user' and str_senha = '" . md5($senha) . "'";

        $usuario = new tbl_user;

        $usr = $usuario->execQuery($sql, 'row');

        //Se nao tiver uma foto, retorna uma padrao.
        // if(empty($usr['blb_foto']))
        $usr['blb_foto'] = file_get_contents(IMGCORE.'icons/foto.png');

        return $usr;
    }
}