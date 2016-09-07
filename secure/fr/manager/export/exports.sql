-- phpMyAdmin SQL Dump
-- version 2.10.0.1
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Jeu 21 Juin 2007 à 19:05
-- Version du serveur: 5.0.33
-- Version de PHP: 5.2.1

-- 
-- Base de données: `technico`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `exports`
-- 

CREATE TABLE `exports` (
  `id` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `partner_id` int(10) unsigned NOT NULL,
  `partner_name` varchar(255) NOT NULL,
  `nb_pdt` int(10) unsigned NOT NULL,
  `create_time` int(10) unsigned NOT NULL,
  `timestamp` int(10) unsigned NOT NULL,
  `generate_time` int(10) unsigned NOT NULL,
  `compulsory_fields` text NOT NULL,
  `facultative_fields` text NOT NULL,
  `id_parent` int(10) unsigned NOT NULL
) TYPE=MyISAM;

-- 
-- Contenu de la table `exports`
-- 


-- --------------------------------------------------------

-- 
-- Structure de la table `exports_partner`
-- 

CREATE TABLE `exports_partner` (
  `id` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `flow_type` varchar(255) NOT NULL,
  `compulsory_fields` text NOT NULL,
  `facultative_fields` text NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

-- 
-- Contenu de la table `exports_partner`
-- 

INSERT INTO `exports_partner` (`id`, `name`, `url`, `flow_type`, `compulsory_fields`, `facultative_fields`) VALUES 
(1024, 'leguide.com', 'http://www.leguide.com', 'xml', 'a:5:{s:9:"categorie";a:4:{s:4:"type";s:4:"edit";s:11:"source_type";s:2:"UI";s:7:"default";s:1:"-";s:11:"filter_type";s:10:"tree sep=>";}s:18:"identifiant_unique";a:7:{s:4:"type";s:4:"edit";s:11:"source_type";s:2:"DB";s:7:"default";s:1:"0";s:11:"filter_type";s:0:"";s:10:"table_name";s:8:"products";s:10:"field_name";s:4:"idTC";s:13:"id_field_name";s:2:"id";}s:5:"titre";a:7:{s:4:"type";s:4:"edit";s:11:"source_type";s:2:"DB";s:7:"default";s:18:"Produit sans titre";s:11:"filter_type";s:0:"";s:10:"table_name";s:11:"products_fr";s:10:"field_name";s:4:"name";s:13:"id_field_name";s:2:"id";}s:4:"prix";a:7:{s:4:"type";s:4:"edit";s:11:"source_type";s:2:"DB";s:7:"default";s:1:"0";s:11:"filter_type";s:3:"num";s:10:"table_name";s:8:"products";s:10:"field_name";s:5:"price";s:13:"id_field_name";s:2:"id";}s:11:"URL_produit";a:4:{s:4:"type";s:4:"edit";s:11:"source_type";s:2:"UI";s:7:"default";s:60:"http://www.techni-contact.com/logistique/produit-test-1.html";s:11:"filter_type";s:3:"url";}}', 'a:6:{s:11:"description";a:7:{s:4:"type";s:4:"text";s:11:"source_type";s:2:"DB";s:7:"default";s:1:"-";s:11:"filter_type";s:6:"no-tag";s:10:"table_name";s:11:"products_fr";s:10:"field_name";s:5:"descc";s:13:"id_field_name";s:2:"id";}s:15:"reference_model";a:7:{s:4:"type";s:4:"edit";s:11:"source_type";s:2:"DB";s:7:"default";s:1:"-";s:11:"filter_type";s:0:"";s:10:"table_name";s:8:"products";s:10:"field_name";s:11:"refSupplier";s:13:"id_field_name";s:2:"id";}s:9:"livraison";a:7:{s:4:"type";s:4:"edit";s:11:"source_type";s:2:"DB";s:7:"default";s:1:"-";s:11:"filter_type";s:0:"";s:10:"table_name";s:8:"products";s:10:"field_name";s:15:"delai_livraison";s:13:"id_field_name";s:2:"id";}s:9:"URL_image";a:4:{s:4:"type";s:4:"edit";s:11:"source_type";s:2:"UI";s:7:"default";s:1:"0";s:11:"filter_type";s:3:"url";}s:13:"disponibilite";a:8:{s:4:"type";s:4:"list";s:11:"source_type";s:2:"UI";s:7:"default";s:8:"en stock";s:11:"filter_type";s:0:"";s:7:"option1";s:8:"en stock";s:7:"option2";s:31:"en cours de réapprovisionnement";s:7:"option3";s:30:"disponible chez le fournisseur";s:7:"option4";s:14:"non disponible";}s:8:"garantie";a:4:{s:4:"type";s:4:"edit";s:11:"source_type";s:2:"UI";s:7:"default";s:4:"1 an";s:11:"filter_type";s:0:"";}}');

-- --------------------------------------------------------

-- 
-- Structure de la table `exports_products`
-- 

CREATE TABLE `exports_products` (
  `id_product` int(10) unsigned NOT NULL,
  `id_export` int(10) unsigned NOT NULL,
  `compulsory_fields` text NOT NULL,
  `facultative_fields` text NOT NULL
) TYPE=MyISAM;

-- 
-- Contenu de la table `exports_products`
-- 

