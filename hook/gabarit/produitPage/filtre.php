<?php
/**
 * Chargement des filtres
 *
 * @package    Vel
 * @subpackage Hook
 * @author     dev <dev@solire.fr>
 * @license    Solire http://www.solire.fr/
 */

namespace Vel\Hook\Gabarit\ProduitPage;

/**
 * Chargement des filtres
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
    * Chargement des filtres
    *
    * @param \Slrfw\Hook $env
    *
    * @return void
    */
    public function run($env)
    {
        $idGabPage = $env->idGabPage;
        $page = $env->page;

        $db = \Slrfw\Registry::get('db');

        $path = \Slrfw\FrontController::search(self::CONFIG_PATH, false);
        $config = new \Slrfw\Config($path);
        unset($path);

        /** Récupération des options des filtres */
        $blocProduit = $page->getBlocs('criteres');
        $query  = 'SELECT fo.id_filtre, o.id_option, fo.name value '
                . 'FROM ' . $config->get('table', 'filtresOptions') . ' o '
                . 'INNER JOIN filtre_option fo '
                . ' ON o.id_option = fo.id '
                . '  AND fo.suppr = 0 '
                . 'WHERE o.id_gab_page = ' . $idGabPage . ' '
                . ' AND o.suppr = 0 '
                . 'ORDER BY o.ordre ';
        $values['filtres'] = $db->query($query)->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_ASSOC);

        $blocProduit->setValues($values);
    }
}