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
class Commande extends \App\Back\Controller\Main
{
    /**
     * Toujours executé avant l'action.
     *
     * @return void
     */
    public function start()
    {
        parent::start();

    }

    /**
     * Affiche les commandes en cours
     *
     * @return void
     */
    public function encoursAction()
    {

        $commandeClass = \Slrfw\FrontController::searchClass('Lib\Commande');
        $commande = new $commandeClass;
        unset($commandeClass);

        $clientClass = \Slrfw\FrontController::searchClass('Lib\Client');

        $this->_javascript->addLibrary('app/back/js/jquery/jquery.dataTables.min.js');

        $this->_view->list = $commande->listeEnCours();

        foreach ($this->_view->list as $key => $cmd) {
            $foo = new $clientClass();
            $foo->set($cmd['id_client']);

            $this->_view->list[$key]['client'] = $foo->getInfo();
        }
    }

    /**
     * Fait évoluer l'état d'une commande
     *
     * @return void
     */
    public function evolAction()
    {
        $archi = array(
            'id' => array(
                'test' => 'notEmpty|isInt',
                'obligatoire' => true,
                'erreur' => 'Problème d\'identifiant de commande',
                'exception' => '\Slrfw\Exception\Lib',
            ),
            'etape' => array(
                'test' => 'notEmpty',
                'obligatoire' => true,
                'erreur' => 'Etape érroné',
                'exception' => '\Slrfw\Exception\Lib',
            ),
            'code' => array(
                'test' => 'isString',
                'obligatoire' => false,
                'erreur' => 'code érroné',
                'exception' => '\Slrfw\Exception\Lib',
            ),
        );
        $evol = new \Slrfw\Formulaire($archi);
        list($commandeId, $etape, $code) = $evol->run(\Slrfw\Formulaire::FORMAT_LIST);

        $commandeClass = \Slrfw\FrontController::searchClass('Lib\Commande');
        $commande = new $commandeClass;
        unset($commandeClass);

        $commande->set($commandeId);

        $commande->changeEtat($etape, $code);

        $this->simpleRedirect('encours.html', false);
    }

    /**
     * Affiche les commandes expédiés
     *
     * @return void
     */
    public function traiteAction()
    {
        $commandeClass = \Slrfw\FrontController::searchClass('Lib\Commande');
        $commande = new $commandeClass;
        unset($commandeClass);

        $this->_view->list = $commande->listeExp();

        $clientClass = \Slrfw\FrontController::searchClass('Lib\Client');
        foreach ($this->_view->list as $key => $cmd) {
            $foo = new $clientClass();
            $foo->set($cmd['id_client']);

            $this->_view->list[$key]['client'] = $foo->getInfo();
        }
    }

    /**
     * Affiche le détail d'une commande
     *
     * @return void
     */
    public function voirAction()
    {
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            return;
        }

        $idCommande = $_GET['id'];

        $commandeClass = \Slrfw\FrontController::searchClass('Lib\Commande');
        $commande = new $commandeClass;
        unset($commandeClass);

        $commande->set($idCommande);

        $this->_view->commande = $commande->getInfo($this->_gabaritManager);
        echo '<pre>' . print_r($this->_view->commande, true) . '</pre>';
    }
}

