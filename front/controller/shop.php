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

        echo '<pre>' . print_r($panier, true) . '</pre>';

        $query = 'SELECT id '
               . 'FROM ' . $panier->config->get('table', 'reference') . ' p '
               . 'WHERE p.id_gab_page = ' . $produit;
        $refId = $this->_db->query($query)->fetch(\PDO::FETCH_COLUMN);


        $panier->ajoute($refId, $qte);

        $message = new \Slrfw\Message('Produit ajouté au panier');
        $message->total = \Slrfw\Format\Number::formatMoney($panier->getPrix());
        $message->produitQte = $panier->getNombre($refId);
        $message->produitPrix = \Slrfw\Format\Number::formatMoney($panier->getPrix($refId));
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
            $front = \Slrfw\FrontController::getInstance();
            $front->action = 'paniervide';
            return true;
        }

        $client = $this->chargeCompte(false);
        if ($client !== false) {
            $this->_view->actionPanier = 'panier-adresse';
            $this->_view->client = $client->getInfo();
        } else {
            $this->_view->actionPanier = 'panier-login';
        }

        $this->_view->panier = $panier->getInfo();
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

        $client = $this->chargeCompte();
        $infoClient = $client->getInfo();

        /** Chargement des données **/
        $form = $this->chargeForm('passercommande.form.ini');
        list($livraison, $modeLib) = $form->run(\Slrfw\Formulaire::FORMAT_LIST);
        unset($form);


        /* = Recherche du mode de payement
          ------------------------------- */
        $modes = array(
            'CB' => array('cb'),
            'Cheque' => array('chèque'),
        );
        $modeLib = strtolower($modeLib);
        $mode = null;
        foreach ($modes as $code => $tests) {
            foreach ($tests as $test) {
                if (stripos($modeLib, $test) !== false) {
                    $mode = $code;
                    break;
                }
            }
            if (!empty($mode)) {
                break;
            }
        }
        if (empty($mode)) {
            throw new \Slrfw\Exception\Marvin(
                new \Slrfw\Exception\Lib('Pas de valeur de mode de payement correspondante'),
                'Erreur enregistrement commande'
            );
        }
        unset($modeLib, $modes, $tests, $code);


        /* = Contrôle de l'adresse de livraison
          ------------------------------- */
        $livAdresse = null;
        foreach ($infoClient['adresses'] as $adresse) {
            if ($adresse['id'] == $livraison) {
                $livAdresse = $adresse;
                break;
            }
        }

        if (empty($livAdresse)) {
            throw new \Slrfw\Exception\User('Veuillez Choisir une adresse de livraison');
        }

        /* = Enregistrement de la commande
          ------------------------------- */
        $panier = $this->loadPanier();
        if ($panier->getNombre() == 0) {
            throw new \Slrfw\Exception\User('Aucun Panier en cours');
        }
        $className = \Slrfw\FrontController::searchClass('Lib\Commande', false);
        $commande = new $className();
        $commande->panierToCommande($mode, $panier);

        /* = Enregistrement des adresses
          ------------------------------- */
        $query = 'UPDATE boutique_commande SET '
               . ' id_adresse_livraison = ' . $livAdresse['id'] . ', '
               . ' id_adresse_facturation = ' . $infoClient['adressePrincipale']['id'] . ', '
               . ' id_client = ' . $infoClient['id'] . ' '
               . 'WHERE id = ' . $commande->getId();
        try {
            $this->_db->exec($query);
        } catch (\PDOException $exc) {
            throw new \Slrfw\Exception\Marvin($exc, "Erreur enregistrement commande");
        }

        /* = Finalisation de la commande
          ------------------------------- */
        $method = 'passerCmde' . $mode;
        if (!method_exists('shopController', $method)) {
            throw new \Slrfw\Exception\Marvin(
                new \Slrfw\Exception\Lib("Methode de payement $mode non gérée"),
                "Erreur enregistrement commande"
            );
        }

        /* = Application des stocks
          ------------------------------- */
        $commande->decrementeStocks();

        $this->$method($commande, $client);
    }

    /**
     *
     * @param \Vel\lib\Commande $commande Commande en cours
     * @param \Vel\lib\Client   $client   Informations client
     *
     * @return void
     */
    protected function passerCmdeCB(\Vel\lib\Commande $commande, \Vel\lib\Client $client)
    {
        $config = new Config('config/shop/banque.ini');

        $date = gmdate("YmdHis", time());

        $sign = $config->get('version', 'config') . "+"
              . $config->get('site_id', 'client') . "+"
              . $config->get('mode', 'config') . "+"
              . $trans_id . "+"
              . $date . "+"
              . '' . "+"
              . '' . "+"
              . 'SINGLE' . "+"
              . '' . "+"
              . $amount . "+"
              . $config->get('currency', 'config') . "+"
              . $config->get('key', 'client');
        $signature = sha1($sign);

        $form = '<html>'
              . '<head></head>'
              . '<body onLoad="document.forms[0].submit()">'
              . '<form name="paiment" method="POST" action="https://systempay.cyberpluspaiement.com/vads-payment/">'
              . '<input type="hidden" name="ctx_mode" value="' . $config->get('mode', 'config') . '" />'
              . '<input type="hidden" name="amount" value="' . $amount . '" />'
              . '<input type="hidden" name="capture_delay" value="" />'
              . '<input type="hidden" name="currency" value="' . $config->get('currency', 'config') . '" />'
              . '<input type="hidden" name="payment_cards" value="" />'
              . '<input type="hidden" name="payment_config" value="SINGLE" />'
              . '<input type="hidden" name="site_id" value="' . $config->get('site_id', 'client') . '" />'
              . '<input type="hidden" name="trans_date" value="' . $date . '" />'
              . '<input type="hidden" name="trans_id" value="' . $trans_id . '" />'
              . '<input type="hidden" name="validation_mode" value="" />'
              . '<input type="hidden" name="version" value="V1" />'
              . '<input type="hidden" name="url_success" value="http://www.facilenfil.com/payement-accepte.html" />'
              . '<input type="hidden" name="url_refused" value="http://www.facilenfil.com/payement-refuse.html" />'
              . '<input type="hidden" name="url_referral" value="http://www.facilenfil.com/payement-refuse.html" />'
              . '<input type="hidden" name="url_cancel" value="http://www.facilenfil.com/payement-refuse.html" />'
              .'<input type="hidden" name="signature" value="' . $signature . '" />'
              . '</form></body></html>';

        echo $form;
    }

    /**
     * Retour banque
     *
     */
    public function retourBanqueAction()
    {

        $log = new Log('../log/payment.log');

        $log->logThis($_POST);

        $inscription = new formulaire('banque.form.ini');
        try {
            $bank = $inscription->run();
        } catch (Exception $exc) {
            $this->pageNotFound();
            die();
        }

        $log->logThis($bank);


        $config = new Config("config/shop/banque.ini");
        $chaine = $bank['version'] . "+"
                . $bank['site_id'] . "+"
                . $bank['ctx_mode'] . "+"
                . $bank['trans_id'] . "+"
                . $bank['trans_date'] . "+"
                . $bank['validation_mode'] . "+"
                . $bank['capture_delay'] . "+"
                . $bank['payment_config'] . "+"
                . $bank['card_brand'] . "+"
                . $bank['card_number'] . "+"
                . $bank['amount'] . "+"
                . $bank['currency'] ."+"
                . $bank['auth_mode'] ."+"
                . $bank['auth_result'] . "+"
                . $bank['auth_number'] ."+"
                . $bank['warranty_result'] . "+"
                . $bank['payment_certificate'] ."+"
                . $bank['result'] ."+"
                . $bank['hash'] . "+"
                . $config->get('key', 'client');
        $signature = sha1($chaine);
        if ($bank['signature'] != $signature) {
            $this->pageNotFound();
        }

        if ($bank['result'] == "00") {
            $className = \Slrfw\FrontController::searchClass('Lib\Commande', false);
            $commande = new $className();
            $commande->set($bank['trans_id']);

            $commande->changePourPaye('CB');

            $mail = new Mail('payement');
            $mail->cmde = $commande->getAll();
            $mail->send();
        }


    }


    /**
     * Finalise la validation d'une commande en mode Chèque
     */
    protected function passerCmdeCheque(\Vel\lib\Commande $commande, \Vel\lib\Client $client)
    {
        $commande->changeEtat('attentPayement', 'Cheque');

        /* = Vidage du panier
          ------------------------------- */
        $panier = Panier::run();
        $panier->vide();

        $mail = new Mail('cheque');
        $mail->cmde = $commande->getAll();
        $mail->send();

        $message = new Message("Enregistrement de la commande");
        $message->addRedirect("compte/start.html", 3);
        $message->display();
    }


    public function paiementAccepteAction()
    {

    }

    public function paiementRefuseAction()
    {

    }
}

