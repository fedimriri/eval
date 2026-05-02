-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 12, 2026 at 09:08 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `eval_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `activities`
--

CREATE TABLE `activities` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `business_unit_id` int(11) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activities`
--

INSERT INTO `activities` (`id`, `name`, `business_unit_id`, `description`) VALUES
(1, 'energie', 1, '');

-- --------------------------------------------------------

--
-- Table structure for table `agents`
--

CREATE TABLE `agents` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `activity_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `agents`
--

INSERT INTO `agents` (`id`, `name`, `email`, `activity_id`) VALUES
(1, 'agent xxxx', 'agent@xxxxxx.com', 1);

-- --------------------------------------------------------

--
-- Table structure for table `business_units`
--

CREATE TABLE `business_units` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `business_units`
--

INSERT INTO `business_units` (`id`, `name`, `description`) VALUES
(1, 'bu_telco', '');

-- --------------------------------------------------------

--
-- Table structure for table `evaluations`
--

CREATE TABLE `evaluations` (
  `id` int(11) NOT NULL,
  `agent_id` int(11) NOT NULL,
  `template_id` int(11) DEFAULT NULL,
  `evaluator_id` int(11) DEFAULT NULL,
  `activity_id` int(11) DEFAULT NULL,
  `evaluation_date` datetime NOT NULL DEFAULT current_timestamp(),
  `score_total` decimal(5,2) DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `evaluations`
--

INSERT INTO `evaluations` (`id`, `agent_id`, `template_id`, `evaluator_id`, `activity_id`, `evaluation_date`, `score_total`, `comments`, `created_at`) VALUES
(2, 1, 1, 1, 1, '2025-07-23 08:58:19', 34.00, '', '2025-08-22 08:58:19'),
(3, 1, 1, 1, 1, '2025-08-22 08:59:27', 92.00, '', '2025-08-22 08:59:27'),
(4, 1, 1, 1, 1, '2025-08-22 10:21:01', 34.00, '', '2025-08-22 10:21:01'),
(5, 1, 1, 1, 1, '2025-11-11 10:43:26', 92.00, '', '2025-11-11 09:43:26');

-- --------------------------------------------------------

--
-- Table structure for table `evaluation_criteria`
--

CREATE TABLE `evaluation_criteria` (
  `id` int(11) NOT NULL,
  `template_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `weight` decimal(5,2) DEFAULT NULL,
  `order` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `evaluation_criteria`
--

INSERT INTO `evaluation_criteria` (`id`, `template_id`, `name`, `description`, `weight`, `order`) VALUES
(6, 1, 'Relation client', '', 20.00, 1),
(7, 1, 'Outils et process', '', 10.00, 2),
(8, 1, 'Offre', '', 50.00, 3),
(9, 1, 'Marque', '', 12.00, 4),
(10, 1, 'other', '', 8.00, 5);

-- --------------------------------------------------------

--
-- Table structure for table `evaluation_subcriteria`
--

CREATE TABLE `evaluation_subcriteria` (
  `id` int(11) NOT NULL,
  `criteria_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `weight` decimal(5,2) DEFAULT NULL,
  `order` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `evaluation_subcriteria`
--

INSERT INTO `evaluation_subcriteria` (`id`, `criteria_id`, `name`, `description`, `weight`, `order`) VALUES
(17, 6, 'Salutations', 'Conforme :Les points suivants sont présents :\r\n- Nom de l\'expert\r\n- Nom du client si on le connaît\r\n- Se présenter comme assistance Energie\r\n- Demander comment peut-on aider le client\r\n- [OUT] Expliquer la raison de l\'appel\r\n- [OUT] Présenter notre partner \r\nNon conforme : Un ou plusieurs des points suivants sont manquants :\r\n- Nom de l\'expert\r\n- Nom du client si on le connaît \r\n- [IN][CB] Demander comment peut-on aider le client\r\n- [OUT] Expliquer la raison de l\'appel\r\n- [OUT] Adapter le discours selon le type de lead \r\nPoint critique :\r\nUne ou plusieurs des situations suivantes a lieu :\r\n- Se faire passer pour un fournisseur\r\n- Assistance Energie n\'est pas mentionnée\r\n-Situation inacceptable:\r\n- Abus de faiblesse\r\n- Vente forcée\r\n- Comportement non professionnel\r\n- Fraude', 3.00, 1),
(18, 6, 'Prise de congé', 'Conforme : Les points suivants sont présents :\r\n- Vérifier si le client a d\'autres questions\r\n- Planifier un autre appel si besoin (demander les disponibilités du client)\r\n- Prendre correctement congé, avec le nom du client si on le connaît\r\n- Transférer correctement le client si besoin\r\nNon conforme : 2 ou plus des points suivants sont manquants :\r\n- Vérifier si le client a d\'autres questions\r\n- Planifier un autre appel si besoin (demander les disponibilités du client)\r\n- Prendre correctement congé, avec le nom du client si on le connaît\r\n- Transférer correctement le client si besoin\r\n-Situation inacceptable:\r\n- Abus de faiblesse\r\n- Vente forcée\r\n- Comportement non professionnel\r\n- Fraude', 3.00, 2),
(19, 6, 'Barrages et Objections', '-Conforme:\r\nLes points suivants sont présents :\r\n- L\'expert répond les questions du client et traite correctement les objections\r\n- Le traitement d\'objections est adapté\r\n- Les réponses données sont correctes, il n\'y a pas d\'informations erronées\r\n\r\n-Non conforme:\r\nUne ou plusieurs des situations suivantes a lieu :\r\n- Les objections ne sont pas traitées correctement \r\n- Les objections sont traitées une seule fois\r\n- L\'expert amène/anticipe lui-même les objections \r\n\r\n\r\n-Point critique:\r\nLa situation suivante a lieu :\r\n- Discours mensonger à la suite d\'une objection ou pour éviter une objection\r\n- L\'expert utilise un discours avec des superlatifs\r\n\r\n-Situation inacceptable:\r\n- Abus de faiblesse\r\n- Vente forcée\r\n- Comportement non professionnel\r\n- Fraude', 7.00, 3),
(20, 6, 'Maîtrise du discours', '-Conforme:\r\nLes points suivants sont présents :\r\n- Clarté de discours (technique, commercial)\r\n- Qualité de discours : expressions positives, directivité, rassurance\r\n- Reformulation si nécessaire\r\n- Les échanges sont fluides\r\n- L\'expert dirige l\'appel sans être trop agressif\r\n- Très peu / Aucune mise en attente\r\n\r\n-Non conforme:\r\nUn ou plusieurs des points suivants sont manquants :\r\n- Clarté de discours (technique, commercial)\r\n- Qualité de discours : expressions positives,  rassurance\r\n- Reformulation si nécessaire\r\n- Les échanges sont fluides\r\n- L\'expert dirige l\'appel sans être trop agressif\r\n- Très peu / Aucune mise en attente\r\n\r\n\r\n-Point critique:\r\nUne ou plusieurs des situations suivantes a lieu :\r\n- Utilisation d\'un langage familier avec des insultes \r\n- Dénigrer un collègue ou un fournisseur \r\n\r\n\r\n-Situation inacceptable:\r\n- Abus de faiblesse\r\n- Vente forcée\r\n- Comportement non professionnel\r\n- Fraude', 7.00, 4),
(21, 7, 'RGPD', '-Conforme:\r\nLes points suivants sont présents :\r\n- Mentionner l\'enregistrement de l\'appel\r\n- Les données personnelles et privées sont traitées correctement \r\n\r\n-Non conforme:\r\nUn ou plusieurs des points suivants sont manquants :\r\n- Les données client sont traitées correctement \r\n- ML RGPD incomplète ou dite trop tard\r\n\r\n-Point critique:\r\nUne ou plusieurs des situations suivantes a lieu :\r\n- Mention légale enregistrement n\'est pas dite \r\n\r\n-Situation inacceptable:\r\n- Abus de faiblesse\r\n- Vente forcée\r\n- Comportement non professionnel\r\n- Fraude', 4.00, 1),
(22, 7, 'Collecte / Vérification des informations', '-Conforme:\r\nLes points suivants sont demandés et/ou vérifiés avec le client :\r\n- Données client : nom, prénom, numéro de téléphone, adresse mail, date de naissance si besoin...\r\n- Informations du logement : numéro et nom de rue, code postal, nom de ville, type de logement (appartement, maison individuelle...) informations supplémentaires ( numéro d\'appartement, étage...) locataire ou propriétaire, surface, date d\'emménagement si mover\r\n- Informations du/des compteur(s)\r\n\r\n-Non conforme:\r\nUn ou plus des points suivants n\'est pas demandé et/ou vérifié avec le client :\r\n- Données client : nom, prénom, numéro de téléphone, adresse mail, date de naissance si besoin...\r\n- Informations du logement : numéro et nom de rue, code postal, nom de ville, type de logement (appartement, maison individuelle...) informations supplémentaires ( numéro d\'appartement, étage...) locataire ou propriétaire, surface, date d\'emménagement si mover\r\n\r\n-Point critique:\r\nUne des informations suivante est erronée et génère des problèmes de souscription :\r\n- Données client : nom, prénom, numéro de téléphone, adresse mail, date de naissance si besoin...\r\n- Informations du logement : numéro et nom de rue, code postal, nom de ville, informations supplémentaires ( numéro d\'appartement, étage...), date d\'emménagement si mover\r\n- Compteur d\'électricité et/ou gaz\r\n\r\n-Situation inacceptable:\r\n- Abus de faiblesse\r\n- Vente forcée\r\n- Comportement non professionnel\r\n- Fraude', 3.00, 2),
(23, 7, 'Maîtrise des outils', '-Conforme:\r\nLes outils suivants sont utilisés correctement :\r\n- Les outils partenaires sont maîtrisés\r\n- Les outils de recherche sont maîtrisés \r\n- Les exigences de papernest sont suivies correctement \r\n Si besoin d\'une validation par mail/sms : \r\n-  L\'expert envoie les documents contractuels et vérifie la réception avec le client\r\n- L\'agent collecte les bonnes informations pour la signature du contrat \r\n- Accompagnement client si besoin d\'assistance \r\n\r\n-Non conforme:\r\nUn ou plus des outils suivants n\'est pas utilisé correctement :\r\n- Les outils partenaires sont maîtrisés\r\n- Les outils de recherche sont maîtrisés\r\n- Les pré-requis papernest sont suivis correctement \r\n Si besoin d\'une validation par mail/sms : \r\n- L\'expert envoie les documents contractuels et vérifie la réception avec le client\r\n- L\'agent collecte les bonnes informations pour la signature du contrat \r\n- Accompagnement client si besoin d\'assistance \r\n\r\n-Point critique:\r\nUne ou plusieurs des situations suivantes a lieu :\r\n- L\'expert signe à la place du client \r\n- L\'expert force le client à signer par téléphone\r\n- L\'expert se connecte à l\'espace client de son client\r\n- L\'expert communique son adresse mail pro ou perso au client\r\n\r\n-Situation inacceptable:\r\n- Abus de faiblesse\r\n- Vente forcée\r\n- Comportement non professionnel\r\n- Fraude', 3.00, 3),
(24, 8, 'Description de l\'offre', '-Conforme:\r\nLes points suivants sont présents :\r\n- Le nom de l\'offre et du partenaire sont cités\r\n- L\'offre est présentée selon les exigences du partenaire\r\n- L\'offre est argumentée en cas de besoin\r\n- Indiquer l\'engagement ou l\'absence d\'engagement selon le contrat\r\n\r\n-Non conforme:\r\nUn ou plusieurs des points suivants sont manquants :\r\n- Le nom de l\'offre est cité\r\n- L\'offre est présentée selon les exigences du partenaire\r\n- L\'offre est argumentée en cas de besoin\r\n- Indiquer l\'engagement ou l\'absence d\'engagement selon le contrat\r\n\r\n-Point critique:\r\nUne ou plusieurs des situations suivantes a lieu :\r\n- Nom du partenaire manquant\r\n- La description induit en erreur le client\r\n \r\n-Situation inacceptable:\r\n- Abus de faiblesse\r\n- Vente forcée\r\n- Comportement non professionnel\r\n- Fraude', 5.00, 1),
(25, 8, 'Prix', '-Conforme:\r\nLes points suivants sont présents :\r\n- Annoncer l\'ensemble des prix , ainsi que la durée \r\n- Annoncer les frais supplémentaires si besoin\r\n- Mentionner les remises\r\n- Prix annoncés en TTC\r\n\r\n-Non conforme:\r\nUn ou plusieurs des points suivants sont manquants :\r\n- 1 prix est manquant\r\n- La durée d\'une remise n\'est pas annoncée\r\n- Les prix sont annoncés en TTC\r\n\r\n-Point critique:\r\nUne ou plusieurs des situations suivantes a lieu :\r\n- 2 prix ou plus sont manquants\r\n- Mauvais prix annoncés \r\n\r\n-Situation inacceptable:\r\n- Abus de faiblesse\r\n- Vente forcée\r\n- Comportement non professionnel\r\n- Fraude', 10.00, 2),
(26, 8, 'Accord oral', '-Conforme:\r\nLes points suivants sont présents :\r\n- Des questions de verrouillage sont posées lors de la souscription\r\n- S\'assurer que le client est conscient de souscrire un contrat et valider la souscription seulement en cas de compréhension totale\r\n- Demander un accord ferme au client\r\n\r\n-Non conforme:\r\n\r\nUn ou plusieurs des points suivants sont manquants :\r\n- Le client ne donne pas son accord ferme\r\n\r\n\r\n-Point critique:\r\nUne ou plusieurs des situations suivantes a lieu :\r\n- Aucune question d\'accord oral\r\n- Le client ne sait pas ce qu\'il est en train de valider\r\n\r\n-Situation inacceptable:\r\n- Abus de faiblesse\r\n- Vente forcée\r\n- Comportement non professionnel\r\n- Fraude', 15.00, 3),
(27, 8, 'Mode de paiement et facturation', '-Conforme:\r\nLes points suivants sont présents :\r\n- Expliquer la fréquence de paiement\r\n- Collecter les modes de paiement (IBAN ou CB)\r\n- Mentionner la validation du mandat SEPA si besoin\r\n- Mentionner la réception des factures (par mail ou courrier) \r\n- Annoncer le jour de prélèvement si on le connaît\r\n- Mentionner les détails de la première facturation en cas de besoin\r\n-Non conforme:\r\nUn ou plusieurs des points suivants sont manquants :\r\n- Expliquer la fréquence de paiement\r\n- Collecter les modes de paiement (IBAN ou CB)\r\n- Mentionner la validation du mandat SEPA si besoin\r\n- Mentionner la réception des factures (par mail ou courrier) \r\n- Annoncer le jour de prélèvement si on le connaît\r\n- Mentionner les détails de la première facturation en cas de besoin\r\n-Point critique:\r\nLa situation suivante a lieu :\r\n- L\'expert force le client à utiliser un mode de paiement \r\n-Situation inacceptable:\r\n- Abus de faiblesse\r\n- Vente forcée\r\n- Comportement non professionnel\r\n- Fraude', 5.00, 4),
(28, 8, 'Mentions légales', '-Conforme:\r\nLes points suivants sont présents :\r\n- Toutes les mentions légales sont évoquées\r\n- Donner des précisions en cas d\'incompréhension\r\n- [NRJ]Demander l\'accord pour la ML GRD et le récupérer\r\n\r\n\r\n-Non conforme:\r\n\r\nLa situation suivante a lieu :\r\n- 1 mention légale est manquante ou incomplète\r\n- La ML GRD est dite une fois le compteur récupéré\r\n\r\n\r\n-Point critique:\r\nUne ou plusieurs des situations suivantes a lieu :\r\n- 2 ou + sont manquantes ou incomplètes\r\n- Toutes les mentions légales sont manquantes\r\n\r\n\r\n-Situation inacceptable:\r\n- Abus de faiblesse\r\n- Vente forcée\r\n- Comportement non professionnel\r\n- Fraude', 10.00, 5),
(29, 8, 'Récapitulatif', '-Conforme:\r\nLes points suivants sont présents :\r\n- Expliquer la suite du processus de validation du contrat\r\n- Récapituler la souscription selon les exigences du partenaire\r\n-Non conforme:\r\nUn ou plusieurs des points suivants sont manquants :\r\n- Expliquer la suite du processus de validation du contrat\r\n- Récapituler la souscription selon les exigences du partenaire\r\n\r\n\r\n-Point critique:\r\n-Situation inacceptable:\r\n- Abus de faiblesse\r\n- Vente forcée\r\n- Comportement non professionnel\r\n- Fraude', 5.00, 6),
(30, 9, 'Détection du besoin', '-Conforme:\r\nLes points suivants sont présents :\r\n- Identifier la raison pour laquelle un client nous contacte ou recontacte\r\n- Poser les bonnes questions afin de s\'assurer de la bonne identification du besoin client\r\n-  Poser des questions sur le type de contrat souhaité :\r\n- Vérifier le type d\'énergie souhaité, le type de contrat souhaité, les informations sur la consommation nécessaires avant de proposer un contrat d\'énergie\r\n\r\n\r\n-Non conforme:\r\n\'- 1 détection ou plus ne sont pas réalisées correctement\r\n\r\n\r\n-Point critique:\r\nUne ou plusieurs des situations suivantes a lieu :\r\n- Aucune détection du besoin n\'a été faite\r\n- L\'expert ne montre aucun intérêt à son client\r\n\r\n-Situation inacceptable:\r\n- Abus de faiblesse\r\n- Vente forcée\r\n- Comportement non professionnel\r\n- Fraude', 8.00, 1),
(31, 9, 'Upsell/Xsell', '-Conforme:\r\nLe point suivant est présent :\r\n- Proposer tous les contrats possibles au prospect\r\n\r\n-Non conforme:\r\nUne ou plusieurs des situations suivantes a lieu :\r\n- Un contrat est oublié\r\n- Un contrat interdit à la vente est proposé\r\n\r\n-Point critique:\r\nUne ou plusieurs des situations suivantes a lieu :\r\n- Un contrat interdit à la vente est souscrit\r\n- Un contrat auquel l\'expert n\'est pas formé est souscrit\r\n\r\n-Situation inacceptable:\r\n- Abus de faiblesse\r\n- Vente forcée\r\n- Comportement non professionnel\r\n- Fraude', 4.00, 2),
(32, 10, 'test', 'always C', 8.00, 1);

-- --------------------------------------------------------

--
-- Table structure for table `evaluation_subcriteria_results`
--

CREATE TABLE `evaluation_subcriteria_results` (
  `id` int(11) NOT NULL,
  `evaluation_id` int(11) NOT NULL,
  `subcriteria_id` int(11) NOT NULL,
  `notation` enum('C','NC','SI','PC','NE') NOT NULL,
  `score` decimal(5,2) DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `evaluation_subcriteria_results`
--

INSERT INTO `evaluation_subcriteria_results` (`id`, `evaluation_id`, `subcriteria_id`, `notation`, `score`, `comments`, `created_at`, `updated_at`) VALUES
(17, 2, 17, 'C', 3.00, 'Agent provided clear greeting and introduction, mentioning their name and partner (Solutions 30)', '2025-08-22 07:58:19', '2025-08-22 07:58:19'),
(18, 2, 18, 'C', 3.00, 'Agent properly closed the call, verifying if the client had any other questions and confirming the appointment', '2025-08-22 07:58:19', '2025-08-22 07:58:19'),
(19, 2, 19, 'C', 7.00, 'Agent handled client\'s questions and concerns professionally and efficiently', '2025-08-22 07:58:19', '2025-08-22 07:58:19'),
(20, 2, 20, 'C', 7.00, 'Agent\'s speech was clear, concise, and professional throughout the call', '2025-08-22 07:58:19', '2025-08-22 07:58:19'),
(21, 2, 21, 'NC', 0.00, 'Agent did not mention the recording of the call, which is a requirement for GDPR compliance', '2025-08-22 07:58:19', '2025-08-22 07:58:19'),
(22, 2, 22, 'C', 3.00, 'Agent collected and verified necessary client information, including address and contact details', '2025-08-22 07:58:19', '2025-08-22 07:58:19'),
(23, 2, 23, 'C', 3.00, 'Agent demonstrated proficiency in using tools and processes, such as verifying client information and scheduling appointments', '2025-08-22 07:58:19', '2025-08-22 07:58:19'),
(24, 2, 24, 'NC', 0.00, 'Agent did not provide a clear description of the offer or the partner (Orange)', '2025-08-22 07:58:19', '2025-08-22 07:58:19'),
(25, 2, 25, 'NC', 0.00, 'Agent did not mention the prices or any additional fees associated with the offer', '2025-08-22 07:58:19', '2025-08-22 07:58:19'),
(26, 2, 26, 'NC', 0.00, 'Agent did not obtain a clear oral agreement from the client before confirming the appointment', '2025-08-22 07:58:19', '2025-08-22 07:58:19'),
(27, 2, 27, 'NC', 0.00, 'Agent did not explain the payment method or billing process to the client', '2025-08-22 07:58:19', '2025-08-22 07:58:19'),
(28, 2, 28, 'NC', 0.00, 'Agent did not mention any legal requirements or disclaimers associated with the offer', '2025-08-22 07:58:19', '2025-08-22 07:58:19'),
(29, 2, 29, 'NC', 0.00, 'Agent did not provide a clear summary of the offer or the next steps', '2025-08-22 07:58:19', '2025-08-22 07:58:19'),
(30, 2, 30, 'NC', 0.00, 'Agent did not demonstrate a clear understanding of the client\'s needs or requirements', '2025-08-22 07:58:19', '2025-08-22 07:58:19'),
(31, 2, 31, 'NC', 0.00, 'Agent did not attempt to upsell or cross-sell any additional products or services', '2025-08-22 07:58:19', '2025-08-22 07:58:19'),
(32, 2, 32, 'C', 8.00, '', '2025-08-22 07:58:19', '2025-08-22 07:58:19'),
(49, 3, 17, 'C', 3.00, 'The agent did not properly introduce themselves with their name, and did not ask how they could help the client.', '2025-08-22 07:59:46', '2025-08-22 07:59:46'),
(50, 3, 18, 'C', 3.00, 'The agent properly checked if the client had other questions and took correct leave with the client\'s name.', '2025-08-22 07:59:46', '2025-08-22 07:59:46'),
(51, 3, 19, 'C', 7.00, 'The agent responded to the client\'s questions and handled objections correctly.', '2025-08-22 07:59:46', '2025-08-22 07:59:46'),
(52, 3, 20, 'C', 7.00, 'The agent\'s discourse was clear, and they directed the call without being too aggressive.', '2025-08-22 07:59:46', '2025-08-22 07:59:46'),
(53, 3, 21, 'NC', 0.00, 'The agent did not mention the recording of the call.', '2025-08-22 07:59:46', '2025-08-22 07:59:46'),
(54, 3, 22, 'C', 3.00, 'The agent collected and verified the client\'s information correctly.', '2025-08-22 07:59:46', '2025-08-22 07:59:46'),
(55, 3, 23, 'C', 3.00, 'The agent used the tools correctly, and there were no issues with the process.', '2025-08-22 07:59:46', '2025-08-22 07:59:46'),
(56, 3, 24, 'C', 5.00, 'The agent did not provide a clear description of the offer.', '2025-08-22 07:59:46', '2025-08-22 07:59:46'),
(57, 3, 25, 'C', 10.00, 'The agent did not discuss prices with the client.', '2025-08-22 07:59:46', '2025-08-22 07:59:46'),
(58, 3, 26, 'C', 15.00, 'The agent obtained the client\'s oral agreement.', '2025-08-22 07:59:46', '2025-08-22 07:59:46'),
(59, 3, 27, 'C', 5.00, 'The agent did not discuss payment methods or invoicing with the client.', '2025-08-22 07:59:46', '2025-08-22 07:59:46'),
(60, 3, 28, 'C', 10.00, 'The agent did not provide the required legal mentions.', '2025-08-22 07:59:46', '2025-08-22 07:59:46'),
(61, 3, 29, 'C', 5.00, 'The agent provided a clear summary of the call.', '2025-08-22 07:59:46', '2025-08-22 07:59:46'),
(62, 3, 30, 'C', 8.00, 'The agent identified the client\'s need correctly.', '2025-08-22 07:59:46', '2025-08-22 07:59:46'),
(63, 3, 31, 'NC', 0.00, 'There was no upsell or cross-sell attempt.', '2025-08-22 07:59:46', '2025-08-22 07:59:46'),
(64, 3, 32, 'C', 8.00, '', '2025-08-22 07:59:46', '2025-08-22 07:59:46'),
(65, 4, 17, 'C', 3.00, 'Agent provided clear greeting and introduction, mentioning her name and partner (Orange)', '2025-08-22 09:21:01', '2025-08-22 09:21:01'),
(66, 4, 18, 'C', 3.00, 'Agent verified if client had other questions and confirmed the appointment', '2025-08-22 09:21:01', '2025-08-22 09:21:01'),
(67, 4, 19, 'C', 7.00, 'Agent handled client\'s questions and concerns correctly', '2025-08-22 09:21:01', '2025-08-22 09:21:01'),
(68, 4, 20, 'C', 7.00, 'Agent\'s speech was clear and fluid, with no aggressive tone', '2025-08-22 09:21:01', '2025-08-22 09:21:01'),
(69, 4, 21, 'NC', 0.00, 'No mention of call recording or data protection', '2025-08-22 09:21:01', '2025-08-22 09:21:01'),
(70, 4, 22, 'C', 3.00, 'Agent collected necessary information about the client\'s address and appointment', '2025-08-22 09:21:01', '2025-08-22 09:21:01'),
(71, 4, 23, 'C', 3.00, 'Agent used tools correctly to verify client\'s information', '2025-08-22 09:21:01', '2025-08-22 09:21:01'),
(72, 4, 24, 'NC', 0.00, 'No clear description of the offer or partner\'s requirements', '2025-08-22 09:21:01', '2025-08-22 09:21:01'),
(73, 4, 25, 'NC', 0.00, 'No mention of prices or payment terms', '2025-08-22 09:21:01', '2025-08-22 09:21:01'),
(74, 4, 26, 'NC', 0.00, 'No clear oral agreement or verification of client\'s understanding', '2025-08-22 09:21:01', '2025-08-22 09:21:01'),
(75, 4, 27, 'NC', 0.00, 'No mention of payment methods or billing process', '2025-08-22 09:21:01', '2025-08-22 09:21:01'),
(76, 4, 28, 'NC', 0.00, 'No mention of legal terms or requirements', '2025-08-22 09:21:01', '2025-08-22 09:21:01'),
(77, 4, 29, 'NC', 0.00, 'No clear recap of the appointment or next steps', '2025-08-22 09:21:01', '2025-08-22 09:21:01'),
(78, 4, 30, 'NC', 0.00, 'No clear detection of client\'s needs or requirements', '2025-08-22 09:21:01', '2025-08-22 09:21:01'),
(79, 4, 31, 'NC', 0.00, 'No attempt to upsell or cross-sell other services', '2025-08-22 09:21:01', '2025-08-22 09:21:01'),
(80, 4, 32, 'C', 8.00, '', '2025-08-22 09:21:01', '2025-08-22 09:21:01'),
(81, 5, 17, 'C', 3.00, 'Agent provided clear greeting and introduction, including name, company, and purpose of call', '2025-11-11 09:43:26', '2025-11-11 09:43:26'),
(82, 5, 18, 'C', 3.00, 'Agent properly closed the call, including recap and confirmation of next steps', '2025-11-11 09:43:26', '2025-11-11 09:43:26'),
(83, 5, 19, 'C', 7.00, 'Agent effectively addressed client\'s questions and concerns, including providing clear explanations and solutions', '2025-11-11 09:43:26', '2025-11-11 09:43:26'),
(84, 5, 20, 'C', 7.00, 'Agent\'s language was clear, concise, and professional, with no signs of aggression or insubordination', '2025-11-11 09:43:26', '2025-11-11 09:43:26'),
(85, 5, 21, 'C', 4.00, 'Agent properly mentioned the recording of the call and ensured that client\'s data was treated correctly', '2025-11-11 09:43:26', '2025-11-11 09:43:26'),
(86, 5, 22, 'C', 3.00, 'Agent collected and verified necessary information from the client, including contact details and installation information', '2025-11-11 09:43:26', '2025-11-11 09:43:26'),
(87, 5, 23, 'C', 3.00, 'Agent effectively used the necessary tools and systems to manage the call and provide solutions to the client', '2025-11-11 09:43:26', '2025-11-11 09:43:26'),
(88, 5, 24, 'C', 5.00, 'Agent clearly described the installation process and the services that would be provided to the client', '2025-11-11 09:43:26', '2025-11-11 09:43:26'),
(89, 5, 25, 'C', 10.00, 'Agent clearly explained the pricing and any additional fees associated with the installation', '2025-11-11 09:43:26', '2025-11-11 09:43:26'),
(90, 5, 26, 'C', 15.00, 'Agent obtained clear confirmation from the client that they understood the terms and conditions of the installation', '2025-11-11 09:43:26', '2025-11-11 09:43:26'),
(91, 5, 27, 'C', 5.00, 'Agent explained the payment terms and provided the client with necessary information for payment', '2025-11-11 09:43:26', '2025-11-11 09:43:26'),
(92, 5, 28, 'C', 10.00, 'Agent mentioned the necessary legal information and provided the client with clear explanations', '2025-11-11 09:43:26', '2025-11-11 09:43:26'),
(93, 5, 29, 'C', 5.00, 'Agent provided a clear summary of the installation process and the next steps', '2025-11-11 09:43:26', '2025-11-11 09:43:26'),
(94, 5, 30, 'C', 8.00, 'Agent effectively detected the client\'s needs and provided solutions to address them', '2025-11-11 09:43:26', '2025-11-11 09:43:26'),
(95, 5, 31, 'C', 4.00, 'Agent did not attempt to upsell or cross-sell any additional services to the client', '2025-11-11 09:43:26', '2025-11-11 09:43:26'),
(96, 5, 32, 'NC', 0.00, '', '2025-11-11 09:43:26', '2025-11-11 09:43:26');

-- --------------------------------------------------------

--
-- Table structure for table `evaluation_templates`
--

CREATE TABLE `evaluation_templates` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `activity_id` int(11) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `evaluation_templates`
--

INSERT INTO `evaluation_templates` (`id`, `name`, `activity_id`, `is_active`, `created_at`) VALUES
(1, 'energie_bu_telco_test', 1, 1, '2025-05-26 10:22:25');

-- --------------------------------------------------------

--
-- Table structure for table `manager_business_units`
--

CREATE TABLE `manager_business_units` (
  `id` int(11) NOT NULL,
  `manager_id` int(11) NOT NULL,
  `business_unit_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `manager_business_units`
--

INSERT INTO `manager_business_units` (`id`, `manager_id`, `business_unit_id`) VALUES
(1, 4, 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','manager') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Alice Admin', 'alice.admin@example.com', '$2y$10$JPed9gulsX2Tvaj0yWi54e2rZA.uYbDHoG4U/skPYOUqG/JFh9Gsa', 'admin', '2025-05-20 11:45:33'),
(4, 'manager 2', 'manager@eval.com', '$2y$10$bpPWnbXcewRks4BqjdWMCODUI18zhYjrdwcnAj7FQ4MtqF.9r7cC2', 'manager', '2025-05-26 10:19:34');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activities`
--
ALTER TABLE `activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `business_unit_id` (`business_unit_id`);

--
-- Indexes for table `agents`
--
ALTER TABLE `agents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `activity_id` (`activity_id`);

--
-- Indexes for table `business_units`
--
ALTER TABLE `business_units`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `evaluations`
--
ALTER TABLE `evaluations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `agent_id` (`agent_id`),
  ADD KEY `template_id` (`template_id`),
  ADD KEY `evaluator_id` (`evaluator_id`),
  ADD KEY `activity_id` (`activity_id`);

--
-- Indexes for table `evaluation_criteria`
--
ALTER TABLE `evaluation_criteria`
  ADD PRIMARY KEY (`id`),
  ADD KEY `template_id` (`template_id`);

--
-- Indexes for table `evaluation_subcriteria`
--
ALTER TABLE `evaluation_subcriteria`
  ADD PRIMARY KEY (`id`),
  ADD KEY `criteria_id` (`criteria_id`);

--
-- Indexes for table `evaluation_subcriteria_results`
--
ALTER TABLE `evaluation_subcriteria_results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `evaluation_id` (`evaluation_id`),
  ADD KEY `subcriteria_id` (`subcriteria_id`);

--
-- Indexes for table `evaluation_templates`
--
ALTER TABLE `evaluation_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `activity_id` (`activity_id`);

--
-- Indexes for table `manager_business_units`
--
ALTER TABLE `manager_business_units`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `manager_id` (`manager_id`,`business_unit_id`),
  ADD KEY `business_unit_id` (`business_unit_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activities`
--
ALTER TABLE `activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `agents`
--
ALTER TABLE `agents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `business_units`
--
ALTER TABLE `business_units`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `evaluations`
--
ALTER TABLE `evaluations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `evaluation_criteria`
--
ALTER TABLE `evaluation_criteria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `evaluation_subcriteria`
--
ALTER TABLE `evaluation_subcriteria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `evaluation_subcriteria_results`
--
ALTER TABLE `evaluation_subcriteria_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- AUTO_INCREMENT for table `evaluation_templates`
--
ALTER TABLE `evaluation_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `manager_business_units`
--
ALTER TABLE `manager_business_units`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activities`
--
ALTER TABLE `activities`
  ADD CONSTRAINT `activities_ibfk_1` FOREIGN KEY (`business_unit_id`) REFERENCES `business_units` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `agents`
--
ALTER TABLE `agents`
  ADD CONSTRAINT `agents_ibfk_1` FOREIGN KEY (`activity_id`) REFERENCES `activities` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `evaluations`
--
ALTER TABLE `evaluations`
  ADD CONSTRAINT `evaluations_ibfk_1` FOREIGN KEY (`agent_id`) REFERENCES `agents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `evaluations_ibfk_2` FOREIGN KEY (`template_id`) REFERENCES `evaluation_templates` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `evaluations_ibfk_3` FOREIGN KEY (`evaluator_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `evaluations_ibfk_4` FOREIGN KEY (`activity_id`) REFERENCES `activities` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `evaluation_criteria`
--
ALTER TABLE `evaluation_criteria`
  ADD CONSTRAINT `evaluation_criteria_ibfk_1` FOREIGN KEY (`template_id`) REFERENCES `evaluation_templates` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `evaluation_subcriteria`
--
ALTER TABLE `evaluation_subcriteria`
  ADD CONSTRAINT `evaluation_subcriteria_ibfk_1` FOREIGN KEY (`criteria_id`) REFERENCES `evaluation_criteria` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `evaluation_subcriteria_results`
--
ALTER TABLE `evaluation_subcriteria_results`
  ADD CONSTRAINT `fk_eval_subcrit_results_evaluation` FOREIGN KEY (`evaluation_id`) REFERENCES `evaluations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_eval_subcrit_results_subcriteria` FOREIGN KEY (`subcriteria_id`) REFERENCES `evaluation_subcriteria` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `evaluation_templates`
--
ALTER TABLE `evaluation_templates`
  ADD CONSTRAINT `evaluation_templates_ibfk_1` FOREIGN KEY (`activity_id`) REFERENCES `activities` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `manager_business_units`
--
ALTER TABLE `manager_business_units`
  ADD CONSTRAINT `manager_business_units_ibfk_1` FOREIGN KEY (`manager_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `manager_business_units_ibfk_2` FOREIGN KEY (`business_unit_id`) REFERENCES `business_units` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
