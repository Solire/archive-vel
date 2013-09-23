<?php
/**
 * Chargement des informations sur les commandes
 *
 * @package    Vel
 * @subpackage Hook
 * @author     Adrien <aimbert@solire.fr>
 * @license    CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */

namespace Vel\Hook\Back\Start;

/**
 * Chargement des informations sur les commandes
 *
 * @package    Vel
 * @subpackage Hook
 * @author     Adrien <aimbert@solire.fr>
 * @license    CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */
class Vel implements \Slrfw\HookInterface
{
    /**
    * Chargement des informations sur les commandes
    *
    * @param \Slrfw\Hook $env Données d'environnement
    *
    * @return void
    */
    public function run($env)
    {
        $commandeClass = \Slrfw\FrontController::searchClass('Lib\Commande');
        $commande = new $commandeClass;
        unset($commandeClass);


        $env->ctrl->_view->countCmd = count($commande->listeEnCours());
    }
}

