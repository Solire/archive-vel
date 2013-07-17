<?php
/**
 * Chargement des filtres
 *
 * @package    Vel
 * @subpackage Hook
 * @author     Adrien <aimbert@solire.fr>
 * @license    CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */

namespace Vel\Hook\Back\List102;

/**
 * Chargement des filtres
 *
 * @package    Vel
 * @subpackage Hook
 * @author     Adrien <aimbert@solire.fr>
 * @license    CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */
class Vel implements \Slrfw\HookInterface
{
    /**
    * Chargement des filtres
    *
    * @param \Slrfw\Hook $env
    *
    * @return void
    */
    public function run($env)
    {
        $db = \Slrfw\Registry::get('db');
        $query = 'SELECT p.*, CONCAT (COUNT(e.id) , " rubrique(s) & ", '
               . '  COUNT(e2.id), " produit(s)") aff_enfants, '
               . '  COUNT(e.id) + COUNT(e2.id) nbre_enfants '
               . 'FROM gab_page p '
               . 'LEFT JOIN gab_page e '
               . ' ON e.id_parent = p.id '
               . '  AND e.suppr = 0 '
               . '  AND e.id_gabarit = p.id_gabarit '
               . '  AND e.id_version = ' . $env->idVersion . ' '
               . 'LEFT JOIN gab_page e2 '
               . ' ON e2.id_parent = p.id '
               . '  AND e2.suppr = 0 '
               . '  AND e2.id_gabarit != p.id_gabarit '
               . '  AND e2.id_version = ' . $env->idVersion . ' '
               . 'WHERE p.suppr = 0 '
               . ' AND p.id_version = ' . $env->idVersion . ' '
               . ' AND p.id_api = ' . $env->idApi . ' ';

        if (isset($env->idParent)) {
            $query .= 'AND p.id_parent = ' . $env->idParent . ' ';
        } else {
            $query .= 'AND p.id_parent = ' . 0 . ' ';
        }

        $query .= 'AND p.id_gabarit IN (' . implode(', ', $env->gabaritsList) . ') '
                . 'GROUP BY p.id '
                . 'ORDER BY p.ordre ASC ';

        $metas = $db->query($query)->fetchAll(\PDO::FETCH_ASSOC);
        $version = $env->gabaritManager->getVersion($env->idVersion);

        $pages = array();
        foreach ($metas as $meta) {
            $page = new \Slrfw\Model\GabaritPage();
            $page->setMeta($meta);
            $page->setVersion($version);
            $pages[] = $page;
        }

        $env->list = $pages;
    }
}

