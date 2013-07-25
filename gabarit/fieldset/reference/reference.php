<?php
/**
 * Affichage des blocs Références
 *
 * @package    Gabarit
 * @subpackage fieldset
 * @author     Adrien <aimbert@solire.fr>
 * @license    Solire http://www.solire.fr/
 */

namespace Vel\Gabarit\Fieldset\Reference;

/**
 * Affichage des blocs Références
 *
 * @package    Gabarit
 * @subpackage fieldset
 * @author     Adrien <aimbert@solire.fr>
 * @license    Solire http://www.solire.fr/
 */
class Reference extends \Slrfw\Model\Gabarit\FieldSet\GabaritFieldSet
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

        $path = \Slrfw\FrontController::search('config/sqlVel.ini', false);
        $config = new \Slrfw\Config($path);

        /** Chargement des critères **/
        $query = 'SELECT * '
               . 'FROM ' . $config->get('table', 'produitCritere') . ' crit '
               . 'INNER JOIN ' . $config->get('table', 'critere') . ' c '
               . ' ON c.id = crit.id_critere '
               . '  AND c.suppr = 0 '
               . 'WHERE crit.id_gab_page = ' . $this->idGabPage . ' '
               . ' AND crit.suppr = 0 ';
        $this->criteres = $db->query($query)->fetchAll();

        $colonnes = array();
        foreach ($this->criteres as $crit) {
            $colonnes[] = array(
                'name' => $crit['name'],
                'type' => 'crit',
                'id' => 'id_critere',
            );
        }

        /** Chargement des régions **/
        $query = 'SELECT * '
               . 'FROM ' . $config->get('table', 'produitRegion') . ' reg '
               . 'INNER JOIN ' . $config->get('table', 'region') . ' r '
               . ' ON r.id = reg.id_region '
               . '  AND r.suppr = 0 '
               . 'WHERE reg.id_gab_page = ' . $this->idGabPage . ' '
               . ' AND reg.suppr = 0 ';
        $this->regions = $db->query($query)->fetchAll();

        parent::start();
    }
}

