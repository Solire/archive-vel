<?php
/**
 * installation des tables de gestion des taxes
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

$query = 'CREATE TABLE region (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
    nom VARCHAR( 20 ) NOT NULL
) ENGINE = MYISAM ;';

$db->exec($query);

$query = 'CREATE TABLE lyon_labo.taxe (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
    id_region INT NOT NULL ,
    valeur FLOAT NOT NULL ,
    suppr TINYINT NOT NULL ,
    date_modif TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE = MYISAM ;';

$db->exec($query);

