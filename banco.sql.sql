-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 02-Maio-2026 às 19:40
-- Versão do servidor: 10.4.32-MariaDB
-- versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `gestao_familiar`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `casamentos`
--

CREATE TABLE `casamentos` (
  `id` int(11) NOT NULL,
  `id_pessoa1` int(11) NOT NULL,
  `id_pessoa2` int(11) NOT NULL,
  `data_casamento` date NOT NULL,
  `data_divorcio` date DEFAULT NULL,
  `status` enum('ativo','divorciado') DEFAULT 'ativo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `casamentos`
--

INSERT INTO `casamentos` (`id`, `id_pessoa1`, `id_pessoa2`, `data_casamento`, `data_divorcio`, `status`) VALUES
(1, 1, 2, '2001-02-07', NULL, 'ativo'),
(2, 4, 8, '2026-12-09', NULL, 'ativo');

-- --------------------------------------------------------

--
-- Estrutura da tabela `pessoas`
--

CREATE TABLE `pessoas` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `data_nascimento` date NOT NULL,
  `bi` varchar(20) DEFAULT NULL,
  `sexo` enum('M','F') NOT NULL,
  `id_pai` int(11) DEFAULT NULL,
  `id_mae` int(11) DEFAULT NULL,
  `id_conjuge` int(11) DEFAULT NULL,
  `data_casamento` date DEFAULT NULL,
  `apelido_familiar` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `pessoas`
--

INSERT INTO `pessoas` (`id`, `nome`, `data_nascimento`, `bi`, `sexo`, `id_pai`, `id_mae`, `id_conjuge`, `data_casamento`, `apelido_familiar`) VALUES
(1, 'Eugenio Andre Mateus', '1969-05-05', NULL, 'M', NULL, NULL, NULL, NULL, 'Familia Mateus'),
(2, 'Regina joao Nhica', '1973-10-08', NULL, 'F', NULL, NULL, NULL, NULL, 'Familia Mateus'),
(4, 'Meque Eugenio Mateus', '1999-10-03', NULL, 'M', 1, 2, NULL, NULL, 'Familia Mateus'),
(5, 'Engels Eugenio Mateus', '2003-12-06', NULL, 'M', 1, 2, NULL, NULL, 'Familia Mateus'),
(6, 'Delfin Eugenio Mateus', '2007-12-07', NULL, 'M', 1, 2, NULL, NULL, 'Familia Mateus'),
(7, 'Eunice Regina Joao Nhica', '2009-12-03', NULL, 'F', 1, 2, NULL, NULL, 'Familia Mateus'),
(8, 'Lucrecia Martins', '1998-07-01', NULL, 'F', NULL, NULL, NULL, NULL, 'Familia Martins'),
(9, 'Sakura Meque Marins Eugenio Mateus', '2026-12-04', NULL, 'F', 4, 8, NULL, NULL, 'Familia Mateus');

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `casamentos`
--
ALTER TABLE `casamentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_pessoa1` (`id_pessoa1`),
  ADD KEY `id_pessoa2` (`id_pessoa2`);

--
-- Índices para tabela `pessoas`
--
ALTER TABLE `pessoas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_pai` (`id_pai`),
  ADD KEY `fk_mae` (`id_mae`),
  ADD KEY `fk_conjuge` (`id_conjuge`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `casamentos`
--
ALTER TABLE `casamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `pessoas`
--
ALTER TABLE `pessoas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `casamentos`
--
ALTER TABLE `casamentos`
  ADD CONSTRAINT `casamentos_ibfk_1` FOREIGN KEY (`id_pessoa1`) REFERENCES `pessoas` (`id`),
  ADD CONSTRAINT `casamentos_ibfk_2` FOREIGN KEY (`id_pessoa2`) REFERENCES `pessoas` (`id`);

--
-- Limitadores para a tabela `pessoas`
--
ALTER TABLE `pessoas`
  ADD CONSTRAINT `fk_conjuge` FOREIGN KEY (`id_conjuge`) REFERENCES `pessoas` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_mae` FOREIGN KEY (`id_mae`) REFERENCES `pessoas` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_pai` FOREIGN KEY (`id_pai`) REFERENCES `pessoas` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
