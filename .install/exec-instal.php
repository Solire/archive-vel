<?php
/**
 * Installation des tables du panier
 *
 * @package    Vel
 * @subpackage Install
 * @author     Adrien <aimbert@solire.fr>
 * @license    CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */

require_once pathinfo(__FILE__, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR . 'exec-template.php';

/** Mettre script d'installation ici  **/

$dirPath = pathinfo(__FILE__, PATHINFO_DIRNAME);
$dir = opendir($dirPath);
$me = pathinfo(__FILE__, PATHINFO_BASENAME);
while ($file = @readdir($dir)) {
    if ($file == '.' || $file == '..' || $file == $me) {
        continue;
    }
    if (is_dir($dirPath . DS . $file)) {
        continue;
    }
    if (preg_match('/^exec-instal/', $file) == false) {
        continue;
    }

    try {
        include $dirPath . DS . $file;
        echo str_replace('exec-install', '', $file) . "\t\t\033[32m" . '[OK]' . "\033[00m\r\n";
    } catch (\Exception $exc) {
        echo str_replace('exec-install', '', $file) . "\t\t\033[31m" . '[KO]'
            . "\033[00m" . $exc->getMessage() . "\r\n";
    }


}
closedir($dir);

