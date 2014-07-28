<?php
/**
 * Gestionnaire des commandes
 *
 * @package    Vel
 * @subpackage Library
 * @author     Adrien <aimbert@solire.fr>
 * @license    CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */

namespace Vel\Lib;

/**
 * Gestionnaire des commandes
 *
 * @package    Vel
 * @subpackage Library
 * @author     Adrien <aimbert@solire.fr>
 * @license    CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */
class Commande
{
    /**
     * Lien vers le fichier de configuration des commandes
     */
    const CONFIG_PATH = 'config/commande.ini';

    /**
     * Connection à la base de données.
     * @var \Slrfw\MyPDO
     */
    protected $db = null;
    /**
     * Configuration du module client
     * @var \Slrfw\Config
     */
    protected $config = null;

    /**
     * Configuration du module client
     * @var \Slrfw\Config
     */
    protected $configSql = null;

    /**
     * identifiant de la commande
     * @var int
     */
    protected $id = null;

    /**
     * Création d'un nouvel objet Commande
     */
    final public function __construct()
    {
        $path = \Slrfw\FrontController::search(self::CONFIG_PATH, false);
        $this->config = new \Slrfw\Config($path);

        $path = \Slrfw\FrontController::search('config/sqlVel.ini', false);
        $this->configSql = new \Slrfw\Config($path);

        $this->db = \Slrfw\Registry::get('db');
    }

    /**
     * Renvois les informations de configuration de la commande
     *
     * @param string $section Identifiant du paramètre de configuration
     * @param string $key     Second Identifiant du paramètre de configuration
     *
     * @return array|string
     */
    public function config($section, $key = null)
    {
        return $this->config->get($section, $key);
    }

    /**
     * Listes les commandes payées / non livrées
     *
     * @return array
     */

    /**
     * Listes les commandes payées / non livrées
     *
     * @param array $where   liste d'éléments à mettre dans le where (il y aura
     * un implode(' AND ', $where) de fait)
     * @param array $orderBy tableau des éléments sur lequel trié suivi de ASC
     * ou DESC (il y aura un implode(', ', $orderBy) de fait)
     * @param int   $offset  premiere index de ligne a récupérer
     * @param int   $length  nombre de commandes à récupérer
     *
     * @return array
     * @see liste()
     */
    public function listeEnCours(
        array $select = null,
        array $where = null,
        array $orderBy = null,
        $offset = null,
        $length = null
    ) {
        if (empty($where)) {
            $where = array();
        }
        $where[] = $this->sqlEtat('paye') . ' OR ' . $this->sqlEtat('attentPayement');

        if (empty($select)) {
            $select = array('*');
        }

        $liste = $this->liste($select, $where);
        foreach ($liste as &$commande) {
            if (isset($commande['etat'])) {
                $etat = $this->infoEtat($commande['etat']);
                $commande['couleur'] = $etat->couleur;
                if ($commande['etat'] >= 400 && $commande['etat'] <= 499) {
                    $commande['paye'] = 'oui';
                } else {
                    $commande['paye'] = 'non';
                }
            }
        }
        return $liste;
    }

    /**
     * Listes les commandes expediée
     *
     * @return array
     */
    public function listeExp()
    {
        $where = array();
        $where[] = $this->sqlEtat('expedie');
        $select = array('c.*');
        return $this->liste($select, $where);
    }

    /**
     * Liste les commandes
     *
     * @param array $select  liste d'élements à mettre dans le select (il y
     * aura un implode(',', $select) de fait)
     * @param array $where   liste d'éléments à mettre dans le where (il y aura
     * un implode(' AND ', $where) de fait)
     * @param array $orderBy tableau des éléments sur lequel trié suivi de ASC
     * ou DESC (il y aura un implode(', ', $orderBy) de fait)
     * @param int   $offset  premiere index de ligne a récupérer
     * @param int   $length  nombre de commandes à récupérer
     *
     * @return array liste des commande formatés
     */
    protected function liste(
        array $select = null,
        array $where = null,
        array $orderBy = null,
        $offset = null,
        $length = null
    ) {
        if (empty($select)) {
            $select = array('*');
        }

        if (empty($where)) {
            $where = array('1');
        }

        if (empty($orderBy)) {
            $orderBy = array('date DESC');
        }

        $limit = '';
        if ($offset !== null
            && $length !== null
        ) {
            $limit = ' LIMIT ' . $offset . ', ' . $length;
        }

        // Gestion des jointures de tables
        $join = array();
        foreach ($select as $value) {
            if (strpos($value, 'bc') === 0 && !isset($join['client'])) {
                $clientClass = \Slrfw\FrontController::searchClass('Lib\Client');
                $client = new $clientClass();
                unset($clientClass);

                $join['client'] = 'INNER JOIN '
                                . $client->config('table', 'client') . ' bc '
                                . ' ON bc.id = c.id_client ';
                unset($client);
            }
        }

        $query = 'SELECT ' . implode(', ', $select) . ' '
               . ' FROM ' . $this->configSql->get('table', 'commande') . ' c '
               . ' ' . implode(' ', $join) . ' '
               . 'WHERE ' . implode(' AND ', $where) . ' '
               . 'ORDER BY ' . implode(', ', $orderBy) . ' '
               . $limit;
        $commandes = $this->db->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        // Présentation des Champs
        $className = \Slrfw\FrontController::searchClass('Lib\Format', false);
        $format = new $className($this->config('presentation'));

        for ($i = 0; $i < count($commandes); $i++) {
            $commandes[$i] = $format->formatAll($commandes[$i]);
        }

        return $commandes;
    }

    /**
     * Donne un identifiant à l'objet
     *
     * @param int $id identifiant de la commande
     *
     * @return void
     * @throws \Slrfw\Exception\Lib doubleInit
     */
    final public function set($id)
    {
        if (!empty($this->id)) {
            throw new \Slrfw\Exception\Lib($this->config('erreur', 'doubleInit'));
        }

        $this->id = $id;
    }

    /**
     * Renvois l'identifiant de la commande
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Génère le "where" pour capturer les etats répondant au code
     *  'paye' , 'expedie' etc...
     *
     * @param string $code code de l'état à rechercher
     *
     * @return string
     * @throws \Slrfw\Exception\Lib si aucun etat trouvé pour le code
     */
    protected function sqlEtat($code)
    {
        $name = 'mask' . ucfirst($code);
        $mask = $this->config('etat', $name);

        if (empty ($mask)) {
            $message = $this->config('erreur', 'sqlEtat');
            throw new \Slrfw\Exception\Lib($message);
        }

        $min = (int) str_replace('xx', '00', $mask);
        $max = (int) str_replace('xx', '99', $mask);

        return ' etat BETWEEN ' . $min . ' AND ' . $max . ' ';
    }

    /**
     * Renvois le label et la couleur de l'etat
     *
     * @param int $etat Numéro de l'etat
     *
     * @return object
     * @throws \Slrfw\Exception\Lib si l'état n'est pas trouvé dans la config
     */
    protected function infoEtat($etat)
    {
        $etats = $this->config('etat');
        $idEtat = $etat[0] . 'xx';
        $codeEtat = null;
        foreach ($etats as $nom => $code) {
            if (strpos($nom, 'mask') === false) {
                continue;
            }

            if ($idEtat == $code) {
                $codeEtat = str_replace('mask', '', $nom);
                break;
            }
        }

        if (empty($codeEtat)) {
            $message = $this->config('erreur', 'sqlEtat');
            throw new \Slrfw\Exception\Lib($message);
        }

        $foo = new \stdClass();
        $foo->label = $this->config('etat', 'label' . $codeEtat);
        $foo->couleur = $this->config('etat', 'couleur' . $codeEtat);

        return $foo;
    }

    /**
     * Renvois le numéro d'état d'une commande pour l'état et le $code précisé
     *
     * @param string $etat Libélé de l'état; ex paye, AttentPayement
     * @param string $code Code précisant le libélé (cb etc..)
     *
     * @return int
     * @throws ShopException
     */
    protected function cherchEtat($etat, $code)
    {
        $name = $etat . ucfirst($code);
        $cle = $this->config('etat', $name);
        if (empty ($cle)) {
            $message = $this->config('erreur', 'chercheEtat');
            throw new \Slrfw\Exception\Lib($message);
        }

        return $cle;
    }

    /**
     * Change l'etat de la commande pour payé
     *
     * @param int $code permet de préciser l'etat de la commande (payée par
     * chèque, payée par CB, via X ou Y) voir le fichier de configuration,
     * section etat pour plus de précisions
     *
     * @return void
     */
    public function changePourPaye($code = '')
    {
        $nouvEtat = $this->cherchEtat('paye', $code);
        $this->editEtat($nouvEtat);

        // Modification de la date de réglement
        $dateReg = $this->configSql->get('table', 'dateReglement');
        if (!empty($dateReg)) {
            $query = 'UPDATE ' . $this->configSql->get('table', 'commande') . ' '
                   . 'SET `' . $dateReg . '` = NOW() '
                   . 'WHERE id = ' . $this->id;
            $this->db->exec($query);
        }
    }

    /**
     * Change l'etat de la commande pour expédiée
     *
     * @param int $code permet de préciser l'etat de la commande (expédiée via
     * tel transporteyr ou tel autre) voir le fichier de configuration,
     * section etat pour plus de précisions
     *
     * @return void
     */
    public function changePourExpedie($code = '')
    {
        $nouvEtat = $this->cherchEtat('expedie', $code);
        $this->editEtat($nouvEtat);
    }

    /**
     * Change l'etat de la commande
     * (fonction "manuelle")
     *
     * Il faut se reporter au fichier de configuration pour remplire
     * correctement les champs $etape et $code
     *
     * @param string $etape code identifiant l'étapt (expedition, payement ...)
     * @param string $code  code d'information supplémentaire cf .ini
     *
     * @return void
     */
    public function changeEtat($etape, $code = '')
    {
        $nouvEtat = $this->cherchEtat($etape, $code);
        $this->editEtat($nouvEtat);
    }

    /**
     * Définit le client d'une commande
     * (fonction "manuelle")
     *
     * @param int $clientId identifiant du client
     *
     * @return void
     */
    public function setClient($clientId)
    {
        $query = 'UPDATE ' . $this->configSql->get('table', 'commande') . ' '
               . 'SET id_client = ' . $clientId . ' '
               . 'WHERE id = ' . $this->id;
        try {
            $this->db->exec($query);
        } catch (PDOException $exc) {
            unset($exc);
            $message = $this->config('erreur', 'setClient');
            throw new \Slrfw\Exception\Lib($message);
        }
    }

    /**
     * Traitement de l'edition d'état de la commande
     *
     * @param int $etat numéro de l'état dans lequel faire passer la commande
     *
     * @return void
     * @throws \Slrfw\Exception\Lib si problèmes lors de l'édition de l'état
     */
    protected function editEtat($etat)
    {
        $query = 'UPDATE ' . $this->configSql->get('table', 'commande') . ' '
               . 'SET etat = ' . $etat . ' '
               . 'WHERE id = ' . $this->id;
        try {
            $this->db->exec($query);
        } catch (PDOException $exc) {
            unset($exc);
            $message = $this->config('erreur', 'changeEtat');
            throw new \Slrfw\Exception\Lib($message);
        }
    }


    /**
     * Passe un panier à l'état d'une commande
     * la commande aura comme etat "attente de payement"
     * seules les informations extraites du paniers sont ajoutées
     *
     * @param array           $data   données supplémentaire à insérer dans la
     * commande
     * @param \Vel\Lib\Panier $panier Panier courant
     *
     * @return int Id de la commande
     * @throws LibException si le mode de paiement n'existe pas
     * @throws MarvinException si il y a un problème à l'enregistrement
     */
    public function panierToCommande($data, \Vel\Lib\Panier $panier)
    {
        $etat = $this->cherchEtat('attentPayement', $modeReg);

        if (empty($etat)) {
            $message = $this->config('erreur', 'modeRegIncorrect');
            throw new \Slrfw\Exception\Lib($message);
        }

        $reference = $this->genereReference();

        $data = array_merge(array(
            'total'     => $panier->getTotal(),
            'total_ht'  => $panier->getHT(),
            'total_ttc' => $panier->getPrix(),
            'port'      => $panier->getPort(),
            'date'      => date('Y-m-d H:i:s'),
            'reference' => $reference,
            'etat'      => $etat,
        ), $data);

        /*
         * Insertion de la commande
         */
        $this->db->insert($this->configSql->get('table', 'commande'), $data);
        $this->id = $this->db->lastInsertId();

        /*
         * Insertion des lignes du panier dans la commande
         */
        $query = 'DESC ' . $this->configSql->get('table', 'commandeLigne');
        $desc = $this->db->query($query)->fetchAll(\PDO::FETCH_COLUMN);

        $ignore = array('id', 'id_panier', 'id_commande');

        foreach ($desc as $key => $value) {
            if (in_array($value, $ignore)) {
                unset($desc[$key]);
            }
        }

        $query = 'INSERT INTO ' . $this->configSql->get('table', 'commandeLigne') . ' '
               . 'SELECT "", ' . $this->id . ', ' . implode(', ', $desc) . ' '
               . 'FROM ' . $this->configSql->get('table', 'panierLigne') . ' '
               . 'WHERE id_panier = ' . $panier->getId();
        try {
            $this->db->exec($query);
        } catch (\PDOException $exc) {
            $etat = $this->config('etat', 'annuleEnregistrement');
            $query = 'UPDATE ' . $this->configSql->get('table', 'commande') . ' '
                   . 'SET  etat = ' . $etat . ' '
                   . 'WHERE id = ' . $this->id;
            $this->db->exec($query);
            unset($this->id);
            throw new \Slrfw\Exception\Marvin(
                $exc,
                "Erreur lors de l'enregistrement d'une commande"
            );
        }

        return $this->id;
    }

    public function getInfoSimples()
    {
        $query = 'SELECT * '
               . 'FROM ' . $this->configSql->get('table', 'commande') . ' '
               . 'WHERE id = ' . $this->id . ' ';
        return $this->db->query($query)->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Renvois les données complètes de la commande (hors client)
     *
     * @param \Slrfw\Model\gabaritManager $manager gabarit manager courant
     *
     * @return array tableau des données
     */
    public function getInfo(\Slrfw\Model\gabaritManager $manager)
    {
        $this->data = array();

        $this->data = $this->getInfoSimples();

        $query = 'SELECT * '
               . 'FROM ' . $this->configSql->get('table', 'commandeLigne') . ' '
               . 'WHERE id_commande = ' . $this->id . ' ';
        $this->data['lines'] = $this->db->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        /*
         * Chargement des informations produit
         */
        foreach ($this->data['lines'] as $key => $value) {
            $query = 'SELECT id_gab_page '
                   . 'FROM ' . $this->configSql->get('table', 'reference') . ' r '
                   . 'WHERE r.id = ' . $value['id_reference'] . ' ';
            $idGabPage = $this->db->query($query)->fetchColumn();
            $page = $manager->getPage(
                ID_VERSION,
                $this->configSql->get('global', 'idApi'),
                $idGabPage
            );

            $this->data['lines'][$key]['reference'] = $page;
        }

        return $this->data;
    }

    /**
     * Génère la référence unique de la commande
     *
     * La référence est une chaine de 10 chiffres
     *
     * @return string
     */
    protected function genereReference()
    {
        do {
            $ref = \Slrfw\Format\String::random(
                6,
                \Slrfw\Format\String::RANDOM_NUMERIC
            );

            $query = 'SELECT reference '
                   . 'FROM ' . $this->configSql->get('table', 'commande') . ' '
                   . 'WHERE reference = ' . $this->db->quote($ref) . ' ';

            $result = $this->db->query($query)->fetch();

        } while (!empty($result));

        return $ref;
    }
}
