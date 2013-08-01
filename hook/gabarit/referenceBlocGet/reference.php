<?php
/**
 * Chargement des données du bloc référence
 *
 * @package    Vel
 * @subpackage Hook
 * @author     Adrien <aimbert@solire.fr>
 * @license    CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */

namespace Vel\Hook\Gabarit\ReferenceBlocGet;

/**
 * Chargement des données du bloc référence
 *
 * @package    Vel
 * @subpackage Hook
 * @author     Adrien <aimbert@solire.fr>
 * @license    CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */
class Reference implements \Slrfw\HookInterface
{
    /**
    * Chargement des données référence
    *
    * @param \Slrfw\Hook $env
    *
    * @return void
    */
    public function run($env)
    {
        $path = \Slrfw\FrontController::search('config/sqlVel.ini', false);
        $config = new \Slrfw\Config($path);
        unset($path);

        $db = \Slrfw\Registry::get('db');

        $values = $env->values;

        foreach ($env->values as $key => $value) {
            /** Chargement des critères **/
            $query = 'SELECT * '
                   . 'FROM ' . $config->get('table', 'referenceCritere') . ' rc '
                   . 'INNER JOIN ' . $config->get('table', 'critereOption') . ' co '
                   . ' ON co.id = rc.id_critere_option '
                   . '  AND co.suppr = 0 '
                   . 'WHERE rc.id_bloc = ' . $value['id'] . ' '
                   . ' AND rc.suppr = 0 ';
            $crit = $db->query($query)->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($crit as $val) {
                $values[$key]['criteres'][$val['id_critere']] = $val;
            }

            /** Chargement des régions **/
            $query = 'SELECT *, prix_ttc name '
                   . 'FROM ' . $config->get('table', 'referenceRegion') . ' rr '
                   . 'INNER JOIN ' . $config->get('table', 'region') . ' r '
                   . ' ON r.id = rr.id_region '
                   . '  AND r.suppr = 0 '
                   . 'WHERE rr.id_bloc = ' . $value['id'] . ' '
                   . ' AND rr.suppr = 0 ';
            $region = $db->query($query)->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($region as $val) {
                $values[$key]['prix'][$val['id_region']] = $val;
            }
        }
        $env->values = $values;
     }
}

