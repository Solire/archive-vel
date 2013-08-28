<?php
/**
 * Installation des tables du panier
 *
 * @package    Vel
 * @subpackage Install
 * @author     Adrien <aimbert@solire.fr>
 * @license    CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */

define('MULTIPLE', 'oui');

set_include_path(
    get_include_path()
    . PATH_SEPARATOR . realpath(pathinfo(__FILE__, PATHINFO_DIRNAME) . '/../../')
);

require 'slrfw/init.php';

\Slrfw\FrontController::init();

$db = \Slrfw\Registry::get('db');

/** Mettre script d'installation ici  **/

$dir = opendir(pathinfo(__FILE__, PATHINFO_DIRNAME));
$me = pathinfo(__FILE__, PATHINFO_BASENAME);
while ($file = @readdir($dir)) {
    if ($file == '.' || $file == '..' || $file == $me) {
        continue;
    }
    if (is_dir($dir . DS . $file)) {
        continue;
    }

    if (preg_match('#^exec-install#', $file) === false) {
        continue;
    }

    try {
        include $dir . DS . $file;
        echo str_replace('exec-install', '', $file) . "\t\t\033[32m" . '[OK]' . "\033[00m\r\n";
    } catch (\Exception $exc) {
        echo str_replace('exec-install', '', $file) . "\t\t\033[31m" . '[KO]'
            . "\033[00m" . $exc->getMessage() . "\r\n";
    }


}
closedir($dir);

