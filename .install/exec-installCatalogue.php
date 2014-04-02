<?php
/**
 * Template simple de script d'installation
 *
 * @package    Vel
 * @subpackage Install
 * @author     Adrien <aimbert@solire.fr>
 * @license    CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */

require_once pathinfo(__FILE__, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR . 'exec-init.php';

$query  = 'CREATE TABLE IF NOT EXISTS `' . $confSql->get('global', 'prefix') . '_element_commun` (
  `id_gab_page` int(11) NOT NULL,
  `id_version` tinyint(1) NOT NULL,
  PRIMARY KEY (`id_gab_page`,`id_version`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';
$db->exec($query);

$query  = 'CREATE TABLE IF NOT EXISTS `' . $confSql->get('table', 'produit') . '` (
  `id_gab_page` int(11) NOT NULL,
  `id_version` tinyint(1) NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `prix_ht` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `disponible` tinyint(1) NOT NULL,
  `top` tinyint(4) NOT NULL,
  PRIMARY KEY (`id_gab_page`,`id_version`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';
$db->exec($query);

$query  = 'CREATE TABLE IF NOT EXISTS `' . $confSql->get('table', 'produitCritere') . '` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_version` tinyint(1) NOT NULL,
  `id_gab_page` int(11) NOT NULL,
  `id_critere` int(11) NOT NULL,
  `ordre` int(11) NOT NULL,
  `visible` tinyint(1) NOT NULL,
  `suppr` datetime NOT NULL,
  PRIMARY KEY (`id`,`id_version`),
  KEY `id_version` (`id_version`,`id_gab_page`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;';
$db->exec($query);

$query  = 'CREATE TABLE IF NOT EXISTS `' . $confSql->get('table', 'produit') . '_illustration` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_version` tinyint(1) NOT NULL,
  `id_gab_page` int(11) NOT NULL,
  `ordre` int(11) NOT NULL,
  `visible` tinyint(1) NOT NULL,
  `suppr` datetime NOT NULL,
  `image` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`,`id_version`),
  KEY `id_version` (`id_version`,`id_gab_page`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;';
$db->exec($query);

$query  = 'CREATE TABLE IF NOT EXISTS `' . $confSql->get('table', 'reference') . '` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_version` tinyint(1) NOT NULL,
  `id_gab_page` int(11) NOT NULL,
  `ordre` int(11) NOT NULL,
  `visible` tinyint(1) NOT NULL,
  `suppr` datetime NOT NULL,
  `code` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ean` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`,`id_version`),
  KEY `id_version` (`id_version`,`id_gab_page`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;';
$db->exec($query);

$query  = 'CREATE TABLE IF NOT EXISTS `' . $confSql->get('table', 'referenceCritere') . '` (
  `id_bloc` int(11) NOT NULL,
  `id_critere` int(11) NOT NULL,
  `suppr` datetime NOT NULL,
  `id_critere_option` int(11) NOT NULL,
  PRIMARY KEY (`id_bloc`,`id_critere`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';
$db->exec($query);

$query  = 'CREATE TABLE IF NOT EXISTS `' . $confSql->get('table', 'referenceRegion') . '` (
  `id_bloc` int(11) NOT NULL,
  `id_region` int(11) NOT NULL,
  `suppr` datetime NOT NULL,
  `taxe` float NOT NULL,
  `prix_ttc` float NOT NULL,
  `prix_ht` float NOT NULL,
  PRIMARY KEY (`id_bloc`,`id_region`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';
$db->exec($query);

$query  = 'CREATE TABLE IF NOT EXISTS `' . $confSql->get('table', 'produitRegion') . '` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_version` tinyint(1) NOT NULL,
  `id_gab_page` int(11) NOT NULL,
  `ordre` int(11) NOT NULL,
  `visible` tinyint(1) NOT NULL,
  `suppr` datetime NOT NULL,
  `id_region` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`,`id_version`),
  KEY `id_version` (`id_version`,`id_gab_page`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;';
$db->exec($query);

$query  = 'CREATE TABLE IF NOT EXISTS `' . $confSql->get('table', 'produit') . '_rub_sec` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_version` tinyint(1) NOT NULL,
  `id_gab_page` int(11) NOT NULL,
  `ordre` int(11) NOT NULL,
  `visible` tinyint(1) NOT NULL,
  `suppr` datetime NOT NULL,
  `rubrique` int(11) NOT NULL,
  PRIMARY KEY (`id`,`id_version`),
  KEY `id_version` (`id_version`,`id_gab_page`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;';
$db->exec($query);

$query  = 'CREATE TABLE IF NOT EXISTS `' . $confSql->get('table', 'rubrique') . '` (
  `id_gab_page` int(11) NOT NULL,
  `id_version` tinyint(1) NOT NULL,
  PRIMARY KEY (`id_gab_page`,`id_version`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';
$db->exec($query);

/**
 * Api catalogue
 */
$query  = 'INSERT INTO `gab_api` (`name`, `label`) VALUES
("' . $confSql->get('global', 'prefix') . '", "Catalogue");';
$db->exec($query);
$apiId = $db->lastInsertId();

/**
 * Element commun catalogue
 */
$query  = 'INSERT INTO `gab_gabarit` (`id_parent`, `id_api`, `name`, `label`, `ordre`, `main`, `creable`, `deletable`, `sortable`, `make_hidden`, `editable`, `meta`, `301_editable`, `meta_titre`, `extension`, `view`) VALUES
(0, ' . $apiId . ', "element_commun", "Éléments communs", 0, 1, 0, 0, 0, 0, 1, 0, 1, 1, "", 1)';
$db->exec($query);


/**
 * Rubrique catalogue
 */
$query  = 'INSERT INTO `gab_gabarit` (`id_parent`, `id_api`, `name`, `label`, `ordre`, `main`, `creable`, `deletable`, `sortable`, `make_hidden`, `editable`, `meta`, `301_editable`, `meta_titre`, `extension`, `view`) VALUES
(0, ' . $apiId . ', "' . $confSql->get('global', 'rubrique') . '", "Rubrique", 1, 0, 0, 0, 0, 0, 0, 1, 0, 1, "/", 1)';
$db->exec($query);
$gabaritRubId = $db->lastInsertId();
$query  = 'UPDATE `gab_gabarit` SET'
        . ' id_parent = ' . $gabaritRubId
        . ' WHERE id = ' . $gabaritRubId;
$db->exec($query);


/**
 * Rubrique produit
 */
$query  = 'INSERT INTO `gab_gabarit` (`id_parent`, `id_api`, `name`, `label`, `ordre`, `main`, `creable`, `deletable`, `sortable`, `make_hidden`, `editable`, `meta`, `301_editable`, `meta_titre`, `extension`, `view`) VALUES
(' . $gabaritRubId . ', ' . $apiId . ', "' . $confSql->get('global', 'produit') . '", "Produit", 2, 0, 1, 1, 1, 1, 1, 1, 1, 1, ".html", 1);';
$db->exec($query);
$gabaritProdId = $db->lastInsertId();



$query = 'INSERT INTO `gab_bloc` (`id_gabarit`, `type`, `name`, `label`, `trad`, `ordre`) VALUES
(' . $gabaritProdId . ', "' . $confSql->get('global', 'critere') . '", "criteres", "Critères", 1, 1)';
$db->exec($query);
$blocCritereId = $db->lastInsertId();

$query = 'INSERT INTO `gab_champ` (`id_parent`, `type_parent`, `id_group`, `label`, `name`, `type`, `typedonnee`, `oblig`, `trad`, `visible`, `ordre`, `typesql`, `aide`, `filter_file`) VALUES
(' . $blocCritereId . ', "bloc", 0, "id_critère", "id_critere", "TEXT", "MIX", "fac", 0, 1, 0, "varchar(255) NOT NULL", "", "")';
$db->exec($query);



$query = 'INSERT INTO `gab_bloc` (`id_gabarit`, `type`, `name`, `label`, `trad`, `ordre`) VALUES
(' . $gabaritProdId . ', "", "illustration", "Illustration", 1, 2)';
$db->exec($query);
$blocIllustrationId = $db->lastInsertId();

$query = 'INSERT INTO `gab_champ` (`id_parent`, `type_parent`, `id_group`, `label`, `name`, `type`, `typedonnee`, `oblig`, `trad`, `visible`, `ordre`, `typesql`, `aide`, `filter_file`) VALUES
(' . $blocIllustrationId . ', "bloc", 0, "Image", "image", "FILE", "FILE", "fac", 1, 1, 0, "varchar(255) NOT NULL", "", "")';
$db->exec($query);



$query = 'INSERT INTO `gab_bloc` (`id_gabarit`, `type`, `name`, `label`, `trad`, `ordre`) VALUES
(' . $gabaritProdId . ', "", "rub_sec", "Rubriques_secondaires", 1, 0)';
$db->exec($query);
$blocRubSecId = $db->lastInsertId();

$query = 'INSERT INTO `gab_champ` (`id_parent`, `type_parent`, `id_group`, `label`, `name`, `type`, `typedonnee`, `oblig`, `trad`, `visible`, `ordre`, `typesql`, `aide`, `filter_file`) VALUES
(' . $blocRubSecId . ', "bloc", 0, "Rubrique", "rubrique", "SELECTRUB", "NUM", "fac", 0, 1, 0, "varchar(255) NOT NULL", "", "")';
$db->exec($query);



$query = 'INSERT INTO `gab_bloc` (`id_gabarit`, `type`, `name`, `label`, `trad`, `ordre`) VALUES
(' . $gabaritProdId . ', "region", "region", "Région", 1, 3)';
$db->exec($query);
$blocRegionId = $db->lastInsertId();

$query = 'INSERT INTO `gab_champ` (`id_parent`, `type_parent`, `id_group`, `label`, `name`, `type`, `typedonnee`, `oblig`, `trad`, `visible`, `ordre`, `typesql`, `aide`, `filter_file`) VALUES
(' . $blocRegionId . ', "bloc", 0, "région", "id_region", "JOIN", "MIX", "oblig", 0, 1, 0, "varchar(255) NOT NULL", "", "")';
$db->exec($query);



$query = 'INSERT INTO `gab_bloc` (`id_gabarit`, `type`, `name`, `label`, `trad`, `ordre`) VALUES
(' . $gabaritProdId . ', "reference", "reference", "Référence", 1, 4)';
$db->exec($query);
$blocRefId = $db->lastInsertId();

$query = 'INSERT INTO `gab_champ` (`id_parent`, `type_parent`, `id_group`, `label`, `name`, `type`, `typedonnee`, `oblig`, `trad`, `visible`, `ordre`, `typesql`, `aide`, `filter_file`) VALUES
(' . $blocRefId . ', "bloc", 0, "code", "code", "TEXT", "MIX", "oblig", 0, 1, 0, "varchar(255) NOT NULL", "", ""),
(' . $blocRefId . ', "bloc", 0, "ean", "ean", "TEXT", "MIX", "fac", 0, 1, 1, "varchar(255) NOT NULL", "", "")';
$db->exec($query);


$query = 'CREATE TABLE IF NOT EXISTS `catalogue_produit_reference_critere` (
  `id_bloc` int(11) NOT NULL,
  `id_critere` int(11) NOT NULL,
  `suppr` datetime NOT NULL,
  `id_critere_option` int(11) NOT NULL,
  PRIMARY KEY (`id_bloc`,`id_critere`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';
$db->exec($query);


$query = 'CREATE TABLE IF NOT EXISTS `catalogue_produit_reference_region` (
  `id_bloc` int(11) NOT NULL,
  `id_region` int(11) NOT NULL,
  `suppr` datetime NOT NULL,
  `taxe` float NOT NULL,
  `prix_ttc` float NOT NULL,
  `prix_ht` float NOT NULL,
  PRIMARY KEY (`id_bloc`,`id_region`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';
$db->exec($query);


