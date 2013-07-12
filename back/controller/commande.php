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


        $this->_view->list = $commande->listeEnCours();
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
    }
}

