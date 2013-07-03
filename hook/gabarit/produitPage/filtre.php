<?php
/**
 * Chargement des filtres
 *
 * @package    Vel
 * @subpackage Hook
 * @author     dev <dev@solire.fr>
 * @license    Solire http://www.solire.fr/
 */

namespace Vel\Hook\Gabarit\ProduitPage;

/**
 * Chargement des filtres
 *
 * @package    Vel
 * @subpackage Hook
 * @author     dev <dev@solire.fr>
 * @license    Solire http://www.solire.fr/
 */
class Filtre
{
    /**
     * Lien vers le fichier de configuration des filtres
     */
    const CONFIG_PATH = 'config/filtre.ini';

    /**
    * Chargement des filtres
    *
    * @param \Slrfw\Hook $env
    *
    * @return void
    */
    public function run($env)
    {
        $idGabPage = $env->idGabPage;
        $page = $env->page;

        $db = \Slrfw\Registry::get('db');

        $path = \Slrfw\FrontController::search(self::CONFIG_PATH, false);
        $config = new \Slrfw\Config($path);
        unset($path);

        $path = \Slrfw\FrontController::search('config/panier.ini', false);
        $configPanier = new \Slrfw\Config($path);
        unset($path);

        /** Récupération des filtres */
        $query  = 'SELECT id_filtre '
                . 'FROM ' . $config->get('table', 'filtres') . ' '
                . 'WHERE id_gab_page = ' . $idGabPage . ' ';
        $filtres = $db->query($query)->fetchAll(\PDO::FETCH_COLUMN);
        $page->setValue('filtres', $filtres);

        /** Récupération des options des filtres */
        $blocProduit = $page->getBlocs('criteres');
        $query  = 'SELECT r.' . $configPanier->get('table', 'colId') . ', '
                . '  o.id_option, o.value '
                . 'FROM ' . $config->get('table', 'filtresOptions') . ' o '
                . 'JOIN ' . $configPanier->get('table', 'reference') . ' r '
                . 'ON r.' . $configPanier->get('table', 'colId') . ' = o.id_reference '
                . 'WHERE r.id_gab_page = ' . $idGabPage . ' ';
        $res = $db->query($query)->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_ASSOC);
        $refOptions = array();
        foreach ($res as $idRef => $refOpts) {
            $refOptions[$idRef] = array();
            foreach ($refOpts as $opt) {
                $refOptions[$idRef][$opt['id_option']] = $opt['value'];
            }
        }


        $query  = 'SELECT f.id,';
        if ($filtres) {
            $query  .= ' (f.id IN(' . implode(',', $filtres) . ')) actif,';
        } elseif ($idGabPage == 0) {
            $query  .= ' 1 actif,';
        } else {
            $query  .= ' 0 actif,';
        }
        $query .= ' f.libre, f.name filtre_name, o.*'
                . ' FROM filtre_option o'
                . ' JOIN filtre f ON o.id_filtre = f.id'
                . ' AND f.suppr = 0'
                . ' WHERE o.suppr = 0'
                . ' ORDER BY f.ordre, o.ordre';
        $options = $db->query($query)->fetchAll(
            \PDO::FETCH_GROUP | \PDO::FETCH_ASSOC);

        $values = $blocProduit->getValues();
        if (count($values) == 0 && $env->visible == false) {
            $values[] = array(
                'location' => 1,
                'achat' => 1,
            );
        }
        foreach ($values as $ii => $value) {
            $optionsTmp = $options;

            /** Bloc non vide avec des options selectionnés */
            if (isset($value['id']) && isset($refOptions[$value['id']])) {
                $optionsSel = $refOptions[$value['id']];

                foreach ($optionsTmp as $filtreId => $opts) {
                    foreach ($opts as $jj => $o) {
                        if (isset($optionsSel[$o['id']])) {
                            $optionsTmp[$filtreId][$jj]['value'] = $optionsSel[$o['id']];
                        }
                    }
                }
            }

            $values[$ii]['filtres'] = $optionsTmp;
        }

        $blocProduit->setValues($values);
    }
}