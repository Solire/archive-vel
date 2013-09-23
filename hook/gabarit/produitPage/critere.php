<?php
/**
 * Chargement des critères
 *
 * @package    Vel
 * @subpackage Hook
 * @author     Adrien <aimbert@solire.fr>
 * @license    CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */

namespace Vel\Hook\Gabarit\ProduitPage;

/**
 * Chargement des critères
 *
 * @package    Vel
 * @subpackage Hook
 * @author     Adrien <aimbert@solire.fr>
 * @license    CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */
class Critere implements \Slrfw\HookInterface
{
    /**
     * Lien vers le fichier de configuration des critères
     */
    const CONFIG_PATH = 'config/critere.ini';

    /**
    * Chargement des critères
    *
    * @param \Slrfw\Hook $env Données d'environnement
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
        $values = $blocProduit->getValues();
        $ids = array();
        foreach ($values as $value) {
            if (empty($value['id_critere']) || $value['id_critere'] == 0) {
                continue;
            }
            $ids[] = $value['id_critere'];
        }

        if (empty($ids)) {
            $blocProduit->setValues(array());
            return;
        }

        $query = 'SELECT * '
               . 'FROM critere '
               . 'WHERE id IN (' . implode(', ', $ids) . ') ';
        $criteres = $db->query($query)->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_ASSOC);
        foreach ($values as $key => $value) {
            if (!isset($criteres[$value['id_critere']][0])) {
                continue;
            }
            $values[$key] += $criteres[$value['id_critere']][0];
        }
        $blocProduit->setValues($values);
    }
}

