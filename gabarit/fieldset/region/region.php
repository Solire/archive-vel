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

namespace Vel\Gabarit\Fieldset\Region;

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

        $query = 'SELECT id, nom '
               . 'FROM region '
               . 'WHERE suppr = 0 '
               . 'ORDER BY nom ASC ';
        $this->regions = $db->query($query)->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_ASSOC);

        if (empty($this->regions)) {
            $this->display = false;
            return;
        }

        parent::start();
    }
}

