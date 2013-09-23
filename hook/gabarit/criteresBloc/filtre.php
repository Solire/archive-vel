<?php
/**
 * Chargement des filtres
 *
 * @package    Vel
 * @subpackage Hook
 * @author     Adrien <aimbert@solire.fr>
 * @license    CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */

namespace Vel\Hook\Gabarit\CriteresBloc;

/**
 * Chargement des filtres
 *
 * @package    Vel
 * @subpackage Hook
 * @author     Adrien <aimbert@solire.fr>
 * @license    CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */
class Filtre implements \Slrfw\HookInterface
{
    /**
     * Lien vers le fichier de configuration des filtres
     */
    const CONFIG_PATH = 'config/filtre.ini';

    /**
    * Chargement des filtres
    *
    * @param \Slrfw\Hook $env Données d'environnement
    *
    * @return void
    */
    public function run($env)
    {
        $donnees = $env->data;

        if (!isset($donnees['option']) || !isset($donnees['filtre'])) {
            return;
        }

        $db = \Slrfw\Registry::get('db');
        $path = \Slrfw\FrontController::search(self::CONFIG_PATH, false);
        $config = new \Slrfw\Config($path);
        unset($path);

        $query  = 'SELECT id, libre '
                . 'FROM filtre '
                . 'WHERE id IN (' . implode(',', $donnees['filtre']) . ') ';
        $filtres = $db->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        $filtreIds = array();
        foreach ($filtres as $filtre) {
            $filtreIds[] = $filtre['id'];
        }

        $ids = array();
        $ordre = 1;
        foreach ($donnees['option'] as $filtreId => $options) {
            if (empty($options)) {
                continue;
            }

            if (!in_array($filtreId, $filtreIds)) {
                continue;
            }

            foreach ($options as $optionId => $value) {
                if (empty($value)) {
                    continue;
                }
                if (empty($optionId)) {
                    $query = 'INSERT INTO filtre_option SET '
                           . ' name = ' . $db->quote($value) . ', '
                           . ' id_filtre = ' . $filtreId . ' ';
                    $db->exec($query);
                    $optionId = $db->lastInsertId();
                }

                $query  = 'INSERT INTO ' . $env->tableName . ' '
                        . 'SET '
                        . ' id_gab_page = ' . $env->idGabPage . ', '
                        . ' id_version = ' . $env->idVersion . ', '
                        . ' ordre = ' . $ordre++ . ', '
                        . ' visible = 1, '
                        . ' id_filtre = ' . $filtreId . ', '
                        . ' id_option = ' . $optionId . ' ';
                $db->exec($query);
                $ids[] = $db->lastInsertId();
            }
        }

        $query  = 'UPDATE ' . $env->tableName . ' SET suppr = NOW()'
                . 'WHERE suppr = 0 '
                . ' AND id_gab_page = ' . $env->idGabPage . ' '
                . ' AND id NOT IN (' . implode(', ', $ids) . ')';
        $db->query($query);
    }
}

