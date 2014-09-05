<?php
/**
 * Module de gestion de panier.
 * Mise en panier, passage de commande.
 *
 * @package    Vel
 * @subpackage Library
 * @author     Adrien <aimbert@solire.fr>
 * @license    CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */

namespace Vel\Lib;

/**
 * Fonctionnalités de base d'un panier
 *
 * @package    Vel
 * @subpackage Library
 * @author     Adrien <aimbert@solire.fr>
 * @license    CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */
class Panier
{
    /**
     * Chemin vers le fichier de configuration
     */
    const CONFIG_PATH = 'config/panier.ini';

    /**
     * Identifiant du panier
     *
     * @var int
     */
    protected $id = null;

    /**
     * Connection à la base de données.
     * @var \Slrfw\MyPDO
     */
    protected $db = null;

    /**
     * Configuration du module de panier
     *
     * @var \Slrfw\Config
     */
    public $config = null;

    /**
     * Configuration de la bdd
     *
     * @var \Slrfw\Config
     */
    public $tableConf = null;

    /**
     * Identifiant de la zone / région
     *
     * @var type
     */
    protected $idRegion = 1;

    /**
     * Variable présente pour n'instancier qu'une fois la classe Panier
     *
     * @var self
     * @ignore
     */
    private static $single = false;


    /**
     * Création ou récupération du panier utilisateur
     *
     * @return void
     * @uses \Slrfw\Config pour charger la configuration
     * @uses \Slrfw\Registry récupération de la bdd
     * @uses \Slrfw\FrontController recherche de fichiers .ini
     */
    public function __construct($id = null)
    {
        if (self::$single !== false) {
            return;
        }
        self::$single = true;

        $path = \Slrfw\FrontController::search(self::CONFIG_PATH, false);
        $this->config = new \Slrfw\Config($path);
        unset($path);

        $path = \Slrfw\FrontController::search('config/sqlVel.ini', false);
        $this->tableConf = new \Slrfw\Config($path);
        unset($path);

        $this->db = \Slrfw\Registry::get('db');
        $cookieName = $this->config->get('session', 'cookieName');
        if ($id !== null) {
            $query = 'SELECT id, id_region '
                   . 'FROM ' . $this->tableConf->get('table', 'panier') . ' '
                   . 'WHERE id = ' . $id;
            $panier = $this->db->query($query)->fetch();
            if (empty($panier)) {
                $this->create();
                return null;
            }
            $this->id = $panier['id'];
            $this->idRegion = $panier['id_region'];
        } elseif (isset($_COOKIE[$cookieName])) {
            $query = 'SELECT id, id_region '
                   . 'FROM ' . $this->tableConf->get('table', 'panier') . ' '
                   . 'WHERE cle = ' . $this->db->quote($_COOKIE[$cookieName]);
            $panier = $this->db->query($query)->fetch();
            if (empty($panier)) {
                $this->create();
                return null;
            }
            $this->id = $panier['id'];
            $this->idRegion = $panier['id_region'];
        } else {
            $this->create();
        }
    }

    /**
     * Création d'un nouveau panier
     *
     * @return void
     */
    protected function create()
    {
        $cle = $this->genereCle();
        $cookieName = $this->config->get('session', 'cookieName');
        $expire = time()
                + (int) $this->config->get('session', 'cookieDuration');

        setcookie($cookieName, $cle, $expire, '/');

        $query = 'INSERT INTO ' . $this->tableConf->get('table', 'panier')
               . ' (cle) VALUES ( ' . $this->db->quote($cle) . ' );';
        $this->db->exec($query);
        $this->id = $this->db->lastInsertId();
    }

    /**
     * Modifie la date de dernier edition du panier
     *
     * @return void
     */
    protected function hit()
    {
        $query = 'UPDATE ' . $this->tableConf->get('table', 'panier') . ' '
               . 'SET hit = NOW() '
               . 'WHERE id = ' . $this->id;
        $this->db->exec($query);
    }

    /**
     * Renvois l'identifiant du panier
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Ajoute un produit au panier
     *
     * @param int $idRef Identifiant de la référence à ajouter
     * @param int $qte   Quantité de la référence
     *
     * @return void
     * @throws \Slrfw\Exception\Lib $qte < 0 alors que le produit n'est pas dans le
     * panier
     */
    public function ajoute($idRef, $qte)
    {
        $tableLigne = $this->tableConf->get('table', 'panierLigne');

        $this->hit();

        $query = 'SELECT quantite '
               . 'FROM ' . $tableLigne . ' tl '
               . 'WHERE id_panier = ' . $this->id . ' '
               . 'AND id_reference = ' . $idRef;
        $quantite = $this->db->query($query)->fetch(\PDO::FETCH_COLUMN);
        if ($quantite > 0) {
            /*
             * Produit déjà présent
             */
            if (($quantite + $qte) <= 0) {
                $this->supprime($idRef);
                return true;
            } else {
                $query = 'UPDATE ' . $tableLigne
                       . ' SET quantite = quantite + ' . $qte . ' '
                       . 'WHERE id_panier = ' . $this->id
                       . ' AND id_reference = ' . $idRef;
            }
        } else {
            if ($qte < 0) {
                throw new \Slrfw\Exception\Lib($this->config->get('erreur', 'ajoutQte'));
            }
            /*
             * On ajoute le produit dans le panier
             */
            $query = $this->enregistreNouveauProduit($idRef, $qte);
        }

        try {
            $this->db->exec($query);
        } catch (\PDOException $exc) {
            unset($exc);
            throw new \Slrfw\Exception\Lib($this->config->get('erreur', 'ajoutSql'));
        }
    }

    /**
     * Enregistrement d'un nouveau produit
     *
     * @param int $idRef identifiant de la référence
     * @param int $qte   quantité du produit à ajouter
     *
     * @return string requête sql pour l'insertion
     * @throws \Slrfw\Exception\Lib
     */
    protected function enregistreNouveauProduit($idRef, $qte)
    {
        $info = array();

        /*
         * Recherche des champs à remplir directement dans la table panier
         */
        $query = 'DESC ' . $this->tableConf->get('table', 'panierLigne') . ' ';
        $archiLigne = $this->db->query($query)->fetchAll(\PDO::FETCH_COLUMN, 0);

        $query = 'DESC ' . $this->tableConf->get('table', 'reference');
        $archiRef = $this->db->query($query)->fetchAll(\PDO::FETCH_COLUMN, 0);

        $ignoreList = array('id');
        foreach ($archiLigne as $column) {
            if (in_array($column, $ignoreList)) {
                continue;
            }

            if (in_array($column, $archiRef)) {
                $info[] = $column;
            }
        }
        unset($query, $ignoreList, $archiLigne, $archiRef, $column);


        /*
         * Récupération de la référence
         */
        $query = 'SELECT ' . implode(', ', $info) . ' '
               . 'FROM ' . $this->tableConf->get('table', 'reference') . ' '
               . 'WHERE id = ' . $idRef . ' '
               . ' AND suppr = 0 '
               . ' AND visible = 1 ';
        try {
            $ref = $this->db->query($query)->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $exc) {
            throw new \Slrfw\Exception\Lib(
                $this->config->get('erreur', 'ajoutRef'),
                1,
                $exc
            );
        }
        if (empty($ref)) {
            throw new \Slrfw\Exception\Lib($this->config->get('erreur', 'ajoutRefNo'));
        }

        /*
         * Création des données à insérer
         */
        $data = array(
            'quantite'      => $qte,
            'id_panier'     => $this->id,
            'id_reference'  => $idRef,
        );

        $prices = $this->chargePrix($idRef);

        if (is_array($prices)) {
            $data += $prices;
        }
        unset($prices);

        foreach ($ref as $key => $value) {
            $data[$key] = $value;
        }

        /*
         * Formatage de la requête
         */
        $set = array();
        foreach ($data as $key => $value) {
            $set[] = '`' . $key . '` = ' . $this->db->quote($value);
        }

        $query = 'INSERT INTO ' . $this->tableConf->get('table', 'panierLigne') . ' SET '
               . ' ' . implode(', ', $set) . ' ';

        return $query;
    }

    /**
     * Renvois les données de prix
     *
     * @param int $idRef identifiant de la référence
     *
     * @return array
     */
    protected function chargePrix($idRef)
    {
        $query = 'SELECT prix_ttc, prix_ht, taxe '
               . 'FROM ' . $this->tableConf->get('table', 'referenceRegion') . ' rr '
               . 'WHERE rr.id_bloc = ' . $idRef . ' '
               . ' AND rr.id_region = ' . $this->idRegion . ' ';
        $prices = $this->db->query($query)->fetch(\PDO::FETCH_ASSOC);

        return $prices;
    }

    /**
     * Supprime une référence du panier
     *
     * @param int $idRef identifiant de la référence
     *
     * @return void
     */
    public function supprime($idRef)
    {
        $this->hit();
        $tableLigne = $this->tableConf->get('table', 'panierLigne');

        $query = 'DELETE FROM ' . $tableLigne . ' '
               . 'WHERE id_reference = ' . $idRef
               . ' AND id_panier = ' . $this->id;
        $this->db->exec($query);

        /*
         * Supprimer le panier si celui-ci est vide
         */
        $query = 'SELECT COUNT(*) FROM ' . $tableLigne . ' '
               . 'WHERE id_panier = ' . $this->id;
        $count = $this->db->query($query)->fetch(\PDO::FETCH_COLUMN);

        if (!$count) {
            $tablePanier = $this->tableConf->get('table', 'panier');
            $query = 'DELETE FROM ' . $tablePanier . ' '
                   . 'WHERE id = ' . $this->id;
            $this->db->exec($query);

            $cookieName = $this->config->get('session', 'cookieName');
            setcookie($cookieName, '', time() - 3600);

        }
    }

    /**
     * Calcul et renvois les ports du panier
     *
     * @return float
     * @todo finaliser la function
     */
    public function getPort()
    {
        $port = 0;

        if ($this->config->get('methode', 'port') == 'ini') {
            $port = $this->config->get('port', 'montant');

            if ($this->config->get('port', 'franco')) {
                $prix = $this->getPrix();
                $franco = $this->config->get('port', 'franco');
                if ((float) $prix >= (float)$franco) {
                    $port = 0;
                }
            }
            return $port;
        }

        return $port;
    }


    /**
     * Renvois le prix total du panier
     * <br/>Soit le prix et les frais de port
     *
     * @return flaot
     */
    public function getTotal()
    {
        $prix = $this->getPrix();
        $prix += $this->getPort();

        return $prix;
    }

    /**
     * Renvois le montant hors taxes du panier
     *
     * @return float
     */
    public function getHT()
    {
        $methode = $this->config->get('methode', 'prixHT');
        $query = 'SELECT SUM((' . $methode . ') * quantite) '
               . 'FROM ' . $this->tableConf->get('table', 'panierLigne') . ' '
               . 'WHERE id_panier = ' . $this->id;
        $prix = $this->db->query($query)->fetch(\PDO::FETCH_COLUMN);

        return $prix;
    }

    /**
     * Renvois le montant total du panier (sans les frais de port)
     *
     * @param int $idRef Identifiant de référence,
     *
     * @return float
     */
    public function getPrix($idRef = null)
    {
        $methode = $this->config->get('methode', 'prixTTC');

        $query = 'SELECT SUM((' . $methode . ') * quantite) '
               . 'FROM ' . $this->tableConf->get('table', 'panierLigne') . ' '
               . 'WHERE id_panier = ' . $this->id;

        if (!empty($idRef)) {
            $query .= ' AND id_reference = ' . $idRef;
        }
        $prix = $this->db->query($query)->fetch(\PDO::FETCH_COLUMN);

        return $prix;
    }

    /**
     * Renvois le nombre de produits dans le panier
     * Si $idRef est précisé, c'est le nombre de cette référence qui est renvoyé
     *  et non le nombre de produit total du panier
     *
     * @param int $idRef Identifiant de référence,
     *
     * @return int
     */
    public function getNombre($idRef = null)
    {
        if (empty($idRef)) {
            $query = 'SELECT SUM(quantite) '
                   . 'FROM ' . $this->tableConf->get('table', 'panierLigne') . ' '
                   . 'WHERE id_panier = ' . $this->id;
        } else {
            $query = 'SELECT SUM(quantite) '
                   . 'FROM ' . $this->tableConf->get('table', 'panierLigne') . ' '
                   . 'WHERE id_panier = ' . $this->id . ' '
                   . ' AND id_reference = ' . $idRef;
        }
        $count = $this->db->query($query)->fetch(\PDO::FETCH_COLUMN);

        if (empty($count)) {
            $count = 0;
        }

        return $count;
    }

    /**
     * Renvois toutes les informations du panier
     *
     * @return array
     */
    public function getInfo()
    {
        $query = 'SELECT pl.*, gp.titre nom '
               . 'FROM ' . $this->tableConf->get('table', 'panierLigne') . ' pl '
               . 'INNER JOIN ' . $this->tableConf->get('table', 'reference') . ' r '
               . ' ON r.id = pl.id_reference '
               . 'INNER JOIN gab_page gp '
               . ' ON gp.id = r.id_gab_page '
               . 'WHERE pl.id_panier = ' . $this->id;
        $lignes = $this->db->query($query)->fetchAll();

        for ($i = 0; $i < count($lignes); $i++) {
            $lignes[$i]['prixTotal'] = $lignes[$i]['prix_ttc'] * $lignes[$i]['quantite'];
        }

        $lignes['total'] = $this->getTotal();
        $lignes['prix'] = $this->getPrix();
        $lignes['prixHT'] = $this->getHT();
        $lignes['port'] = $this->getPort();

        return $lignes;
    }

    /**
     * Supprime toutes les informations du panier
     *
     * @return void
     */
    public function vide()
    {
        $query = 'DELETE FROM ' . $this->tableConf->get('table', 'panierLigne') . ' '
               . 'WHERE id_panier = ' . $this->id;
        $this->db->exec($query);
        $query = 'DELETE FROM ' . $this->tableConf->get('table', 'panier') . ' '
               . 'WHERE id = ' . $this->id;
        $this->db->exec($query);

        $this->id = null;
    }

    /**
     * Génère une clé pour identifier le panier
     *
     * @return string clé unique pour identifier un panier
     */
    protected function genereCle()
    {
        $use = false;
        do {
            $cle = \Slrfw\Format\String::random(32);
            $query = 'SELECT COUNT(*) '
                   . 'FROM ' . $this->tableConf->get('table', 'panier') . ' '
                   . 'WHERE cle = ' . $this->db->quote($cle);
            $use = $this->db->query($query)->fetch(\PDO::FETCH_COLUMN);
        } while ($use);

        return $cle;
    }
}
