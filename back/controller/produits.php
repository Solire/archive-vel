<?php
/**
 * Gestionnaire des commandes
 *
 * @package    Vel
 * @subpackage Back
 * @author     Adrien <aimbert@solire.fr>
 * @license    CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */

namespace Vel\Back\Controller;

/**
 * Gestionnaire des commandes
 *
 * @package    Vel
 * @subpackage Back
 * @author     Adrien <aimbert@solire.fr>
 * @license    CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */
class Produits extends \App\Back\Controller\Main
{
    /**
     * Configuration Sql de la partie Vente en ligne
     *
     * @var \Slrfw\Config
     */
    protected $confsql;

    /**
     * Traitement préparatoire
     *
     * @return void
     */
    public function start()
    {
        /** Récupération de la configuration de la base **/
        $path = \Slrfw\FrontController::search('config/sqlVel.ini', false);
        $this->confVel = new \Slrfw\Config($path);
        unset($path);


        parent::start();
    }

    /**
     * Affichage du tableau de bord
     *
     * @return void
     */
    public function startAction()
    {
        $this->_view->action = 'produits';

        $this->boardDatatable();

        $this->_view->breadCrumbs[] = array(
            'label' => 'Tableau de bord',
            'url' => 'back/board/start.html',
        );
        $this->_javascript->addLibrary('back/js/listeproduits.js');

        $this->_view->confVel = $this->confVel;
    }


    /**
     * Change l'état de disponibilité des produits
     *
     * @return void
     */
    public function disponibleAction()
    {
        $this->_view->enable(false);
        $json = array('status' => 'error');

        $idVersion = BACK_ID_VERSION;

        if (isset($_POST['id_version']) && $_POST['id_version'] > 0) {
            $idVersion = intval($_POST['id_version']);
        }
        if (!(isset($_POST['id_gab_page']) && is_numeric($_POST['id_gab_page'])
            && isset($_POST['disponible']))
        ) {
            exit(json_encode($json));
        }

        if ($_POST['disponible'] === 'true') {
            $dispo = 1;
        } else {
            $dispo = 0;
        }

        $query = 'UPDATE ' . $this->confVel->get('table', 'produit') . ' SET '
               . '  disponible = ' . (int) $dispo . ' '
               . 'WHERE id_gab_page = ' . (int) $_POST['id_gab_page'] . ' '
               . ' AND id_version = ' . $idVersion . ' ';
        try {
            $this->_db->exec($query);
        } catch (\PDOException $exc) {
            exit(json_encode($json));
        }

        $json['status'] = 'success';
        exit(json_encode($json));
    }


    /**
     * Génération du datatable des pages crées / éditées / supprimées
     *
     * @return void
     */
    private function boardDatatable()
    {
        $configName = 'produits';
        $gabarits = array();
        $configPageModule = $this->_configPageModule[101];
        $gabaritsListUser = $configPageModule['gabarits'];
        foreach ($this->_gabarits as $keyId => $gabarit) {
            if (in_array($gabarit['id'], $gabaritsListUser)) {
                $gabarits[$keyId] = $gabarit;
            }
        }
        unset($configPageModule);


        $configPath = \Slrfw\FrontController::search(
            'config/datatable/' . $configName . '.cfg.php'
        );

        $this->_gabarits = $gabarits;

        $datatableClassName = 'Back\\Datatable\\Produits';
        $className = \Slrfw\FrontController::searchClass($datatableClassName);

        /** On cré notre object datatable */
        $datatable = new $className(
            $_GET, $configPath, $this->_db, '/back/css/datatable/',
            '/back/js/datatable/', 'img/datatable/'
        );

        $datatable->setUtilisateur($this->_utilisateur);
        $datatable->setGabarits($this->_gabarits);
        $datatable->setVersions($this->_versions);

        /** On cré un filtre pour les gabarits de l'api courante */
        $idsGabarit = array();
        foreach ($this->_gabarits as $gabarit) {
            $idsGabarit[] = $gabarit['id'];
        }
        $aqqQuery = 'gab_page.id_gabarit IN (' . implode(',', $idsGabarit) . ')';
        $datatable->additionalWhereQuery($aqqQuery);

        $datatable->start();
        $datatable->setDefaultNbItems(
            $this->_appConfig->get('board', 'nb-content-default')
        );

        if (isset($_GET['json']) || (isset($_GET['nomain'])
            && $_GET['nomain'] == 1)
        ) {
            echo $datatable;
            exit();
        }

        $this->_view->datatableRender = $datatable;
    }


    /**
     * Enregistrement d'une référence
     *
     * Renvois des informations sur l'exécution de l'enregistrement et
     * les informations de la référence en vue de son affichage dans le
     * tableau
     *
     * @return void
     */
    public function saveReferenceAction()
    {
        $this->_view->enable(false);

        /** Contrôle des variables **/
        if (!isset($_POST['idGabPage']) || empty($_POST['idGabPage'])) {
            $data = array(
                'message' => 'idGabPage vide',
            );
            $this->sendError($data);
            return;
        }

        $idGabPage = (int) $_POST['idGabPage'];

        $query = 'SELECT * '
               . 'FROM gab_page '
               . 'WHERE id = ' . $idGabPage . ' ';
        $gab = $this->_db->query($query)->fetch();

        if (empty($gab)) {
            $data = array(
                'message' => 'Aucun gabarit pour cet id',
            );
            $this->sendError();
            return;
        }

        $path = \Slrfw\FrontController::search('config/sqlVel.ini', false);
        $config = new \Slrfw\Config($path);

        /** Enregistrement ou édition **/
        if (isset($_POST['idBloc']) && !empty($_POST['idBloc'])) {
            $idBloc = $_POST['idBloc'];
        } else {
            $query = 'INSERT INTO ' . $config->get('table', 'reference') . ' '
                   . 'SET id_version = ' . BACK_ID_VERSION . ', '
                   . ' id_gab_page = ' . $idGabPage . ', '
                   . ' visible = 1 ';
            $this->_db->exec($query);
            $idBloc = $this->_db->lastInsertId();
        }

        /** Enregistrement des modifications de la table référence **/
        $query = 'DESC ' . $config->get('table', 'reference') . ' ';
        $col = $this->_db->query($query)->fetchAll(\PDO::FETCH_COLUMN, 0);
        $update = array();
        foreach ($col as $val) {
            if (!isset($_POST[$val])) {
                continue;
            }
            $update[] = $val . ' = ' . $this->_db->quote($_POST[$val]);
        }

        if (!empty($update)) {
            $query = 'UPDATE ' . $config->get('table', 'reference') . ' SET '
                   . implode(', ', $update) . ' '
                   . 'WHERE id = ' . $idBloc . ' ';
            $this->_db->query($query);
        }

        /** Renregistrement des éléments spéciaux **/
        $this->saveCritere($config, $idGabPage, $idBloc);
        $this->saveRegion($config, $idGabPage, $idBloc);

        $hook = new \Slrfw\Hook();
        $hook->idGabPage = $idGabPage;
        $hook->data = $_POST;

        $hook->exec('referenceSave');

        $this->sendSuccess();
    }

    /**
     * Enregistrement des critères
     *
     * @param \Slrfw\Config $config    Configuration sqlVel
     * @param int           $idGabPage Identifiant de la page
     * @param int           $idBloc    Identifiant du bloc
     *
     * @return void
     */
    protected function saveCritere($config, $idGabPage, $idBloc)
    {
        /** Enregistrement des critères **/
        $query = 'SELECT * '
               . 'FROM ' . $config->get('table', 'produitCritere') . ' proCrit '
               . 'INNER JOIN ' . $config->get('table', 'critere') . ' c '
               . ' ON c.id = proCrit.id_critere '
               . 'WHERE proCrit.id_gab_page = ' . $idGabPage . ' '
               . ' AND proCrit.suppr = 0 ';
        $criteres = $this->_db->query($query)->fetchAll();

        $idsCritere = array();
        foreach ($criteres as $critere) {
            if (!isset($_POST['crit_' . $critere['id_critere']])
                || empty($_POST['crit_' . $critere['id_critere']])
            ) {
                continue;
            }
            $idsCritere[] = $critere['id_critere'];

            $val = $_POST['crit_' . $critere['id_critere']];
            if ((string) $val == (string) (int) $val) {
                $idVal = (int) $val;
            } else {
                $query = 'SELECT id '
                       . 'FROM ' . $config->get('table', 'critereOption') . ' '
                       . 'WHERE id_critere = ' . $critere['id_critere'] . ' '
                       . ' AND name LIKE ' . $this->_db->quote($val) . ' ';
                $idVal = $this->_db->query($query)->fetchColumn();
                if (empty($idVal)) {
                    $query = 'INSERT INTO ' . $config->get('table', 'critereOption') . ' '
                           . 'SET id_critere = ' . $critere['id_critere'] . ', '
                           . ' name = ' . $this->_db->quote($val) . ' ';
                    $this->_db->exec($query);
                    $idVal = $this->_db->lastInsertId();
                }
            }

            $query = 'INSERT INTO ' . $config->get('table', 'referenceCritere') . ' '
                   . 'SET id_bloc = ' . $idBloc . ', '
                   . '  id_critere = ' . $critere['id_critere'] . ', '
                   . '  id_critere_option = ' . $idVal . ' '
                   . 'ON DUPLICATE KEY UPDATE id_critere_option = ' . $idVal . ', '
                   . ' suppr = "0000-00-00 00:00:00" ';
            $this->_db->exec($query);
        }
        if (empty($idsCritere)) {
            $idsCritere[] = $this->_db->quote('toto');
        }
        $query = 'UPDATE ' . $config->get('table', 'referenceCritere') . ' '
               . 'SET suppr = NOW() '
               . 'WHERE id_critere NOT IN (' . implode(', ', $idsCritere) . ') '
               . ' AND id_bloc = ' . $idBloc . ' ';
        $this->_db->exec($query);
    }

    /**
     * Enregistrement des régions
     *
     * @param \Slrfw\Config $config    Configuration sqlVel
     * @param int           $idGabPage Identifiant de la page
     * @param int           $idBloc    Identifiant du bloc
     *
     * @return void
     */
    protected function saveRegion($config, $idGabPage, $idBloc)
    {
        $query = 'SELECT * '
               . 'FROM ' . $config->get('table', 'produitRegion') . ' proReg '
               . 'INNER JOIN ' . $config->get('table', 'region') . ' r '
               . ' ON r.id = proReg.id_region '
               . 'WHERE proReg.id_gab_page = ' . $idGabPage . ' '
               . ' AND proReg.suppr = 0 ';
        $regions = $this->_db->query($query)->fetchAll();

        $idsRegion = array();
        foreach ($regions as $region) {
            if (!isset($_POST['reg_taxe_' . $region['id_region']])) {
                continue;
            }

            $idsRegion[] = $region['id_region'];

            $taxeId = $_POST['reg_taxe_' . $region['id_region']];
            $prixTTC = (float) $_POST['reg_ttc_' . $region['id_region']];
            $prixHT = (float) $_POST['reg_ht_' . $region['id_region']];

            $query = 'INSERT INTO ' . $config->get('table', 'referenceRegion') . ' '
                   . 'SET id_bloc = ' . $idBloc . ', '
                   . '  id_region = ' . $region['id_region'] . ', '
                   . '  taxe = ' . $this->_db->quote($taxeId) . ', '
                   . '  prix_ttc = ' . $prixTTC . ', '
                   . '  prix_ht = ' . $prixHT . ' '
                   . 'ON DUPLICATE KEY UPDATE taxe = ' . $this->_db->quote($taxeId) . ', '
                   . '  prix_ttc = ' . $prixTTC . ', '
                   . '  prix_ht = ' . $prixHT . ', '
                   . ' suppr = "0000-00-00 00:00:00" ';
            $this->_db->exec($query);
        }
        if (empty($idsRegion)) {
            $idsRegion[] = $this->_db->quote('toto');
        }
        $query = 'UPDATE ' . $config->get('table', 'referenceRegion') . ' '
               . 'SET suppr = NOW() '
               . 'WHERE id_region NOT IN (' . implode(', ', $idsRegion) . ') '
               . ' AND id_bloc = ' . $idBloc . ' ';
        $this->_db->exec($query);
    }

    /**
     * Suppression d'une référence
     *
     * @return void
     */
    public function deleteRefAction()
    {
        $this->_view->enable(false);

        if (!isset($_POST['idBloc'])) {
            return;
        }

        $idBloc = (int) $_POST['idBloc'];

        $path = \Slrfw\FrontController::search('config/sqlVel.ini', false);
        $config = new \Slrfw\Config($path);

        $tables = array('referenceCritere', 'referenceRegion');

        foreach ($tables as $table) {
            $query = 'UPDATE ' . $config->get('table', $table) . ' SET '
                   . ' suppr = NOW() '
                   . 'WHERE id_bloc = ' . $idBloc . ' ';
            $this->_db->exec($query);
        }

        $query = 'UPDATE ' . $config->get('table', 'reference') . ' SET '
               . ' suppr = NOW() '
               . 'WHERE id = ' . $idBloc . ' ';
        $this->_db->exec($query);

        $this->sendSuccess();
    }
}

