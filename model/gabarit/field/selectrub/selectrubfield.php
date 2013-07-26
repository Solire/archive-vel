<?php
/**
 *
 *
 * @package    Vel
 * @subpackage Gabarit
 * @author     Adrien <aimbert@solire.fr>
 * @license    CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */

namespace Vel\Model\Gabarit\Field\SelectRub;

/**
 *
 *
 * @package    Vel
 * @subpackage Gabarit
 * @author     Adrien <aimbert@solire.fr>
 * @license    CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */

class SelectRubField extends \Slrfw\Model\Gabarit\Field\GabaritField
{
    /**
     *
     * @return void
     */
    public function start()
    {
        $db = \Slrfw\Registry::get('db');
        $query = 'SELECT id_parent, gab_page.* '
               . 'FROM gab_page '
               . 'WHERE id_gabarit = ' . $this->params['RUB_ID'] . ' '
               . ' AND suppr = 0 '
               . 'ORDER BY id_parent ASC, titre ASC ';
        $this->rub = $db->query($query)->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_ASSOC);
    }
}

