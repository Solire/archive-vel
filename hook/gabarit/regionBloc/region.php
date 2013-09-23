<?php
/**
 * Enregistrement des données du bloc région
 *
 * @package    Vel
 * @subpackage Hook
 * @author     Adrien <aimbert@solire.fr>
 * @license    CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */

namespace Vel\Hook\Gabarit\RegionBloc;

/**
 * Enregistrement des données du bloc région
 *
 * @package    Vel
 * @subpackage Hook
 * @author     Adrien <aimbert@solire.fr>
 * @license    CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */
class Region implements \Slrfw\HookInterface
{
    /**
    * Enregistrement des données région
    *
    * @param \Slrfw\Hook $env Données d'environnement
    *
    * @return void
    */
    public function run($env)
    {
        $donnees = $env->data;

        if (!isset($donnees['region'])) {
            return;
        }

        $db = \Slrfw\Registry::get('db');

        $path = \Slrfw\FrontController::search('config/sqlVel.ini', false);
        $config = new \Slrfw\Config($path);
        unset($path);

        $query  = 'SELECT id, nom '
                . 'FROM ' . $config->get('table', 'region') . ' r '
                . 'WHERE suppr = 0 ';
        $regions = $db->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        $regionIds = array();
        foreach ($regions as $region) {
            $regionIds[] = $region['id'];
        }
        unset($region, $regions);

        $ids = array();
        $ordre = 1;
        foreach ($donnees['region'] as $idRegion) {
            if (empty($idRegion)) {
                continue;
            }

            if (!in_array($idRegion, $regionIds)) {
                continue;
            }

            $query  = 'INSERT INTO ' . $env->tableName . ' '
                    . 'SET '
                    . ' id_gab_page = ' . $env->idGabPage . ', '
                    . ' id_version = ' . $env->idVersion . ', '
                    . ' ordre = ' . $ordre++ . ', '
                    . ' visible = 1, '
                    . ' id_region = ' . $idRegion . ' ';
            $db->exec($query);
            $ids[] = $db->lastInsertId();
        }

        $query  = 'UPDATE ' . $env->tableName . ' SET '
                . '  suppr = NOW() '
                . 'WHERE suppr = 0 '
                . ' AND id_gab_page = ' . $env->idGabPage . ' '
                . ' AND id NOT IN (' . implode(', ', $ids) . ') ';
        $db->query($query);
    }
}

