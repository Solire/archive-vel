<?php
/**
 * Affichage des blocs filtre
 *
 * @package    Gabarit
 * @subpackage fieldset
 * @author     Adrien <aimbert@solire.fr>
 * @license    Solire http://www.solire.fr/
 */

namespace Vel\Gabarit\Fieldset\Filtre;

/**
 * Affichage des blocs filtre
 *
 * @package    Gabarit
 * @subpackage fieldset
 * @author     Adrien <aimbert@solire.fr>
 * @license    Solire http://www.solire.fr/
 */
class Filtre extends \Slrfw\Model\Gabarit\FieldSet\GabaritFieldSet
{
    /**
     * Liste des filtres
     *
     * @var array
     */
    protected $filtres = array();

    /**
     * Chargement du fieldset
     *
     * @return void
     */
    public function start()
    {

        $db = \Slrfw\Registry::get('db');

        $query = 'SELECT id, name filtreName, ordre, libre '
               . 'FROM filtre '
               . 'WHERE suppr = 0 '
               . ' AND libre = 1 ';

        $filtres = $db->query($query)->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_ASSOC);


        $query = 'SELECT f.id, f.name filtreName, fo.id optionId, fo.name, '
               . '  fo.ordre, libre '
               . 'FROM filtre_option fo '
               . 'INNER JOIN filtre f '
               . ' ON f.id = fo.id_filtre '
               . '  AND f.suppr = 0 '
               . 'WHERE fo.suppr = 0 ';
        $foo = $db->query($query)->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_ASSOC);

        $this->filtres = $filtres + $foo;
        parent::start();
    }
}

