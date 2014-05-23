<?php
/**
 * Trait pour panier
 *
 * @author     Adrien <aimbert@solire.fr>
 * @license    CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 * @filesource
 */

namespace Vel\Lib;

/**
 * Trait pour panier
 *
 * @author     Adrien <aimbert@solire.fr>
 * @license    CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 * @filesource
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
