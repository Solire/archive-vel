<?php
/**
 * Affichage des blocs Région
 *
 * Ce bloc permet de choisir les régions dans lesquelles sera vendu le produit
 *
 * @package    Gabarit
 * @subpackage fieldset
 * @author     Adrien <aimbert@solire.fr>
 * @license    Solire http://www.solire.fr/
 */

namespace Vel\Model\Gabarit\Fieldset\Region;

/**
 * Affichage des blocs Région
 *
 * Ce bloc permet de choisir les régions dans lesquelles sera vendu le produit
 *
 * @package    Gabarit
 * @subpackage fieldset
 * @author     Adrien <aimbert@solire.fr>
 * @license    Solire http://www.solire.fr/
 */
class Region extends \Slrfw\Model\Gabarit\FieldSet\GabaritFieldSet
{
    /**
     * Chargement du fieldset
     *
     * @return void
     */
    public function start()
    {
        $db = \Slrfw\Registry::get('db');

        $path = \Slrfw\FrontController::search('config/sqlVel.ini', false);
        $config = new \Slrfw\Config($path);

        $query = 'SELECT r.id, r.nom '
               . 'FROM ' . $config->get('table', 'region') . ' r '
               . 'INNER JOIN ' . $config->get('table', 'regionTaxe') . ' t '
               . ' ON t.id_region = r.id '
               . '  AND t.suppr = 0 '
               . 'WHERE r.suppr = 0 '
               . 'ORDER BY r.nom ASC ';
        $this->regions = $db->query($query)->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_ASSOC);

        if (empty($this->regions)) {
            $this->display = false;
            return;
        }

        parent::start();
    }
}

