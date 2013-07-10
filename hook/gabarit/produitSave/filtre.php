<?php
/**
 * Enregistrement des filtres
 *
 * @package    Vel
 * @subpackage Hook
 * @author     dev <dev@solire.fr>
 * @license    Solire http://www.solire.fr/
 */

namespace Vel\Hook\Gabarit\ProduitSave;

/**
 * Enregistrement des filtres
 *
 * @package    Vel
 * @subpackage Hook
 * @author     dev <dev@solire.fr>
 * @license    Solire http://www.solire.fr/
 */
class Filtre
{
    /**
     * Lien vers le fichier de configuration des filtres
     */
    const CONFIG_PATH = 'config/filtre.ini';

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
        $db->exec($query);

        if (isset($donnees['filtre'])) {
            foreach ($donnees['filtre'] as $filtreId) {
                $query  = 'INSERT INTO ' . $config->get('table', 'filtres') . ' SET '
                        . ' id_gab_page = ' . $page->getMeta('id') . ','
                        . ' id_filtre = ' . $filtreId;
                $db->exec($query);
            }
        }

//        $query  = 'DELETE FROM ' . $config->get('table', 'filtresOptions') . ' '
//                . ' WHERE id_gab_page = ' . $page->getMeta('id');
//        $db->exec($query);
    }
}