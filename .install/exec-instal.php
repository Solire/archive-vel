<?php
/**
 * Installation des tables du panier
 *
 * @package    Vel
 * @subpackage Install
 * @author     Adrien <aimbert@solire.fr>
 * @license    CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */

set_include_path(
    get_include_path()
    . PATH_SEPARATOR . realpath(pathinfo(__FILE__, PATHINFO_DIRNAME) . '/../../')
);

require 'slrfw/init.php';

\Slrfw\FrontController::init();

$db = \Slrfw\Registry::get('db');

/** Mettre script d'installation ici  **/

$query = 'CREATE TABLE IF NOT EXISTS `boutique_panier` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cle` char(32) NOT NULL,
  `hit` timestamp NOT NULL DEFAULT "0000-00-00 00:00:00" ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;';

$db->exec($query);

$query = 'CREATE TABLE IF NOT EXISTS `boutique_panier_ligne` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_panier` int(11) unsigned NOT NULL,
  `id_reference` int(11) unsigned NOT NULL,
  `quantite` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_commande` (`id_panier`),
  KEY `id_reference` (`id_reference`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';

$db->exec($query);

