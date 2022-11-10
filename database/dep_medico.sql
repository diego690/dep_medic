-- --------------------------------------------------------
-- Host:                         localhost
-- Versión del servidor:         8.0.21 - MySQL Community Server - GPL
-- SO del servidor:              Win64
-- HeidiSQL Versión:             11.0.0.5919
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Volcando estructura para tabla dep_medico.areas
CREATE TABLE IF NOT EXISTS `areas` (
  `id` varchar(36) NOT NULL,
  `name` varchar(50) NOT NULL,
  `campus` varchar(100) NOT NULL,
  `description` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Volcando datos para la tabla dep_medico.areas: 6 rows
/*!40000 ALTER TABLE `areas` DISABLE KEYS */;
INSERT INTO `areas` (`id`, `name`, `campus`, `description`) VALUES
	('2584c89d-f1a4-416a-8041-42bfb1dc1616', 'Enfermería', 'Central', NULL),
	('8838dd8e-99d2-4f87-8fbf-4d8e8952136b', 'Medicina', 'Central', NULL),
	('d3cd1d33-5932-4550-917c-0db1b2a6ee4a', 'Odontología', 'Central', NULL),
	('5f583780-d850-4f33-87de-14c099e0dbec', 'Enfermería', 'La María', NULL),
	('32e42172-64a2-4d7e-8173-cac3080b9afa', 'Medicina', 'La María', NULL),
	('46aae5c8-f4f3-4095-8359-87bffea2ee74', 'Odontología', 'La María', NULL);
/*!40000 ALTER TABLE `areas` ENABLE KEYS */;

-- Volcando estructura para tabla dep_medico.careers
CREATE TABLE IF NOT EXISTS `careers` (
  `id` varchar(36) NOT NULL,
  `faculty_id` varchar(36) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text,
  `visible` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Volcando datos para la tabla dep_medico.careers: 29 rows
/*!40000 ALTER TABLE `careers` DISABLE KEYS */;
INSERT INTO `careers` (`id`, `faculty_id`, `name`, `description`, `visible`, `created_at`, `updated_at`) VALUES
	('f55da3dc-bd78-11eb-ae48-0250e3456170', '1d779565-bbe2-11eb-8af8-0250e3456170', 'Ingeniería en Sistemas', '', 1, '2021-05-25 11:47:57', '2021-05-25 12:58:20'),
	('0bc59b80-bd79-11eb-ae48-0250e3456170', '1d779565-bbe2-11eb-8af8-0250e3456170', 'Ingeniería Ambiental', '', 1, '2021-05-25 11:48:35', NULL),
	('10988b89-bd79-11eb-ae48-0250e3456170', '1d779565-bbe2-11eb-8af8-0250e3456170', 'Electricidad', '', 1, '2021-05-25 11:48:43', NULL),
	('14998b41-bd79-11eb-ae48-0250e3456170', '1d779565-bbe2-11eb-8af8-0250e3456170', 'Telemática', '', 1, '2021-05-25 11:48:49', NULL),
	('18e2960e-bd79-11eb-ae48-0250e3456170', '1d779565-bbe2-11eb-8af8-0250e3456170', 'Mecánica', '', 1, '2021-05-25 11:48:57', NULL),
	('1b6e9b67-bd79-11eb-ae48-0250e3456170', '1d779565-bbe2-11eb-8af8-0250e3456170', 'Software', '', 1, '2021-05-25 11:49:01', NULL),
	('226b753e-bd79-11eb-ae48-0250e3456170', '9b95be6c-bbe2-11eb-8af8-0250e3456170', 'Enfermería', '', 1, '2021-05-25 11:49:13', NULL),
	('344ede1e-bd79-11eb-ae48-0250e3456170', '5c998772-bbe2-11eb-8af8-0250e3456170', 'Contabilidad y Auditoría', '', 1, '2021-05-25 11:49:43', NULL),
	('37d35a44-bd79-11eb-ae48-0250e3456170', '5c998772-bbe2-11eb-8af8-0250e3456170', 'Finanzas', '', 1, '2021-05-25 11:49:49', NULL),
	('3ea21849-bd79-11eb-ae48-0250e3456170', '5c998772-bbe2-11eb-8af8-0250e3456170', 'Gestión del Talento Humano', '', 1, '2021-05-25 11:50:00', NULL),
	('4401832e-bd79-11eb-ae48-0250e3456170', '5c998772-bbe2-11eb-8af8-0250e3456170', 'Administración Pública', '', 1, '2021-05-25 11:50:09', NULL),
	('53cda42a-bd79-11eb-ae48-0250e3456170', '5c998772-bbe2-11eb-8af8-0250e3456170', 'Administración de Empresas', '', 1, '2021-05-25 11:50:35', NULL),
	('5a176206-bd79-11eb-ae48-0250e3456170', '5c998772-bbe2-11eb-8af8-0250e3456170', 'Mercadotecnia', '', 1, '2021-05-25 11:50:46', NULL),
	('5ddc480b-bd79-11eb-ae48-0250e3456170', '5c998772-bbe2-11eb-8af8-0250e3456170', 'Economía', '', 1, '2021-05-25 11:50:52', NULL),
	('647c9ea8-bd79-11eb-ae48-0250e3456170', '6533aca6-bbe2-11eb-8af8-0250e3456170', 'Agronomía', '', 1, '2021-05-25 11:51:03', NULL),
	('69c4b564-bd79-11eb-ae48-0250e3456170', '6533aca6-bbe2-11eb-8af8-0250e3456170', 'Ingeniería Forestal', '', 1, '2021-05-25 11:51:12', NULL),
	('6f669e24-bd79-11eb-ae48-0250e3456170', '6533aca6-bbe2-11eb-8af8-0250e3456170', 'Ingeniería Agrícola', '', 1, '2021-05-25 11:51:22', NULL),
	('79367dc9-bd79-11eb-ae48-0250e3456170', '6533aca6-bbe2-11eb-8af8-0250e3456170', 'Acuicultura', '', 1, '2021-05-25 11:51:38', NULL),
	('7d83cd4f-bd79-11eb-ae48-0250e3456170', '6533aca6-bbe2-11eb-8af8-0250e3456170', 'Agropecuaria', '', 1, '2021-05-25 11:51:45', NULL),
	('82de1f3f-bd79-11eb-ae48-0250e3456170', '6533aca6-bbe2-11eb-8af8-0250e3456170', 'Agroecología', '', 1, '2021-05-25 11:51:54', NULL),
	('877887d3-bd79-11eb-ae48-0250e3456170', '6533aca6-bbe2-11eb-8af8-0250e3456170', 'Zootecnia', '', 1, '2021-05-25 11:52:02', NULL),
	('8bdacc1c-bd79-11eb-ae48-0250e3456170', '6533aca6-bbe2-11eb-8af8-0250e3456170', 'Biología', '', 1, '2021-05-25 11:52:09', NULL),
	('90717f4a-bd79-11eb-ae48-0250e3456170', '71acfadc-bbe2-11eb-8af8-0250e3456170', 'Agroindustria', '', 1, '2021-05-25 11:52:17', NULL),
	('9327f4b9-bd79-11eb-ae48-0250e3456170', '71acfadc-bbe2-11eb-8af8-0250e3456170', 'Alimentos', '', 1, '2021-05-25 11:52:22', NULL),
	('9809d61d-bd79-11eb-ae48-0250e3456170', '71acfadc-bbe2-11eb-8af8-0250e3456170', 'Seguridad Industrial', '', 1, '2021-05-25 11:52:30', NULL),
	('9c9d0cc9-bd79-11eb-ae48-0250e3456170', '71acfadc-bbe2-11eb-8af8-0250e3456170', 'Ingeniería Industrial', '', 1, '2021-05-25 11:52:38', NULL),
	('a288e1d0-bd79-11eb-ae48-0250e3456170', '8bc0ad03-bbe2-11eb-8af8-0250e3456170', 'Educación Básica', '', 1, '2021-05-25 11:52:48', NULL),
	('ab27b91a-bd79-11eb-ae48-0250e3456170', '8bc0ad03-bbe2-11eb-8af8-0250e3456170', 'Pedagogía de los Idiomas Nacionales y Extranjeros', '', 1, '2021-05-25 11:53:02', NULL),
	('b9c1b0ea-bd79-11eb-ae48-0250e3456170', '8bc0ad03-bbe2-11eb-8af8-0250e3456170', 'Psicopedagogía', '', 1, '2021-05-25 11:53:27', NULL);
/*!40000 ALTER TABLE `careers` ENABLE KEYS */;

-- Volcando estructura para tabla dep_medico.dental_consultation
CREATE TABLE IF NOT EXISTS `dental_consultation` (
  `id` varchar(36) NOT NULL,
  `dentalhistory_id` varchar(36) NOT NULL,
  `turn_id` varchar(36) DEFAULT NULL,
  `lips` tinyint DEFAULT '0',
  `cheeks` tinyint DEFAULT '0',
  `maxilla_sup` tinyint DEFAULT '0',
  `maxilla_inf` tinyint DEFAULT '0',
  `tongue` tinyint DEFAULT '0',
  `palate` tinyint DEFAULT '0',
  `floor` tinyint DEFAULT '0',
  `jowl` tinyint DEFAULT '0',
  `salivary_glands` tinyint DEFAULT '0',
  `oropharynx` tinyint DEFAULT '0',
  `atm` tinyint DEFAULT '0',
  `ganglion` tinyint DEFAULT '0',
  `description` tinyint DEFAULT '0',
  `recession_sup` text NOT NULL,
  `mobility_sup` text NOT NULL,
  `vestibular_sup` text NOT NULL,
  `lingual` text NOT NULL,
  `vestibular_inf` text NOT NULL,
  `mobility_inf` text NOT NULL,
  `recession_inf` text NOT NULL,
  `bacterial_plaque` tinyint NOT NULL DEFAULT '0',
  `calculus` tinyint NOT NULL DEFAULT '0',
  `gingivitis` tinyint NOT NULL DEFAULT '0',
  `periodontal_disease` tinyint NOT NULL DEFAULT '0',
  `fluorosis` tinyint NOT NULL DEFAULT '0',
  `diagnostic` text NOT NULL,
  `created_by` varchar(36) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Volcando datos para la tabla dep_medico.dental_consultation: 0 rows
/*!40000 ALTER TABLE `dental_consultation` DISABLE KEYS */;
/*!40000 ALTER TABLE `dental_consultation` ENABLE KEYS */;

-- Volcando estructura para tabla dep_medico.dental_evolve
CREATE TABLE IF NOT EXISTS `dental_evolve` (
  `id` varchar(36) NOT NULL,
  `dentalhistory_id` varchar(36) NOT NULL,
  `date` date NOT NULL,
  `diagnostic_complications` text NOT NULL,
  `procedures` text NOT NULL,
  `prescriptions` text NOT NULL,
  `confirmated` tinyint NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Volcando datos para la tabla dep_medico.dental_evolve: 0 rows
/*!40000 ALTER TABLE `dental_evolve` DISABLE KEYS */;
/*!40000 ALTER TABLE `dental_evolve` ENABLE KEYS */;

-- Volcando estructura para tabla dep_medico.dental_history
CREATE TABLE IF NOT EXISTS `dental_history` (
  `id` varchar(36) NOT NULL,
  `person_id` varchar(36) NOT NULL,
  `gender` char(1) NOT NULL,
  `antibiotic_allergy` tinyint DEFAULT '0',
  `allergy_anesthesia` tinyint DEFAULT '0',
  `bleeding` tinyint DEFAULT '0',
  `tuberculosis` tinyint DEFAULT '0',
  `asthma` tinyint DEFAULT '0',
  `diabetes` tinyint DEFAULT '0',
  `hypertension` tinyint DEFAULT '0',
  `others` tinyint DEFAULT '0',
  `description` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Volcando datos para la tabla dep_medico.dental_history: 0 rows
/*!40000 ALTER TABLE `dental_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `dental_history` ENABLE KEYS */;

-- Volcando estructura para tabla dep_medico.docs
CREATE TABLE IF NOT EXISTS `docs` (
  `id` varchar(36) NOT NULL,
  `person_id` varchar(36) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `name` varchar(50) NOT NULL,
  `url_doc` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Volcando datos para la tabla dep_medico.docs: 1 rows
/*!40000 ALTER TABLE `docs` DISABLE KEYS */;
/*!40000 ALTER TABLE `docs` ENABLE KEYS */;

-- Volcando estructura para tabla dep_medico.faculties
CREATE TABLE IF NOT EXISTS `faculties` (
  `id` varchar(36) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text,
  `visible` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Volcando datos para la tabla dep_medico.faculties: 6 rows
/*!40000 ALTER TABLE `faculties` DISABLE KEYS */;
INSERT INTO `faculties` (`id`, `name`, `description`, `visible`, `created_at`, `updated_at`) VALUES
	('1d779565-bbe2-11eb-8af8-0250e3456170', 'Facultad de Ciencias de la Ingeniería', '', 1, '2021-05-23 11:15:39', '2021-05-25 10:51:15'),
	('5c998772-bbe2-11eb-8af8-0250e3456170', 'Facultad de Ciencias Empresariales', '', 1, '2021-05-23 11:17:25', NULL),
	('6533aca6-bbe2-11eb-8af8-0250e3456170', 'Facultad de Ciencias Agropecuarias', '', 1, '2021-05-23 11:17:39', NULL),
	('71acfadc-bbe2-11eb-8af8-0250e3456170', 'Facultad de Ciencias de la Industria y Producción', '', 1, '2021-05-23 11:18:00', NULL),
	('8bc0ad03-bbe2-11eb-8af8-0250e3456170', 'Facultad de Ciencias Sociales y de la Educación', '', 1, '2021-05-23 11:18:44', NULL),
	('9b95be6c-bbe2-11eb-8af8-0250e3456170', 'Vicerrectorado Académico', '', 1, '2021-05-23 11:19:11', NULL);
/*!40000 ALTER TABLE `faculties` ENABLE KEYS */;

-- Volcando estructura para tabla dep_medico.familiar_requests
CREATE TABLE IF NOT EXISTS `familiar_requests` (
  `id` varchar(36) NOT NULL,
  `user_id` varchar(36) NOT NULL,
  `type` varchar(50) NOT NULL COMMENT 'new = Nuevo paciente, existing = Paciente registrado',
  `kin` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `person_id` varchar(36) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla dep_medico.familiar_requests: 0 rows
/*!40000 ALTER TABLE `familiar_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `familiar_requests` ENABLE KEYS */;

-- Volcando estructura para tabla dep_medico.familiar_request_details
CREATE TABLE IF NOT EXISTS `familiar_request_details` (
  `request_id` varchar(36) NOT NULL,
  `name` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `identification` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `phone` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `birth_date` date NOT NULL,
  `civil_state` varchar(50) NOT NULL,
  `sex` char(1) DEFAULT NULL,
  `address` varchar(100) NOT NULL,
  `backup_doc` text NOT NULL,
  UNIQUE KEY `request_id` (`request_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla dep_medico.familiar_request_details: 0 rows
/*!40000 ALTER TABLE `familiar_request_details` DISABLE KEYS */;
/*!40000 ALTER TABLE `familiar_request_details` ENABLE KEYS */;

-- Volcando estructura para tabla dep_medico.log_products
CREATE TABLE IF NOT EXISTS `log_products` (
  `user_id` varchar(36) NOT NULL,
  `action` varchar(100) NOT NULL,
  `product_id` varchar(36) NOT NULL,
  `datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `details` longtext NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla dep_medico.log_products: 5 rows
/*!40000 ALTER TABLE `log_products` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_products` ENABLE KEYS */;

-- Volcando estructura para tabla dep_medico.medical_consultation
CREATE TABLE IF NOT EXISTS `medical_consultation` (
  `id` varchar(36) NOT NULL,
  `medicalhistory_id` varchar(36) NOT NULL,
  `turn_id` varchar(36) DEFAULT NULL,
  `reason` text NOT NULL,
  `head_neck` text,
  `thorax` text,
  `abdomen` text,
  `extremities` text,
  `diagnostic` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `treatment` text NOT NULL,
  `created_by` varchar(36) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Volcando datos para la tabla dep_medico.medical_consultation: 1 rows
/*!40000 ALTER TABLE `medical_consultation` DISABLE KEYS */;
/*!40000 ALTER TABLE `medical_consultation` ENABLE KEYS */;

-- Volcando estructura para tabla dep_medico.medical_evolve
CREATE TABLE IF NOT EXISTS `medical_evolve` (
  `id` varchar(36) NOT NULL,
  `medicalhistory_id` varchar(36) NOT NULL,
  `date` date NOT NULL,
  `evolve_notes` text NOT NULL,
  `prescription` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Volcando datos para la tabla dep_medico.medical_evolve: 3 rows
/*!40000 ALTER TABLE `medical_evolve` DISABLE KEYS */;
/*!40000 ALTER TABLE `medical_evolve` ENABLE KEYS */;

-- Volcando estructura para tabla dep_medico.medical_history
CREATE TABLE IF NOT EXISTS `medical_history` (
  `id` varchar(36) NOT NULL,
  `person_id` varchar(36) NOT NULL,
  `app` text COMMENT 'Antecedentes Patológicos Personales',
  `apf` text COMMENT 'Antecedentes Patológicos Familiares',
  `ago` text COMMENT 'Antecedentes Gineco-Obstetricos',
  `allergies` text,
  `habits` text,
  `pressure` double NOT NULL DEFAULT '0',
  `heart_frequency` double NOT NULL DEFAULT '0',
  `weight` double NOT NULL DEFAULT '0',
  `height` double NOT NULL DEFAULT '0',
  `imc` double NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by` varchar(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Volcando datos para la tabla dep_medico.medical_history: 1 rows
/*!40000 ALTER TABLE `medical_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `medical_history` ENABLE KEYS */;

-- Volcando estructura para tabla dep_medico.nursing_data
CREATE TABLE IF NOT EXISTS `nursing_data` (
  `id` varchar(36) NOT NULL,
  `turn_id` varchar(36) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `person_id` varchar(36) NOT NULL,
  `weight` double NOT NULL DEFAULT '0',
  `pressure` double NOT NULL DEFAULT '0',
  `temperature` double NOT NULL DEFAULT '0',
  `heart_frequency` double(22,0) NOT NULL DEFAULT '0',
  `oxygen` double NOT NULL DEFAULT '0',
  `height` double NOT NULL DEFAULT '0',
  `breathing_frequency` double NOT NULL DEFAULT '0',
  `imc` double NOT NULL DEFAULT '0',
  `created_by` varchar(36) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Volcando datos para la tabla dep_medico.nursing_data: 2 rows
/*!40000 ALTER TABLE `nursing_data` DISABLE KEYS */;
/*!40000 ALTER TABLE `nursing_data` ENABLE KEYS */;

-- Volcando estructura para tabla dep_medico.occupations
CREATE TABLE IF NOT EXISTS `occupations` (
  `id` varchar(36) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text,
  `visible` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Volcando datos para la tabla dep_medico.occupations: 13 rows
/*!40000 ALTER TABLE `occupations` DISABLE KEYS */;
INSERT INTO `occupations` (`id`, `name`, `description`, `visible`, `created_at`, `updated_at`) VALUES
	('15107a76-be3f-11eb-90e1-0250e3456170', 'Docente', '', 1, '2021-05-26 11:26:11', '2021-05-26 12:02:38'),
	('9037dbf7-be43-11eb-90e1-0250e3456170', 'Conserje', '', 1, '2021-05-26 11:58:15', NULL),
	('b8499ce6-be43-11eb-90e1-0250e3456170', 'Secretario', '', 1, '2021-05-26 11:59:22', NULL),
	('bcd100d5-be43-11eb-90e1-0250e3456170', 'Coordinador', '', 1, '2021-05-26 11:59:30', NULL),
	('c230cba6-be43-11eb-90e1-0250e3456170', 'Rector', '', 1, '2021-05-26 11:59:39', NULL),
	('e3d725dc-be43-11eb-90e1-0250e3456170', 'Vicerrector', '', 1, '2021-05-26 12:00:35', NULL),
	('e91a2698-be43-11eb-90e1-0250e3456170', 'Vicerrector Académico', '', 1, '2021-05-26 12:00:44', NULL),
	('efd97c4c-be43-11eb-90e1-0250e3456170', 'Vicerrector Administrativo', '', 1, '2021-05-26 12:00:56', NULL),
	('f35a2d1b-be43-11eb-90e1-0250e3456170', 'Decano', '', 1, '2021-05-26 12:01:01', NULL),
	('0bb21d35-be44-11eb-90e1-0250e3456170', 'Sub decano', '', 1, '2021-05-26 12:01:42', NULL),
	('37589607-be44-11eb-90e1-0250e3456170', 'Coordinador de Área', '', 1, '2021-05-26 12:02:56', NULL),
	('c5575220-be44-11eb-90e1-0250e3456170', 'Guardia', '', 1, '2021-05-26 12:06:54', NULL),
	('d6cabda7-be44-11eb-90e1-0250e3456170', 'Bibliotecario', '', 1, '2021-05-26 12:07:23', NULL);
/*!40000 ALTER TABLE `occupations` ENABLE KEYS */;

-- Volcando estructura para tabla dep_medico.odontogram_symb
CREATE TABLE IF NOT EXISTS `odontogram_symb` (
  `id` varchar(36) NOT NULL,
  `name` varchar(50) NOT NULL,
  `url` text NOT NULL,
  `description` text CHARACTER SET latin1 COLLATE latin1_swedish_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Volcando datos para la tabla dep_medico.odontogram_symb: 0 rows
/*!40000 ALTER TABLE `odontogram_symb` DISABLE KEYS */;
/*!40000 ALTER TABLE `odontogram_symb` ENABLE KEYS */;

-- Volcando estructura para tabla dep_medico.persons
CREATE TABLE IF NOT EXISTS `persons` (
  `id` varchar(36) NOT NULL,
  `name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `identification` varchar(15) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(10) DEFAULT NULL,
  `birth_date` date NOT NULL,
  `civil_state` varchar(50) NOT NULL,
  `sex` char(1) DEFAULT NULL,
  `address` varchar(100) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `identification` (`identification`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Volcando datos para la tabla dep_medico.persons: 6 rows
/*!40000 ALTER TABLE `persons` DISABLE KEYS */;
INSERT INTO `persons` (`id`, `name`, `last_name`, `identification`, `email`, `phone`, `birth_date`, `civil_state`, `sex`, `address`, `active`, `created_at`, `updated_at`) VALUES
	('098b7c62-a71b-4e67-96ac-7d4244d37571', 'Administrador', '', 'xxxxxxxxxx', NULL, NULL, '1984-02-01', '', NULL, '', 1, '2021-10-20 18:32:16', NULL);
/*!40000 ALTER TABLE `persons` ENABLE KEYS */;

-- Volcando estructura para tabla dep_medico.products
CREATE TABLE IF NOT EXISTS `products` (
  `id` varchar(36) NOT NULL,
  `name` varchar(150) NOT NULL,
  `image` text,
  `units` int NOT NULL DEFAULT '0',
  `description` text,
  `visible` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla dep_medico.products: 2 rows
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` (`id`, `name`, `image`, `units`, `description`, `visible`, `created_at`, `updated_at`, `deleted_at`) VALUES
	('4b23bec9-9ab0-46ec-ae4b-9e48a385065b', 'Paracetamol v2', '/dep_medico/media/products/2021/07/25/image_20217250162.jpg', 30, 'Pastillas para calmar el dolor de cabeza y el estómago', 0, '2021-07-24 23:36:59', '2021-07-25 00:16:02', NULL),
	('86ec0a0b-441f-436a-a597-d406413141f2', 'Paracetamol v2', '/dep_medico/media/products/2021/07/25/image_202172595114.jpg', 48, 'Pastillas para el dolor de cabeza', 1, '2021-07-25 09:50:55', '2021-07-25 09:51:14', NULL);
/*!40000 ALTER TABLE `products` ENABLE KEYS */;

-- Volcando estructura para tabla dep_medico.recipes
CREATE TABLE IF NOT EXISTS `recipes` (
  `id` varchar(36) NOT NULL,
  `person_id` varchar(36) NOT NULL,
  `created_by` varchar(36) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla dep_medico.recipes: 1 rows
/*!40000 ALTER TABLE `recipes` DISABLE KEYS */;
/*!40000 ALTER TABLE `recipes` ENABLE KEYS */;

-- Volcando estructura para tabla dep_medico.recipe_details
CREATE TABLE IF NOT EXISTS `recipe_details` (
  `id` varchar(36) NOT NULL,
  `recipe_id` varchar(36) NOT NULL,
  `product` varchar(200) NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `indications` text NOT NULL,
  `kit_quantity` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla dep_medico.recipe_details: 2 rows
/*!40000 ALTER TABLE `recipe_details` DISABLE KEYS */;
/*!40000 ALTER TABLE `recipe_details` ENABLE KEYS */;

-- Volcando estructura para tabla dep_medico.restore_pwd_codes
CREATE TABLE IF NOT EXISTS `restore_pwd_codes` (
  `id` varchar(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` varchar(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla dep_medico.restore_pwd_codes: 1 rows
/*!40000 ALTER TABLE `restore_pwd_codes` DISABLE KEYS */;
/*!40000 ALTER TABLE `restore_pwd_codes` ENABLE KEYS */;

-- Volcando estructura para tabla dep_medico.schedule_settings
CREATE TABLE IF NOT EXISTS `schedule_settings` (
  `id` varchar(36) NOT NULL,
  `area_id` varchar(36) NOT NULL,
  `days` varchar(100) NOT NULL COMMENT 'días => [1, 2, 3, ...]',
  `hours_p` text NOT NULL COMMENT 'horas presencial => [\r\n	{start: 09:00, end: 12:00},\r\n	{start: 13:00, end: 15:00}\r\n]',
  `hours_t` text NOT NULL COMMENT 'horas telemedicina => [\r\n	{start: 09:00, end: 12:00},\r\n	{start: 13:00, end: 15:00}\r\n]',
  `duration` int NOT NULL COMMENT 'duración en segundos',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla dep_medico.schedule_settings: 4 rows
/*!40000 ALTER TABLE `schedule_settings` DISABLE KEYS */;
INSERT INTO `schedule_settings` (`id`, `area_id`, `days`, `hours_p`, `hours_t`, `duration`, `created_at`) VALUES
	('3edcc596-cec0-11eb-996b-0250e3456170', '8838dd8e-99d2-4f87-8fbf-4d8e8952136b', '[1,2,3,4,5]', '[{"start":"09:00:00", "end":"11:40:00"}]', '[{"start":"14:00:00", "end":"16:40:00"}]', 1200, '2021-06-16 11:31:04'),
	('40e513eb-cec0-11eb-996b-0250e3456170', 'd3cd1d33-5932-4550-917c-0db1b2a6ee4a', '[1,2,3,4,5]', '[{"start":"09:00:00", "end":"11:40:00"}]', '[{"start":"14:00:00", "end":"16:40:00"}]', 1200, '2021-06-16 11:31:08'),
	('41bd1a4f-cec0-11eb-996b-0250e3456170', '32e42172-64a2-4d7e-8173-cac3080b9afa', '[1,2,3,4,5]', '[{"start":"09:00:00", "end":"11:40:00"}]', '[{"start":"14:00:00", "end":"16:40:00"}]', 1200, '2021-06-16 11:31:09'),
	('41f8f2e0-cec0-11eb-996b-0250e3456170', '46aae5c8-f4f3-4095-8359-87bffea2ee74', '[1,2,3,4,5]', '[{"start":"09:00:00", "end":"11:40:00"}]', '[{"start":"14:00:00", "end":"16:40:00"}]', 1200, '2021-06-16 11:31:10');
/*!40000 ALTER TABLE `schedule_settings` ENABLE KEYS */;

-- Volcando estructura para tabla dep_medico.settings
CREATE TABLE IF NOT EXISTS `settings` (
  `id` varchar(36) NOT NULL,
  `area_id` varchar(36) NOT NULL,
  `meet_link` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `area_id` (`area_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla dep_medico.settings: 4 rows
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` (`id`, `area_id`, `meet_link`) VALUES
	('25125cc8-e278-11eb-ad16-0250e3456170', '8838dd8e-99d2-4f87-8fbf-4d8e8952136b', 'https://meet.google.com/zep-axvx-jvi'),
	('25126167-e278-11eb-ad16-0250e3456170', 'd3cd1d33-5932-4550-917c-0db1b2a6ee4a', ''),
	('25126257-e278-11eb-ad16-0250e3456170', '32e42172-64a2-4d7e-8173-cac3080b9afa', ''),
	('251262e9-e278-11eb-ad16-0250e3456170', '46aae5c8-f4f3-4095-8359-87bffea2ee74', '');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;

-- Volcando estructura para tabla dep_medico.turn
CREATE TABLE IF NOT EXISTS `turn` (
  `id` varchar(36) NOT NULL,
  `area_id` varchar(36) NOT NULL,
  `person_id` varchar(36) NOT NULL,
  `date` date NOT NULL,
  `init_time` time NOT NULL,
  `end_time` time NOT NULL,
  `status` char(2) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL COMMENT 'CR = Creado, CO = Confirmado, AT = Atendido, NA = No atendido, CA = Cancelado',
  `type` char(1) NOT NULL COMMENT 'P = presencial, T = telemedicina',
  `description` text NOT NULL,
  `created_by` varchar(36) NOT NULL,
  `checked_by` varchar(36) DEFAULT NULL COMMENT 'Se almacena el id de usuario',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Volcando datos para la tabla dep_medico.turn: 3 rows
/*!40000 ALTER TABLE `turn` DISABLE KEYS */;
/*!40000 ALTER TABLE `turn` ENABLE KEYS */;

-- Volcando estructura para tabla dep_medico.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` varchar(36) NOT NULL,
  `person_id` varchar(36) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` char(2) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '' COMMENT 'AD = admin, DR = doctor, US=usuario',
  `password` varchar(100) NOT NULL,
  `avatar` text,
  `active` tinyint NOT NULL DEFAULT '1',
  `visible` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `person_id` (`person_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Volcando datos para la tabla dep_medico.users: 5 rows
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` (`id`, `person_id`, `email`, `role`, `password`, `avatar`, `active`, `visible`, `created_at`, `updated_at`) VALUES
	('619978e4-b33c-11eb-9731-0250e3456170', '098b7c62-a71b-4e67-96ac-7d4244d37571', 'admin@admin.com', 'AD', '$2y$10$8g8ZETW3CsDVeYljrgvKtu2dbh3t5tIlEC2O/AXEJU1qrgpf/dhze', '/dep_medico/media/fotos/2021/05/16/foto_202151692619.png', 1, 1, '2021-05-12 11:09:08', NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;

-- Volcando estructura para tabla dep_medico.user_areas
CREATE TABLE IF NOT EXISTS `user_areas` (
  `user_id` varchar(36) NOT NULL,
  `area_id` varchar(36) NOT NULL,
  UNIQUE KEY `user_id_area_id` (`user_id`,`area_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Volcando datos para la tabla dep_medico.user_areas: 1 rows
/*!40000 ALTER TABLE `user_areas` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_areas` ENABLE KEYS */;

-- Volcando estructura para tabla dep_medico.user_career
CREATE TABLE IF NOT EXISTS `user_career` (
  `user_id` varchar(36) NOT NULL,
  `career_id` varchar(36) NOT NULL,
  `semester` int NOT NULL,
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Volcando datos para la tabla dep_medico.user_career: 2 rows
/*!40000 ALTER TABLE `user_career` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_career` ENABLE KEYS */;

-- Volcando estructura para tabla dep_medico.user_kinship
CREATE TABLE IF NOT EXISTS `user_kinship` (
  `employee_id` varchar(36) NOT NULL,
  `kinsman_id` varchar(36) NOT NULL,
  `kin` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `observation` text,
  UNIQUE KEY `employee_id_kinsman_id` (`employee_id`,`kinsman_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Volcando datos para la tabla dep_medico.user_kinship: 1 rows
/*!40000 ALTER TABLE `user_kinship` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_kinship` ENABLE KEYS */;

-- Volcando estructura para tabla dep_medico.user_occupation
CREATE TABLE IF NOT EXISTS `user_occupation` (
  `user_id` varchar(36) NOT NULL,
  `occupation_id` varchar(36) NOT NULL,
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla dep_medico.user_occupation: 1 rows
/*!40000 ALTER TABLE `user_occupation` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_occupation` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
