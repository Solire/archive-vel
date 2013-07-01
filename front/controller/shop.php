<?php
/**
 * Fonctionnalités de vente en ligne
 *
 * @package    Controller
 * @subpackage Front
 * @author     Adrien <aimbert@solire.fr>
 * @license    Solire http://www.solire.fr/
 * @filesource
 */

namespace Vel\Front\Controller;

/**
 * Fonctionnalités de vente en ligne
 *
 * @package    Controller
 * @subpackage Front
 * @author     Adrien <aimbert@solire.fr>
 * @license    Solire http://www.solire.fr/
 * @filesource
 */
class Shop extends \Vel\Front\Controller\Main
{

    /**
     * @var \Vel\Lib\Panier nom de la classe panier
     */
    private $panier;

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


	}

    /**
     * Charge le panier de l'utilisateur
     *
     * @return \Vel\Lib\Panier
     */
    private function loadPanier()
    {
        $panierClass = \Slrfw\FrontController::searchClass('Lib\Panier');
        $panier = new $panierClass;
        return $panier;
    }

    /**
     * Charge les informations du prix du produit
     */
    public function prixAction()
    {
        $this->onlyAjax();

        $ajoutProduit = new Formulaire('ajoutproduit.form.ini');
        list($produit, $couleur, $taille, $qte)
            = $ajoutProduit->run(Formulaire::FORMAT_LIST);

        /* = Recherche de l'id de la référence
          ------------------------------- */
        $query = 'SELECT r.id, r.prix, r.taux_remise '
               . 'FROM shop_produit_reference r '
               . 'WHERE r.couleur = ' . $couleur . ' '
               . ' AND r.taille = ' . $taille . ' '
               . ' AND r.id_gab_page = ' . $produit . ' '
               . ' AND r.stock >= 0 '
               . ' AND visible = 1 ';

        $ref = $this->_db->query($query)->fetch(PDO::FETCH_ASSOC);

        if (empty($ref))
            throw new LibException("");

        $message = new Message("");
        $price = $ref['prix'] * ( 1 - ($ref['taux_remise'] / 100));
        $message->prix = \Slrfw\Format\Number::formatMoney($price);
        $message->promotion = $ref['taux_remise'];
        $message->prixFix = \Slrfw\Format\Number::formatMoney($ref['prix']);
        $message->display();
    }

    /**
     * Ajout d'un produit au panier
     *
     * @return void
     */
    public function ajoutProduitAction()
    {
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

        $message = new \Slrfw\Message('Produit ajouté au panier');
        $message->total = \Slrfw\Format\Number::formatMoney($panier->getPrix());
        $message->produitQte = $panier->getNombre($produit);
        $message->produitPrix = \Slrfw\Format\Number::formatMoney($panier->getPrix($produit));
        $message->port = \Slrfw\Format\Number::formatMoney($panier->getPort());
        $message->nbProduits = $panier->getNombre();
        $message->display();
    }

    /**
     * Supprime un produit du panier
     * @uses Formulaire
     * @uses Panier::supprime()
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
                'exception' => 'LibException'
            )
        );
        $formulaire = new \Slrfw\Formulaire($form);
        $data = $formulaire->run();

        $panier = $this->loadPanier();
        $panier->supprime($data['id']);

        $message = new Message('Produit supprimé du panier');
        $message->prix = \Slrfw\Format\Number::formatMoney($panier->getPrix());
        $message->nbProduits = $panier->getNombre();
        $message->port = \Slrfw\Format\Number::formatMoney($panier->getPort());
        $message->total = \Slrfw\Format\Number::formatMoney($panier->getTotal());
        $message->addRedirect("shop/panier.html", 3);
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
        list($livraison, $mode) = $form->run(\Slrfw\Formulaire::FORMAT_LIST);
        $hook->form = $form->getArray();
        unset($form);

        /* = Enregistrement de la commande
          ------------------------------- */
        $panier = $this->loadPanier();
        if ($panier->getNombre() == 0) {
            throw new \Slrfw\Exception\User('Aucun Panier en cours');
        }

        $hook->exec('control');

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

    public function paiementAccepteAction()
    {

    }

    public function paiementRefuseAction()
    {

    }
}

