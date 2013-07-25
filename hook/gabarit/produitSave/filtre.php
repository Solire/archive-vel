<?php
/**
 * Enregistrement des critères
 *
 * @package    Vel
 * @subpackage Hook
 * @author     Adrien <aimbert@solire.fr>
 * @license    CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */

namespace Vel\Hook\Gabarit\ProduitSave;

/**
 * Enregistrement des critères
 *
 * @package    Vel
 * @subpackage Hook
 * @author     Adrien <aimbert@solire.fr>
 * @license    CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */
class Filtre implements \Slrfw\HookInterface
{
    /**
     * Lien vers le fichier de configuration des critères
     */
    const CONFIG_PATH = 'config/critere.ini';

    /**
    * Enregistrement des filtres
    *
    * @param \Slrfw\Hook $env
    *
    * @return void
    */
    public function run($env)
    {
        $donnees = $env->data;
        $page = $env->page;

        $db = \Slrfw\Registry::get('db');

        $path = \Slrfw\FrontController::search(self::CONFIG_PATH, false);
        $config = new \Slrfw\Config($path);
        unset($path);
        /** Filtres applicables au Gabarit */
        $query  = 'DELETE FROM ' . $config->get('table', 'filtres') . ' '
                . 'WHERE id_gab_page = ' . $page->getMeta('id') . ' ';
//        $db->exec($query);

        if (isset($donnees['filtre'])) {
            foreach ($donnees['filtre'] as $filtreId) {
                $query  = 'INSERT INTO ' . $config->get('table', 'filtres') . ' SET '
                        . ' id_gab_page = ' . $page->getMeta('id') . ','
                        . ' id_filtre = ' . $filtreId;
//                $db->exec($query);
            }
        }

//        $query  = 'DELETE FROM ' . $config->get('table', 'filtresOptions') . ' '
//                . ' WHERE id_gab_page = ' . $page->getMeta('id');
//        $db->exec($query);
    }
}

