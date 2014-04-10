<?php
/**
 * Fonctionnalités de vente en ligne
 *
 * @package    Controller
 * @subpackage Front
 * @author     Adrien <aimbert@solire.fr>
 * @license    CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 * @filesource
 */

namespace Vel\Front\Controller;

use \Slrfw\Format\Number;

/**
 * Fonctionnalités de vente en ligne
 *
 * @package    Controller
 * @subpackage Front
 * @author     Adrien <aimbert@solire.fr>
 * @license    CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 * @filesource
 */
class Shop extends \Vel\Front\Controller\Main
{

    /**
     * @var \Vel\Lib\Panier nom de la classe panier
     */
    protected $panier;

    /**
     *
     * @var \Slrfw\Config
     */
    protected $config;

    /**
     * Suppression du référencement
     *
     * @return void
     * @ignore
     */
    public function start()
    {
        parent::start();
        $this->_view->gindex = 'no';
        $this->_view->gfollow = 'no';

        $path = \Slrfw\FrontController::search('config/sqlVel.ini', false);
        $this->config = new \Slrfw\Config($path);
        unset($path);

    }

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

    /**
     * Ajout d'un produit au panier
     *
     * @return void
     */
    public function ajoutProduitAction()
    {
        $this->_view->enable(false);
        $archi = array(
            'produit' => array(
                'test' => 'notEmpty|isInt',
                'obligatoire' => true,
                'erreur' => 'Veuillez choisir un produit'
            ),
            'qte' => array(
                'test' => 'notEmpty|isInt',
                'obligatoire' => true,
                'erreur' => 'Veuillez préciser une quantité',
                'exception' => 'LibException',
            ),
        );
        $ajoutProduit = new \Slrfw\Formulaire($archi);
        list($produit, $qte) = $ajoutProduit->run(\Slrfw\Formulaire::FORMAT_LIST);

        $panier = $this->loadPanier();

        $panier->ajoute($produit, $qte);

        /** Chargement du nom produit **/
        $query = 'SELECT titre '
               . 'FROM ' . $this->config->get('table', 'reference') . ' r '
               . 'INNER JOIN gab_page gp '
               . ' ON gp.id = r.id_gab_page '
               . 'WHERE r.id = ' . $produit . ' '
               . ' AND r.suppr = 0 '
               . ' AND r.visible = 1 ';
        $dataProd = $this->_db->query($query)->fetch(\PDO::FETCH_ASSOC);

        $message = new \Slrfw\Message('Produit ajouté au panier');
        $message->total = Number::money($panier->getPrix(), true, '€');
        $message->nom = $dataProd['titre'];
        $message->produitQte = $panier->getNombre($produit);
        $message->produitPrix = Number::money($panier->getPrix($produit), true, '€');
        $message->port = Number::money($panier->getPort(), true, '€');
        $message->nbProduits = $panier->getNombre();
        $message->display();
    }

    /**
     * Supprime un produit du panier
     *
     * @return void
     */
    public function supprimeProduitAction()
    {
        $this->_view->enable(false);
        $form = array(
            'id' => array(
                'test' => 'notEmpty|isInt',
                'obligatoire' => true,
                'exception' => '\Slrfw\Exception\Lib'
            )
        );
        $formulaire = new \Slrfw\Formulaire($form);
        $data = $formulaire->run();

        $panier = $this->loadPanier();
        $panier->supprime($data['id']);

        $message = new \Slrfw\Message('Produit supprimé du panier');
        $message->nbProduits = $panier->getNombre();
        $message->port = Number::money($panier->getPort(), true, '€');
        $message->total = Number::money($panier->getPrix(), true, '€');
        $message->addRedirect('shop/panier.html', 3);
        $message->display();
    }

    /**
     * Affichage du panier
     *
     * @return void
     * @uses Panier::getInfo()
     */
    public function panierAction()
    {
        $panier = $this->loadPanier();

        $this->_seo->setTitle('Panier');

        /** Si le panier est vide on envois une autre page **/
        if ($panier->getNombre() == 0) {
            $this->pageNotFound();
            return;
        }

        $client = $this->chargeCompte(false);
        if ($client !== false) {
            $name = 'panier-adresse';
            $this->_view->client = $client->getInfo();
        } else {
            $name = 'panier-login';
        }

        $this->_view->actionPanier = \Slrfw\FrontController::search(
            'view/shop/' . $name . '.php'
        );

        /** Utilisation d'un hook **/
        $hook = new \Slrfw\Hook();
        $hook->setSubdirName('panier');

        /** Passage des variables utilisables **/
        $hook->panier = $panier->getInfo();
        $hook->gabaritManager = $this->_gabaritManager;

        /** Execution du hook **/
        $hook->exec('affichage');

        /** Récupération des variables **/
        $this->_view->panier = $hook->panier;
        unset($hook);
    }

    /**
     * Validation de la commande
     *
     * @return void
     * @throws UserException
     */
    public function passerCommandeAction()
    {
        $this->_view->enable(false);

        /** Utilisation d'un hook **/
        $hook = new \Slrfw\Hook();
        $hook->setSubdirName('commande');

        /** Chargement des données **/
        $form = $this->chargeForm('passercommande.form.ini');
        list($mode) = $form->run(\Slrfw\Formulaire::FORMAT_LIST);
        $hook->form = $form->getArray();
        unset($form);

        /* = Enregistrement de la commande
          ------------------------------- */
        $panier = $this->loadPanier();
        if ($panier->getNombre() == 0) {
            throw new \Slrfw\Exception\User('Aucun Panier en cours');
        }

        $hook->exec('controle');

        $className = \Slrfw\FrontController::searchClass('Lib\Commande', false);
        $commande = new $className();

        $modes = $commande->config('modePayement', 'enable');
        if (empty($modes)) {
            throw new \Slrfw\Exception\Lib('Pas de valeur de mode de payement correspondante');
        }
        $modes = explode(',', $modes);
        $modes = array_map('trim', $modes);

        if (!in_array($mode, $modes)) {
            throw new \Slrfw\Exception\Lib('Mode de payement non disponible');
        }

        $commande->panierToCommande($mode, $panier);

        /** Execution du hook **/
        $hook->commande = $commande;
        $hook->exec('traitement');
        $hook->exec($mode);
    }
}

