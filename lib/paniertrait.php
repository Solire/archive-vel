<?php
/**
 *
 *
 * @author     Adrien <aimbert@solire.fr>
 * @license    CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */

namespace Vel\Lib;

/**
 *
 *
 * @author     Adrien <aimbert@solire.fr>
 * @license    CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */
trait PanierTrait
{
    /**
     * Charge le panier de l'utilisateur
     *
     * @return \Vel\Lib\Panier
     */
    protected function loadPanier()
    {
        $panierClass = \Slrfw\FrontController::searchClass('Lib\Panier');
        $panier = new $panierClass;
        return $panier;
    }
}
