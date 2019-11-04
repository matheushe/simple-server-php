-- --------------------------------------------------------
-- Servidor:                     localhost
-- Versão do servidor:           10.4.6-MariaDB - mariadb.org binary distribution
-- OS do Servidor:               Win64
-- HeidiSQL Versão:              10.2.0.5599
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


-- Copiando estrutura do banco de dados para db_controller
DROP DATABASE IF EXISTS `db_controller`;
CREATE DATABASE IF NOT EXISTS `db_controller` /*!40100 DEFAULT CHARACTER SET latin1 */;
USE `db_controller`;

-- Copiando estrutura para tabela db_controller.sys002
DROP TABLE IF EXISTS `sys002`;
CREATE TABLE IF NOT EXISTS `sys002` (
  `str_filial` varchar(3) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `str_desc` varchar(60) NOT NULL,
  `str_folder` varchar(1) NOT NULL,
  `id_pai` varchar(6) NOT NULL,
  `str_img` varchar(20) DEFAULT NULL,
  `str_link` varchar(50) DEFAULT NULL,
  `str_ordem` varchar(2) NOT NULL DEFAULT 'ZZ',
  `str_camposys` varchar(1) DEFAULT NULL,
  `str_open` varchar(1) DEFAULT NULL,
  `str_resp` varchar(40) NOT NULL,
  `str_manutencao` varchar(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `filial + link` (`str_filial`,`str_link`),
  KEY `id+str_filial` (`id`,`str_filial`)
) ENGINE=MyISAM AUTO_INCREMENT=1909 DEFAULT CHARSET=latin1 COMMENT='Cadastro de menus e links';

-- Copiando dados para a tabela db_controller.sys002: 0 rows
/*!40000 ALTER TABLE `sys002` DISABLE KEYS */;
/*!40000 ALTER TABLE `sys002` ENABLE KEYS */;

-- Copiando estrutura para tabela db_controller.sys005
DROP TABLE IF EXISTS `sys005`;
CREATE TABLE IF NOT EXISTS `sys005` (
  `str_filial` varchar(3) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `str_nome` varchar(64) NOT NULL,
  `str_desc` varchar(100) NOT NULL,
  `str_retconsulta` varchar(30) DEFAULT NULL,
  `str_filconsulta` varchar(200) DEFAULT NULL,
  `str_formfiles` varchar(1) DEFAULT NULL,
  `str_chavconsulta` varchar(30) DEFAULT NULL,
  `str_expxls` varchar(1) DEFAULT NULL,
  `str_checkable` varchar(1) DEFAULT NULL COMMENT 'Inclui uma coluna com checkbox para as linhas',
  `str_db` varchar(40) NOT NULL,
  `str_modo` varchar(1) NOT NULL,
  `str_inclui` varchar(1) DEFAULT NULL,
  `str_altera` varchar(1) DEFAULT NULL,
  `str_visual` varchar(1) DEFAULT NULL,
  `str_exclui` varchar(1) DEFAULT NULL,
  `str_camposys` varchar(1) DEFAULT NULL,
  `str_revisa` varchar(1) DEFAULT NULL,
  `str_duplic` varchar(1) DEFAULT NULL,
  `str_view` varchar(1) DEFAULT NULL,
  `str_sqlview` varchar(10000) DEFAULT NULL,
  `str_bkpuni` varchar(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `db+tabela` (`str_db`,`str_nome`),
  KEY `id+str_filial` (`id`,`str_filial`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COMMENT='Cadastro de Tabelas do Banco';

-- Copiando dados para a tabela db_controller.sys005: 2 rows
/*!40000 ALTER TABLE `sys005` DISABLE KEYS */;
INSERT INTO `sys005` (`str_filial`, `id`, `str_nome`, `str_desc`, `str_retconsulta`, `str_filconsulta`, `str_formfiles`, `str_chavconsulta`, `str_expxls`, `str_checkable`, `str_db`, `str_modo`, `str_inclui`, `str_altera`, `str_visual`, `str_exclui`, `str_camposys`, `str_revisa`, `str_duplic`, `str_view`, `str_sqlview`, `str_bkpuni`) VALUES
	('', 1, 'sys005', 'Cadastro de Tabelas do Sistema', 'str_nome', '', 'N', 'str_nome', 'S', 'N', 'portal', 'C', 'S', 'S', 'S', 'S', 'S', 'N', NULL, NULL, '', 'S'),
	('', 2, 'sys008', 'Classes do Sistema', '', '', 'N', '', 'S', 'N', 'portal', 'C', 'S', 'S', 'S', 'S', 'S', NULL, NULL, NULL, '', 'S');
/*!40000 ALTER TABLE `sys005` ENABLE KEYS */;

-- Copiando estrutura para tabela db_controller.sys006
DROP TABLE IF EXISTS `sys006`;
CREATE TABLE IF NOT EXISTS `sys006` (
  `str_filial` char(50) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `str_menu` varchar(45) DEFAULT NULL,
  `id_sys002` int(11) DEFAULT NULL,
  `id_usuario` int(11) NOT NULL,
  `str_nome` varchar(60) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `filial + id_sys002 + id_usuario` (`str_filial`,`id_sys002`,`id_usuario`),
  KEY `id+str_filial` (`id`,`str_filial`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Amarracao entre usuario e permissao de menus';

-- Copiando dados para a tabela db_controller.sys006: 0 rows
/*!40000 ALTER TABLE `sys006` DISABLE KEYS */;
/*!40000 ALTER TABLE `sys006` ENABLE KEYS */;

-- Copiando estrutura para tabela db_controller.sys008
DROP TABLE IF EXISTS `sys008`;
CREATE TABLE IF NOT EXISTS `sys008` (
  `str_filial` varchar(3) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `str_nome` varchar(60) NOT NULL,
  `str_extend` varchar(60) NOT NULL,
  `str_camposys` varchar(1) DEFAULT NULL,
  `namespace` varchar(60) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `str_nome` (`str_nome`),
  KEY `id+str_filial` (`id`,`str_filial`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 COMMENT='Cadastro de Externds das rotinas';

-- Copiando dados para a tabela db_controller.sys008: 4 rows
/*!40000 ALTER TABLE `sys008` DISABLE KEYS */;
INSERT INTO `sys008` (`str_filial`, `id`, `str_nome`, `str_extend`, `str_camposys`, `namespace`) VALUES
	('', 1, 'Sys008', 'Model', 'S', NULL),
	('', 2, 'Sys008Controller', 'CadastroController', 'S', NULL),
	('', 4, 'Sys005Controller', 'CadastroController', 'S', NULL),
	('', 3, 'Sys005', 'Model', 'S', NULL);
/*!40000 ALTER TABLE `sys008` ENABLE KEYS */;

-- Copiando estrutura para tabela db_controller.sys015
DROP TABLE IF EXISTS `sys015`;
CREATE TABLE IF NOT EXISTS `sys015` (
  `str_filial` varchar(3) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador da tabela sys015',
  `str_nome` varchar(50) NOT NULL,
  `str_abrev` varchar(3) DEFAULT NULL,
  `str_cnpj` varchar(15) NOT NULL,
  `str_fantasia` varchar(50) DEFAULT NULL,
  `str_filport` varchar(3) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `str_cnpj` (`str_cnpj`),
  KEY `id+str_filial` (`id`,`str_filial`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COMMENT='Cadastro de Empresas do Sistema';

-- Copiando dados para a tabela db_controller.sys015: 1 rows
/*!40000 ALTER TABLE `sys015` DISABLE KEYS */;
INSERT INTO `sys015` (`str_filial`, `id`, `str_nome`, `str_abrev`, `str_cnpj`, `str_fantasia`, `str_filport`) VALUES
	('ADM', 1, 'Administrador', 'ADM', '', 'Administrador do Sistema', '01');
/*!40000 ALTER TABLE `sys015` ENABLE KEYS */;

-- Copiando estrutura para tabela db_controller.sysnotificacao
DROP TABLE IF EXISTS `sysnotificacao`;
CREATE TABLE IF NOT EXISTS `sysnotificacao` (
  `str_filial` varchar(3) DEFAULT NULL COMMENT 'filial da tabela sysnotificacao',
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador da tabela sysnotificacao',
  `dat_inclusao` datetime NOT NULL,
  `dat_visualizacao` datetime DEFAULT NULL,
  `str_usrinclusao` varchar(50) NOT NULL,
  `str_setorvisual` varchar(50) DEFAULT NULL,
  `str_usrvisualiza` varchar(50) NOT NULL,
  `str_msg` varchar(120) NOT NULL,
  `str_url` varchar(200) DEFAULT NULL,
  `str_flag` varchar(2) DEFAULT '',
  `dat_validade` date NOT NULL,
  `str_titulo` varchar(50) DEFAULT NULL,
  `str_viewhome` varchar(1) DEFAULT NULL,
  `str_tipo` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Coletanea de notificacoes do sistemas';

-- Copiando dados para a tabela db_controller.sysnotificacao: 0 rows
/*!40000 ALTER TABLE `sysnotificacao` DISABLE KEYS */;
/*!40000 ALTER TABLE `sysnotificacao` ENABLE KEYS */;

-- Copiando estrutura para tabela db_controller.syspermissao
DROP TABLE IF EXISTS `syspermissao`;
CREATE TABLE IF NOT EXISTS `syspermissao` (
  `str_filial` varchar(3) DEFAULT NULL COMMENT 'filial da tabela sysacesso',
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador da tabela sysacesso',
  `str_grupo` varchar(64) NOT NULL,
  `str_login` varchar(30) NOT NULL,
  `str_loginc` varchar(40) NOT NULL,
  `dat_inc` date NOT NULL,
  `str_logalt` varchar(40) NOT NULL DEFAULT '',
  `dat_alt` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Cadastro de Grupos de permissoes do sistema';

-- Copiando dados para a tabela db_controller.syspermissao: 0 rows
/*!40000 ALTER TABLE `syspermissao` DISABLE KEYS */;
/*!40000 ALTER TABLE `syspermissao` ENABLE KEYS */;

-- Copiando estrutura para tabela db_controller.usuario
DROP TABLE IF EXISTS `usuario`;
CREATE TABLE IF NOT EXISTS `usuario` (
  `str_filial` varchar(3) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `str_login` varchar(40) NOT NULL,
  `str_senha` varchar(32) DEFAULT NULL,
  `str_nome` varchar(50) NOT NULL,
  `str_bloq` varchar(1) NOT NULL DEFAULT 'N',
  `str_ramal` varchar(20) NOT NULL,
  `str_cargo` varchar(30) DEFAULT NULL,
  `str_setor` varchar(40) DEFAULT NULL,
  `str_email` varchar(50) DEFAULT NULL,
  `str_matr` varchar(6) DEFAULT NULL,
  `str_cc` varchar(9) NOT NULL,
  `str_desccc` varchar(50) NOT NULL,
  `str_fax` varchar(20) NOT NULL,
  `str_emp` varchar(5) DEFAULT NULL,
  `int_cod_cracha` int(15) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `Filial + Login` (`str_filial`,`str_login`),
  UNIQUE KEY `Filial + Empresa + Matricula` (`str_filial`,`str_emp`,`str_matr`),
  KEY `id+str_filial` (`id`,`str_filial`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COMMENT='Cadastro de TODOS os usuarios do sistema';

-- Copiando dados para a tabela db_controller.usuario: 2 rows
/*!40000 ALTER TABLE `usuario` DISABLE KEYS */;
INSERT INTO `usuario` (`str_filial`, `id`, `str_login`, `str_senha`, `str_nome`, `str_bloq`, `str_ramal`, `str_cargo`, `str_setor`, `str_email`, `str_matr`, `str_cc`, `str_desccc`, `str_fax`, `str_emp`, `int_cod_cracha`) VALUES
	('ADM', 1, 'admin', '202cb962ac59075b964b07152d234b70', 'Administrador do Sistema', 'N', '', 'Administrador', 'TODOS', 'matheus_henriquealves@hotmail.com', '000001', '', '-', '', 'ADM', NULL),
	('ADM', 2, 'matheus', '202cb962ac59075b964b07152d234b70', 'Matheus Henrique Rodrigues', 'N', '', 'CEO', 'TODOS', 'matheus_henriquealves@hotmail.com', '000002', '', '-', '', 'ADM', NULL);
/*!40000 ALTER TABLE `usuario` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
