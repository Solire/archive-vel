<?php
/**
 * Affichage des blocs Critères
 *
 * @package    Gabarit
 * @subpackage fieldset
 * @author     Adrien <aimbert@solire.fr>
 * @license    Solire http://www.solire.fr/
 */

namespace Vel\Model\Gabarit\Fieldset\Critere;

/**
 * Affichage des blocs Critères
 *
 * @package    Gabarit
 * @subpackage fieldset
 * @author     Adrien <aimbert@solire.fr>
 * @license    Solire http://www.solire.fr/
 */
class Critere extends \Slrfw\Model\Gabarit\FieldSet\GabaritFieldSet
{
    /**
     * Liste des Critères
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

        $query = 'SELECT * '
               . 'FROM critere '
               . 'WHERE suppr = 0 '
               . 'ORDER BY name ASC ';

        $criteres = $db->query($query)->fetchAll();

        if (empty($criteres)) {
            $this->display = false;
            return;
        }


        $champs = $this->gabarit->getChamps();
        $this->idChamp = $champs[0]['id'];
        unset($champs);

        $this->criteres = $criteres;
        parent::start();
    }
}

