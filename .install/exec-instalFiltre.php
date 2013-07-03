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

\Slrfw\FrontController::setApp('projet');
\Slrfw\FrontController::setApp('vel');
\Slrfw\FrontController::setApp('app');

\Slrfw\FrontController::init();

$db = \Slrfw\Registry::get('db');

/** Mettre script d'installation ici  **/

$query = 'CREATE TABLE IF NOT EXISTS `filtre` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `libre` tinyint(1) NOT NULL,
  `ordre` int(11) NOT NULL,
  `suppr` tinyint(1) NOT NULL,
  `date_modif` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
';

$db->exec($query);

$query = '
CREATE TABLE IF NOT EXISTS `filtre_option` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `id_filtre` int(11) NOT NULL,
  `ordre` int(11) NOT NULL,
  `suppr` tinyint(1) NOT NULL,
  `date_modif` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_filtre` (`id_filtre`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';

$db->exec($query);

$path = \Slrfw\FrontController::search('config/filtre.ini', false);
$config = new \Slrfw\Config($path);
unset($path);

$query = '
CREATE TABLE IF NOT EXISTS `' . $config->get('table', 'filtres') . '` (
  `id_gab_page` int(11) NOT NULL,
  `id_filtre` int(11) NOT NULL,
  PRIMARY KEY (`id_gab_page`,`id_filtre`),
  KEY `id_gab_page` (`id_gab_page`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';

$db->exec($query);


$query = '
CREATE TABLE IF NOT EXISTS `' . $config->get('table', 'filtresOptions') . '` (
  `id_gab_page` int(11) NOT NULL,
  `id_reference` int(11) NOT NULL,
  `id_filtre` int(11) NOT NULL,
  `id_option` int(11) NOT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id_reference`,`id_option`),
  KEY `index produit` (`id_gab_page`),
  KEY `index reference` (`id_reference`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';

$db->exec($query);
