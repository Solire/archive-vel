<?php
/**
 * Fonctionnalité communes de la vente en ligne
 *
 * @package    Controller
 * @subpackage Front
 * @author     Adrien <aimbert@solire.fr>
 * @license    CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 * @filesource
 */

namespace Vel\Front\Controller;

/**
 * Fonctionnalité communes de la vente en ligne
 *
 * @package    Controller
 * @subpackage Front
 * @author     Adrien <aimbert@solire.fr>
 * @license    CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */
class Main extends \App\Front\Controller\Main
{
    /**
     * Charge le compte courent
     * Et lance une 401 si aucun compte n'est disponible
     *
     * @param boolean $displayError Affichage des erreurs
     *
     * @return \Vel\Lib\Client|false
     */
    protected function chargeCompte($displayError = true)
    {
        $session = new \Slrfw\Session('client');
        if (!$session->isConnected()) {
            if ($displayError === true) {
                $this->redirectError(401);
            }
            return false;
        }

        $className = \Slrfw\FrontController::searchClass('Lib\Client', false);
        $client = new $className($session->id);

        return $client;
    }

    /**
     * Charge un formulaire
     *
     * Le formulaire est juste instancié à partir du fichier présent dans
     * <app>/config/form/
     *
     * @param string $name Nom du fichier de configuration du formulaire
     *
     * @return \Slrfw\Formulaire
     */
    protected function chargeForm($name)
    {
        $name = 'config/form/' . $name;
        $path = \Slrfw\FrontController::search($name, false);
        $form = new \Slrfw\Formulaire($path, true);

        return $form;
    }
}

