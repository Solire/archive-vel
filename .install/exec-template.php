<?php
/**
 * Template simple de script d'installation
 *
 * @package    Slrfw
 * @subpackage Install
 * @author     Adrien <aimbert@solire.fr>
 * @license    Solire http://www.solire.fr/
 */

set_include_path(
    get_include_path()
    . PATH_SEPARATOR . realpath(pathinfo(__FILE__, PATHINFO_DIRNAME) . '/../../')
);

require 'slrfw/init.php';

\Slrfw\FrontController::init();

$db = \Slrfw\Registry::get('db');

/** Mettre script d'installation ici  **/

$query = 'CREATE TABLE IF NOT EXISTS `boutique_commande` ('
       . ' `id` int(11) NOT NULL AUTO_INCREMENT, '
       . ' `etat` int(10) unsigned NOT NULL, '
       . ' `reference` varchar(31) COLLATE utf8_unicode_ci NOT NULL DEFAULT "", '
       . ' `id_client` int(11) NOT NULL, '
       . ' `id_adresse_livraison` int(11) NOT NULL, '
       . ' `id_adresse_facturation` int(11) NOT NULL, '
       . ' `mode_livraison` varchar(10) COLLATE utf8_unicode_ci NOT NULL, '
       . ' `total_ht` float(10,4) NOT NULL, '
       . ' `total_ttc` float(8,2) NOT NULL, '
       . ' `port` float(8,2) NOT NULL, '
       . ' `total` float(8,2) NOT NULL, '
       . ' `bon_livraison` tinyint(1) NOT NULL, '
       . ' `facture` tinyint(1) NOT NULL, '
       . ' `mode_reg` tinyint(7) NOT NULL, '
       . ' `date` datetime NOT NULL, '
       . ' `date_reg` datetime NOT NULL, '
       . ' `adresse_ip` varchar(16) COLLATE utf8_unicode_ci NOT NULL, '
       . 'PRIMARY KEY (`id`) '
       . ') ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci';