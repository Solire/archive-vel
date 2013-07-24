<?php
/**
 * installation des critères
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

\Slrfw\FrontController::setApp('projet');
\Slrfw\FrontController::setApp('vel');
\Slrfw\FrontController::setApp('app');

\Slrfw\FrontController::init();

$db = \Slrfw\Registry::get('db');

/** Mettre script d'installation ici  **/

$query = 'CREATE TABLE IF NOT EXISTS `critere` (
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
CREATE TABLE IF NOT EXISTS `critere_option` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `id_critere` int(11) NOT NULL,
  `ordre` int(11) NOT NULL,
  `suppr` tinyint(1) NOT NULL,
  `date_modif` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_critere` (`id_critere`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';

$db->exec($query);

