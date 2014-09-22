<?php
/**
 * Datatable d'affichage des produits
 *
 * @package    Vel
 * @subpackage Back
 * @author     Adrien <aimbert@solire.fr>
 * @license    CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */

namespace Vel\Back\Datatable;


/**
 * Datatable d'affichage des produits
 *
 * @package    Vel
 * @subpackage Back
 * @author     Adrien <aimbert@solire.fr>
 * @license    CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */
class Produits extends \Slrfw\Datatable\Datatable
{

    /**
     * Liste des gabarits
     *
     * @var array
     */
    protected $_gabarits;

    /**
     * Liste des versions
     *
     * @var array
     */
    protected $_versions;

    /**
     * versions courantes
     *
     * @var array
     */
    protected $_currentVersion;

    /**
     * Utilisateur courant
     *
     * @var utilisateur
     * @access protected
     */
    protected $_utilisateur;

    /**
     * Configuration Sql de la partie Vente en ligne
     *
     * @var \Slrfw\Config
     */
    protected $confSql;

    /**
     * Traitement préparatoire
     *
     * @return void
     */
    public function start()
    {
        /** Récupération de la configuration de la base **/
        $path = \Slrfw\FrontController::search('config/sqlVel.ini', false);
        $this->confSql = new \Slrfw\Config($path);
        unset($path);


        foreach ($this->_versions as $version) {
            if (BACK_ID_VERSION == $version['id']) {
                $this->_currentVersion = $version;
                break;
            }
        }
        parent::start();
    }

    /**
     * Autre traitement préparatoire
     *
     * @return void
     */
    protected function beforeRunAction()
    {
        parent::beforeRunAction();
        if (count($this->_versions) == 1) {
            unset($this->config['columns'][count($this->config['columns']) - 2]);
        }
    }

    /**
     * Création de la datatable
     *
     * @return void
     */
    public function datatableAction()
    {
        $fieldGabaritTypeKey = \Slrfw\Tools::multidimensional_search(
            $this->config['columns'], array('name' => 'id_gabarit', 'filter_field' => 'select')
        );
        foreach ($this->_gabarits as $gabarit) {
            $idsGabarit[] = $gabarit['id'];
        }
        $this->config['columns'][$fieldGabaritTypeKey]['filter_field_where'] =
            'id IN  (' . implode(', ', $idsGabarit) . ')';

        parent::datatableAction();
    }

    /**
     * Défini l'utilisateur
     *
     * @param utilisateur $utilisateur Utilisateur courant
     *
     * @return void
     */
    public function setUtilisateur($utilisateur)
    {
        $this->_utilisateur = $utilisateur;
    }

    /**
     * Défini les versions
     *
     * @param array $versions versions disponibles
     *
     * @return void
     */
    public function setVersions($versions)
    {
        $this->_versions = $versions;
    }

    /**
     * Défini les gabarits
     *
     * @param array $gabarits tableau des gabarits
     *
     * @return void
     */
    public function setGabarits($gabarits)
    {
        $this->_gabarits = $gabarits;
    }

    /**
     * Construit la colonne d'action
     *
     * @param array &$data Ligne courante de donnée
     *
     * @return string Html des actions
     */
    public function buildAction(&$data)
    {
        $actionHtml = '<div class="btn-group">';

        if (($this->_utilisateur != null
            && $this->_utilisateur->get('niveau') == 'solire')
            || ($this->_gabarits != null
            && $this->_gabarits[$data['id_gabarit']]['editable'])
        ) {
            $actionHtml .= '<a href="back/page/display.html?id_gab_page=' . $data['id']
                         . '" class="btn btn-small btn-info" title="Modifier en version : '
                         . $this->_currentVersion['nom'] .  '"><i class="icon-pencil"></i></a>';
        }
        if (($this->_utilisateur->get('niveau') == 'solire'
            || $this->_gabarits[$data['id_gabarit']]['make_hidden']
            || $data['visible'] == 0)
            && $data['rewriting'] != ''
        ) {
            if ($data['visible'] == true) {
                $title = 'invisible';
                $class = 'icon-eye-open';
                $aClass = 'btn-success';
                $check = ' checked="checked"';
            } else {
                $title = 'visible';
                $class = 'icon-eye-close';
                $aClass = 'btn-default';
                $check = '';
            }
            $actionHtml .= '<a class="btn btn-small ' . $aClass . ' visible-lang" '
                         . 'title="Rendre \'' . $data['titre'] . '\' '
                         . $title . ' sur le site"><input type="checkbox" value="'
                         . $data['id'] . '|' . $data['id_version']
                         . '" style="display:none;" class="visible-lang-' . $data['id']
                         . '-' . $data['id_version'] . '" ' . $check . '/>'
                         . '<i class="' . $class . '"></i></a>';
        }

        if ($data['suppr'] == 1) {
            $actionHtml = '<a href="#" class="btn btn-small btn-warning supprimer"'
                        . 'title="Supprimer"><i class="icon-eye-open"></i></a>';
        }

        $actionHtml .= '</div>';
        return $actionHtml;
    }

    /**
     * Construit la colonne de traduction
     *
     * @param array &$data Ligne courante de donnée
     *
     * @return string Html de traduction
     */
    public function buildTraduit(&$data)
    {
        if ($data['suppr'] == 1) {
            return '';
        }
        $actionHtml = '<div style="width:110px">';

        $query = 'SELECT id_version, rewriting '
                . 'FROM gab_page '
                . 'WHERE id = ' . $data['id'] . ' ';
        $pages = $this->_db->query($query)->fetchAll(\PDO::FETCH_COLUMN | \PDO::FETCH_UNIQUE);

        foreach ($this->_versions as $version) {
            if ($pages[$version['id']] == '') {
                continue;
            }
            if (empty($page[$version['id']])) {
                $str = 'title="' . $version['nom'] . ' : Non traduit"  class="grayscale"';
            } else {
                $str = 'title="' . $version['nom'] . ' : Traduit"';
            }
            $actionHtml .= '<img ' . $str . ' src="app/back/img/flags/png/'
                         . strtolower($version['suf']) . '.png" alt="'
                         . $version['nom'] . '" />';
            $actionHtml .= '&nbsp;' ;
        }

        $actionHtml .= '</div>';

        return $actionHtml;
    }

    /**
     * Construit la colonne de disponibilité
     *
     * @param array &$data Ligne courante de donnée
     *
     * @return string Html de traduction
     * @hook back listProduitDispo Chargement de la colonne disponibilité, peut
     * remplacer le chargement par défaut en passant $env->defaultExec à true
     */
    public function buildDispo(&$data)
    {
        $hook = new \Slrfw\Hook();
        $hook->setSubdirName('back');

        $hook->defaultExec = true;
        $hook->data = $data;
        $hook->content = '';

        $hook->exec('listProduitDispo');

        if ($hook->defaultExec !== true) {
            return $hook->content;
        }

        $query = 'SELECT disponible '
               . 'FROM ' . $this->confSql->get('table', 'produit') . ' p '
               . 'WHERE id_gab_page = ' . $data['id'] . ' ';
        $dispo = $this->_db->query($query)->fetchColumn();

        if ($dispo == 1) {
            $str = 'indisponible';
            $check = ' checked="checked"';
            $msg = 'oui';
            $aClass = 'btn-success';
        } else {
            $str = 'disponible';
            $check = ' ';
            $msg = 'non';
            $aClass = 'btn-warning';
        }

        $actionHtml = '<a class="btn btn-small ' . $aClass . ' disponible" '
                    . 'title="Rendre ' . $str . '"><input type="checkbox" value="'
                    . $data['id'] . '|' . $data['id_version']
                    . '" style="display:none;" class="disponible-lang-' . $data['id']
                    . '-' . $data['id_version'] . '" ' . $check . '/><i>' . $msg
                    . '</i></a>';

        return $actionHtml;
    }

    /**
     * Permet de gérer les pages supprimer (Visuel + action)
     *
     * @param array $aRow     Ligne courante de toutes les données (ASSOC)
     * @param array $rowAssoc Ligne courante des données affiché (ASSOC)
     * @param array &$row     Ligne courante de donnée affiché (NUM)
     *
     * @return void
     */
    public function disallowDeleted($aRow, $rowAssoc, &$row)
    {
        $row['DT_RowClass'] = '';
        if ($aRow['suppr'] == 1) {
            $keyAction = array_search('visible_1', array_keys($rowAssoc));
            $row[$keyAction] = '<div class="btn btn-small btn-danger disabled" >Supprimée</div>';
            $row['DT_RowClass'] = 'translucide';
        }
    }
}

