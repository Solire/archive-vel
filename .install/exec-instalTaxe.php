<?php
/**
 * installation des tables de gestion des taxes
 *
 * @package    Vel
 * @subpackage Install
 * @author     Adrien <aimbert@solire.fr>
 * @license    CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */

if (!defined('MULTIPLE')) {
    set_include_path(
        get_include_path()
        . PATH_SEPARATOR . realpath(pathinfo(__FILE__, PATHINFO_DIRNAME) . '/../../')
    );

    include 'slrfw/init.php';

    \Slrfw\FrontController::init();

    $db = \Slrfw\Registry::get('db');
}

/** Mettre script d'installation ici  **/
$query = 'CREATE TABLE IF NOT EXISTS `region` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `suppr` tinyint(4) NOT NULL,
  `ordre` int(11) NOT NULL,
  `date_modif` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';

$db->exec($query);

$query = 'CREATE TABLE IF NOT EXISTS `taxe` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_region` int(11) NOT NULL,
  `valeur` float NOT NULL,
  `suppr` tinyint(4) NOT NULL,
  `date_modif` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';

$db->exec($query);

