<?php
/**
 * Gestionnaire des commandes
 *
 * @package    Vel
 * @subpackage Back
 * @author     Adrien <aimbert@solire.fr>
 * @license    Solire http://www.solire.fr/
 */

namespace Vel\Back\Controller;

/**
 * Gestionnaire des commandes
 *
 * @package    Vel
 * @subpackage Back
 * @author     Adrien <aimbert@solire.fr>
 * @license    Solire http://www.solire.fr/
 */
class Produits extends \App\Back\Controller\Main
{
    /**
     * Configuration Sql de la partie Vente en ligne
     *
     * @var \Slrfw\Config
     */
    protected $confsql;

    public function start()
    {
        /** Récupération de la configuration de la base **/
        $path = \Slrfw\FrontController::search('config/sqlVel.ini', false);
        $this->confSql = new \Slrfw\Config($path);
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
            && isset($_POST['disponible']) && is_numeric($_POST['disponible']))) {
            exit(json_encode($json));
        }

        $dispo = (boolean) $_POST['disponible'];

        $query = 'UPDATE ' . $this->confSql->get('table', 'produit') . ' SET '
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
            if(in_array($gabarit['id'], $gabaritsListUser))
                $gabarits[$keyId] = $gabarit;
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
        $aqqQuery = 'gab_page.id_gabarit IN (' . implode(",", $idsGabarit) . ')';
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
}

