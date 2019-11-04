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
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

-- Copiando dados para a tabela db_controller.sys005: 2 rows
/*!40000 ALTER TABLE `sys005` DISABLE KEYS */;
INSERT INTO `sys005` (`str_filial`, `id`, `str_nome`, `str_desc`, `str_retconsulta`, `str_filconsulta`, `str_formfiles`, `str_chavconsulta`, `str_expxls`, `str_checkable`, `str_db`, `str_modo`, `str_inclui`, `str_altera`, `str_visual`, `str_exclui`, `str_camposys`, `str_revisa`, `str_duplic`, `str_view`, `str_sqlview`, `str_bkpuni`) VALUES
	('', 1, 'sys005', 'Cadastro de Tabelas do Sistema', 'str_nome', '', 'N', 'str_nome', 'S', 'N', 'controller', 'C', 'S', 'S', 'S', 'S', 'S', 'N', NULL, NULL, '', 'S'),
	('', 2, 'sys008', 'Classes do Sistema', '', '', 'N', '', 'S', 'N', 'controller', 'C', 'S', 'S', 'S', 'S', 'S', NULL, NULL, NULL, '', 'S');
/*!40000 ALTER TABLE `sys005` ENABLE KEYS */;

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
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

-- Copiando dados para a tabela db_controller.sys008: 4 rows
/*!40000 ALTER TABLE `sys008` DISABLE KEYS */;
INSERT INTO `sys008` (`str_filial`, `id`, `str_nome`, `str_extend`, `str_camposys`, `namespace`) VALUES
	('', 1, 'Sys008', 'Model', 'S', NULL),
	('', 2, 'Sys008Controller', 'CadastroController', 'S', NULL),
	('', 4, 'Sys005Controller', 'CadastroController', 'S', NULL),
	('', 3, 'Sys005', 'Model', 'S', NULL);
/*!40000 ALTER TABLE `sys008` ENABLE KEYS */;

-- Copiando estrutura para tabela db_controller.tbl_user
DROP TABLE IF EXISTS `tbl_user`;
CREATE TABLE IF NOT EXISTS `tbl_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `str_nome` varchar(240) DEFAULT NULL,
  `str_login` varchar(50) DEFAULT NULL,
  `str_setor` varchar(50) DEFAULT NULL,
  `str_senha` varchar(512) DEFAULT NULL,
  `str_email` varchar(512) DEFAULT NULL,
  `str_auditor` varchar(1) DEFAULT NULL,
  `str_empresa` varchar(3) DEFAULT NULL,
  KEY `Index 1` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

-- Copiando dados para a tabela db_controller.tbl_user: ~0 rows (aproximadamente)
/*!40000 ALTER TABLE `tbl_user` DISABLE KEYS */;
INSERT INTO `tbl_user` (`id`, `str_nome`, `str_login`, `str_setor`, `str_senha`, `str_email`, `str_auditor`, `str_empresa`) VALUES
	(1, 'Administrador', 'admin', 'Todos', '21232f297a57a5a743894a0e4a801fc3', 'admin@admin', 'S', 'ADM');
/*!40000 ALTER TABLE `tbl_user` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
