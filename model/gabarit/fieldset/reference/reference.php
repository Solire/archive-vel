<?php
/**
 * Affichage des blocs Références
 *
 * @package    Vel
 * @subpackage Gabarit
 * @author     Adrien <aimbert@solire.fr>
 * @license    CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */

namespace Vel\Model\Gabarit\Fieldset\Reference;

/**
 * Affichage des blocs Références
 *
 * @package    Vel
 * @subpackage Gabarit
 * @author     Adrien <aimbert@solire.fr>
 * @license    CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */
class Reference extends \Slrfw\Model\Gabarit\FieldSet\GabaritFieldSet
{
    /**
     * Chargement du fieldset
     *
     * @return void
     */
    public function start()
    {
        $db = \Slrfw\Registry::get('db');

        if ($this->idGabPage === 0) {
            $this->display = false;
            return;
        }

        $path = \Slrfw\FrontController::search('config/sqlVel.ini', false);
        $config = new \Slrfw\Config($path);

        $this->idPrixHt = $config->get('champ', 'prixHt');

        $this->colonnes = array();
        $this->colonnes[] = array(
            'name' => 'code',
            'nameSql' => 'code',
            'type' => 'data',
            'id' => 'code',
        );

        $this->colonnes[] = array(
            'name' => 'EAN',
            'nameSql' => 'ean',
            'type' => 'data',
            'id' => 'ean',
        );


        $this->start = count($this->colonnes);

        /** Chargement des critères **/
        $query = 'SELECT * '
               . 'FROM ' . $config->get('table', 'produitCritere') . ' crit '
               . 'INNER JOIN ' . $config->get('table', 'critere') . ' c '
               . ' ON c.id = crit.id_critere '
               . '  AND c.suppr = 0 '
               . 'WHERE crit.id_gab_page = ' . $this->idGabPage . ' '
               . ' AND crit.suppr = 0 '
               . 'ORDER BY crit.ordre ';
        $this->criteres = $db->query($query)->fetchAll();

        foreach ($this->criteres as $crit) {
            $select = null;
            if ($crit['libre'] == 0) {
                $query = 'SELECT id, name '
                       . 'FROM ' . $config->get('table', 'critereOption') . ' co '
                       . 'WHERE id_critere = ' . $crit['id_critere'] . ' ';
                $select = $db->query($query)->fetchAll();
            }

            $this->colonnes[] = array(
                'name' => $crit['name'],
                'type' => 'criteres',
                'select' => $select,
                'id' => $crit['id_critere'],
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

        if (empty($this->regions)) {
            $this->display = false;
            return;
        }

        foreach ($this->regions as $region) {
            $query = 'SELECT * '
                   . 'FROM ' . $config->get('table', 'regionTaxe') . ' '
                   . 'WHERE id_region = ' . $region['id_region'] . ' ';
            $select = $db->query($query)->fetchAll();

            if (empty($select)) {
                continue;
            }

            $this->colonnes[] = array(
                'name' => $region['nom'],
                'type' => 'prix',
                'id' => $region['id_region'],
                'select' => $select,
            );
        }

        parent::start();
    }
}

