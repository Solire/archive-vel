<?php
/**
 * Module des comptes utilisateur
 *
 * @package    Controller
 * @subpackage Front
 * @author     Adrien <aimbert@solire.fr>
 * @license    CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 * @filesource
 */

namespace Vel\Front\Controller;

/**
 * Module des comptes utilisateur
 *
 * @package    Controller
 * @subpackage Front
 * @author     Adrien <aimbert@solire.fr>
 * @license    CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */
class Compte extends \Vel\Front\Controller\Main
{

    /**
     * Configuration de l'espace client
     *
     * @var \Slrfw\Config
     */
    protected $config;

    /**
     * Chargement de la configuration client
     *
     * @return void
     * @ignore
     */
    public function start()
    {
        parent::start();

        $path = \Slrfw\FrontController::search('config/client.ini', false);
        $this->config = new \Slrfw\Config($path);

        $this->_view->breadCrumbs[] = array(
            'label' => $this->config->get('noms', 'espace'),
            'url'   => 'compte/'
        );
    }

    /**
     * Page d'accueil de l'espace compte
     *
     * @return void
     * @page Page d'acceuil de l'espace client
     */
    public function startAction()
    {
        $client = $this->chargeCompte();

        // @todo Charger les informations pour le compte client
    }

    /**
     * Déconnexion du client
     *
     * @return void
     */
    public function deconnexionAction()
    {
        $this->_view->enable(false);

        $client = new \Slrfw\Session('client');
        $client->disconnect();

        $url = $this->config->get('url', 'afterdeco');
        $this->simpleRedirect($url, true);
    }

    /**
     * Connexion d'un nouvel utilisateur
     *
     * @return void
     */
    public function connexionAction()
    {
        $this->_view->enable(false);

        $form = $this->chargeForm('connexion.form.ini');

        list($mail, $password) = $form->run(\Slrfw\Formulaire::FORMAT_LIST);

        $client = new \Slrfw\Session('client');
        $client->connect($mail, $password);

        if (isset($form->url)) {
            $message = new \Slrfw\Message($this->_view->_('Connexion Ok'));
            $message->addRedirect($form->url, 1);
            $message->display();
        } else {
            $this->simpleRedirect('compte/start.html');
        }
    }

    /**
     * Génère un nouveau mot de passe pour l'utilisateur et lui envois par mail
     *
     * @return void
     * @mail Mot de passe perdu
     */
    public function mdpPerduAction()
    {
        $this->_view->enable(false);

        $form = $this->chargeForm('mdp.perdu.form.ini');
        $form->run();

        $query = 'SELECT id '
               . 'FROM ' . $this->config('table', 'client') . ' c '
               . 'WHERE email = ' . $this->_db->quote($form->email) . ' ';
        $id = $this->_db->query($query)->fetch(PDO::FETCH_COLUMN);

        if (!empty($id)) {

            /* = Enregistrement du nouveau mot de passe
              ------------------------------- */
            $data = array();
            $password = \Slrfw\Session::makePass();
            $data[$this->config->get('table', 'colPassword')] = $password;
            $className = \Slrfw\FrontController::searchClass('Lib\Client');
            $client = new $className($id);
            $client->update($data);

            /* = Envois du mot de passe
              ------------------------------- */
            $mail = new Mail('mdp.perdu');
            $mail->to = $form->email;
            $mail->subject = $this->_view->_('Voici votre nouveau mot de passe');
            $mail->mdp = $password;

            $mail->send();

            $phrase = 'Un email a été envoyé avec votre nouveau mot de passe.';
            $this->_message($phrase);
            return true;
        } else {
            $phrase = "Votre adresse email n'est pas enregistré dans notre boutique.";
            $this->_message($phrase);
        }
    }

    /**
     * Formulaire d'edition du compte
     *
     * @return void
     * @page Formulaire d'édition du compte client
     */
    public function editionAction()
    {
        $client = $this->chargeCompte();
        $this->_view->client = $client->getInfo();

        $this->_view->breadCrumbs[] = array(
            'label' => 'edition',
            'url'   => 'compte/edition.html'
        );
    }

    /**
     * Enregistrement de l'édition d'un compte
     *
     * @return void
     */
    public function enregistrementEditionAction()
    {
        $this->_view->enable(false);
        $client = $this->chargeCompte();

        $formCompte = $this->chargeForm('client.edition.form.ini');
        $infoClient = $formCompte->run();

        $client->update($infoClient);

        $phrase = 'Modifications correctement enregistrées';
        $message = new Message($this->_view->_($phrase));
        $message->addRedirect('compte/edition.html', 2);
        $message->display();
    }

    /**
     * Formulaire d'inscription
     *
     * @return void
     * @page Inscription utilisateur
     */
    public function inscriptionAction()
    {
        /**
         * Fils d'ariane
         */
        $this->_view->breadCrumbs[] = array(
            'label' => $this->_view->_('inscription'),
            'url'   => ''
        );
    }

    /**
     * Création d'un nouveau compte
     *
     * @return void
     * @todo mep les envois de mail
     */
    public function enregistrementAction()
    {
        $this->_view->enable(false);

        /**
         * Chargement du formulaire
         */
        $formCompte = $this->chargeForm('client.form.ini');
        $infoClient = $formCompte->run();

        /**
         * Chargement de la class Client
         */
        $className = \Slrfw\FrontController::searchClass('Lib\Client', false);
        $client = new $className();

        /**
         * Enregistrement
         */
        $client->enreg($infoClient);

        $this->simpleRedirect('compte/start.html', true);
    }
}

