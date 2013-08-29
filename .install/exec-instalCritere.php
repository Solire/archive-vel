<?php
/**
 * installation des critères
 *
 * @package    Vel
 * @subpackage Install
 * @author     Adrien <aimbert@solire.fr>
 * @license    CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */

require_once pathinfo(__FILE__, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR . 'exec-template.php';

/** Mettre script d'installation ici  **/

$query = 'CREATE TABLE IF NOT EXISTS `' . $confSql->get('table', 'critere') . '` (
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

$query = 'CREATE TABLE IF NOT EXISTS `' . $confSql->get('table', 'critereOption') . '` (
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

