<?php
/**
 * Module des comptes utilisateur
 *
 * @package    Controller
 * @subpackage Front
 * @author     Adrien <aimbert@solire.fr>
 * @license    Solire http://www.solire.fr/
 * @filesource
 */

namespace Vel\Front\Controller;

/**
 * Module des comptes utilisateur
 *
 * @package    Controller
 * @subpackage Front
 * @author     Adrien <aimbert@solire.fr>
 * @license    Solire http://www.solire.fr/
 */
class Commande
{
    /**
     * Lien vers le fichier de configuration des commandes
     */
    const CONFIG_PATH = 'config/commande.ini';

    /**
     * Connection à la base de données.
     * @var \PDO
     */
    protected $db = null;
    /**
     * Configuration du module client
     * @var Config
     */
    private $config = null;

    /**
     * identifiant de la commande
     * @var int
     */
    protected $_id = null;

    /**
     * Création d'un nouvel objet Commande
     */
    public final function __construct()
    {
        $path = \Slrfw\FrontController::search(self::CONFIG_PATH, false);
        $this->config = new \Slrfw\Config($path);

        $this->db = Registry::get('db');
    }

    /**
     * Renvois les informations de configuration de la commande
     *
     * @param string $key     Identifiant du paramètre de configuration
     * @param string $section Second Identifiant du paramètre de configuration
     *
     * @return array|string
     */
    public function config($section, $key = null)
    {
        return $this->config->get($section, $key);
    }

    /**
     * Liste les commandes
     * @param array $select
     * @param array $where
     * @return array
     */
    protected function _liste(array $select = null, array $where = null)
    {
        if (empty($select))
            $select = array('*');

        if (empty($where))
            $where = array();


        /* = Gestion des jointures de tables
          ------------------------------- */
        $join = array();
        foreach($select as $value) {
            if (strpos($value, 'bc') === 0 && !isset($join['client'])) {
                $client = new Client();
                $join['client'] = 'INNER JOIN '
                                . $client->config('table', 'client') . ' bc '
                                . ' ON bc.id = c.id_client ';
                unset($client);
            }
        }

        $query = 'SELECT ' . implode(', ', $select) . ' '
               . 'FROM ' . $this->config('table', 'commande') . ' c '
               . ' ' . implode(' ', $join) . ' '
               . 'WHERE ' . implode(' AND ', $where);
        $commandes = $this->db->query($query)->fetchAll(PDO::FETCH_ASSOC);

        /* = Présentation des Champs
          ------------------------------- */
        $format = new ShopFormat($this->config('presentation'));
        for ($i = 0; $i < count($commandes); $i++) {
            $commandes[$i] = $format->formatAll($commandes[$i]);
        }

        return $commandes;
    }

    /**
     * Donne un identifiant à l'objet
     *
     * @param int $id
     * @throws Exception doubleInit
     */
    public final function set($id)
    {
        if (!empty($this->id))
            throw new Exception($this->config('erreur', 'doubleInit'));

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
     * @param string $code
     * @return string
     * @throws ShopException
     */
    protected function _sqlEtat($code)
    {
        $name = 'mask' . ucfirst($code);
        $mask = $this->config('etat', $name);

        if (empty ($mask)) {
            $message = $this->config('erreur', 'sqlEtat');
            throw new ShopException($message);
        }

        $min = (int) str_replace('xx', '00', $mask);
        $max = (int) str_replace('xx', '99', $mask);

        return " etat BETWEEN $min AND $max ";
    }

    /**
     * Renvois le label et la couleur de l'etat
     *
     * @param int $etat Numéro de l'etat
     * @return object
     * @throws ShopException
     */
    protected function _infoEtat($etat)
    {
        $etats = $this->config('etat');
        $idEtat = $etat[0] . 'xx';
        $codeEtat = null;
        foreach ($etats as $nom => $code) {
            if (strpos($nom, 'mask') === false)
                continue;

            if ($idEtat == $code) {
                $codeEtat = str_replace('mask', '', $nom);
                break;
            }
        }

        if (empty($codeEtat)) {
            $message = $this->config('erreur', 'sqlEtat');
            throw new ShopException($message);
        }

        $foo->label = $this->config('etat', 'label' . $codeEtat);
        $foo->couleur = $this->config('etat', 'couleur' . $codeEtat);

        return $foo;
    }

    /**
     * Renvois le numéro d'état d'une commande pour l'état et le $code précisé
     * @param string $etat
     * @param string $code
     * @return int
     * @throws ShopException
     */
    protected function _cherchEtat($etat, $code)
    {
        $name = $etat . ucfirst($code);
        $cle = $this->config('etat', $name);
        if (empty ($cle)) {
            $message = $this->config('erreur', 'chercheEtat');
            throw new ShopException($message);
        }

        return $cle;
    }

    /**
     * Change l'etat de la commande pour payé
     *
     * @param int $code permet de préciser l'etat de la commande (payée par
     * chèque, payée par CB, via X ou Y) voir le fichier de configuration,
     * section etat pour plus de précisions
     */
    public function changePourPaye($code = '')
    {
        $nouvEtat = $this->_cherchEtat('paye', $code);
        $this->_changeEtat($nouvEtat);

        /* = Modification de la date de réglement
          ------------------------------- */
        $dateReg = $this->config('table', 'dateReglement');
        if (!empty($dateReg)) {
            $query = 'UPDATE ' . $this->config('table', 'commande') . ' '
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
     */
    public function changePourExpedie($code = '')
    {
        $nouvEtat = $this->_cherchEtat('expedie', $code);
        $this->_changeEtat($nouvEtat);
    }

    /**
     * Change l'etat de la commande
     * (fonction "manuelle")
     *
     * Il faut se reporter au fichier de configuration pour remplire correctement
     * les champs $etape et $code
     * @param string $etape
     * @param string $code
     */
    public function changeEtat($etape, $code = '')
    {
        $nouvEtat = $this->_cherchEtat($etape, $code);
        $this->_changeEtat($nouvEtat);
    }

    /**
     * Traitement de l'edition d'état de la commande
     *
     * @param int $etat
     * @throws ShopException
     */
    protected function _changeEtat($etat)
    {
        $query = 'UPDATE ' . $this->config('table', 'commande') . ' '
               . 'SET etat = ' . $etat . ' '
               . 'WHERE id = ' . $this->id;
        try {
            $this->db->exec($query);
        } catch (PDOException $exc) {
            unset($exc);
            $message = $this->config('erreur', 'changeEtat');
            throw new ShopException($message);
        }
    }


    /**
     * Passe un panier à l'état d'une commande
     * la commande aura comme etat "attente de payement"
     * seules les informations extraites du paniers sont ajoutées
     *
     * @param string $modeReg
     * @param \Vel\Lib\Panier $panier
     *
     * @return int Id de la commande
     * @throws LibException
     * @throws MarvinException
     */
    public function panierToCommande($modeReg, \Vel\Lib\Panier $panier)
    {
        $name = 'attentPayement' . $modeReg;
        $etat = $this->config('table', $name);

        if (empty($etat)) {
            $message = $this->config('erreur', 'modeRegIncorecte');
            throw new \Slrfw\Exception\Lib($message);
        }

        $query = 'INSERT INTO ' . $this->config('table', 'commande') . ' '
               . 'SET total = ' . $panier->getTotal() . ', '
               . 'total_ht = ' . $panier->getHT() . ', '
               . 'total_ttc = ' . $panier->getPrix() . ', '
               . 'port = ' . $panier->getPort() . ', '
               . 'date = ' . 'NOW()' . ', '
               . 'mode_reg = ' . $this->db->quote($modeReg) . ', '
               . 'etat = ' . $etat;
        $this->exec($query);
        $this->id = $this->db->lastInsertId();

        $reference = $this->genereReference();

        $query = 'UPDATE ' . $this->config('table', 'commande') . ' '
               . 'SET reference = ' . $this->db->quote($reference) . ' '
               . 'WHERE id = ' . $this->id;
        $this->db->exec($query);

        /* = Insertion des lignes du panier dans la commande
          `------------------------------------------------- */
        $query = 'DESC ' . $this->config('table', 'commandeLigne');
        $desc = $this->db->query($query)->fetchAll(\PDO::FETCH_COLUMN);

        $ignore = array('id', 'id_panier', 'id_commande');

        foreach ($desc as $key => $value)
            if (in_array($value, $ignore))
                unset($desc[$key]);

        $query = 'INSERT INTO ' . $this->config('table', 'commandeLigne') . ' '
               . 'SELECT "", ' . $this->id . ', ' . implode(', ', $desc) . ' '
               . 'FROM ' . $panier->config('table', 'panierLigne') . ' '
               . 'WHERE id_panier = ' . $panier->getId();
        try {
            $this->db->exec($query);
        } catch (\PDOException $exc) {
            $etat = $this->config('etat', 'annuleEnregistrement');
            $query = 'UPDATE ' . $this->config('table', 'commande') . ' '
                   . 'SET  etat = ' . $etat . ' '
                   . 'WHERE id = ' . $this->id;
            $this->db->exec($query);
            unset($this->id);
            throw new \Slrfw\Exception\Marvin(
                $exc, "Erreur lors de l'enregistrement d'une commande"
            );
        }


        return $this->id;
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
            $ref = '';
            for ($i = 0; $i < 10; $i++) {
                $ref .= (string) rand(0, 9);
            }

            $query = 'SELECT reference '
                   . 'FROM ' . $this->config('table', 'commande') . ' '
                   . 'WHERE reference = ' . $this->db->quote($ref) . ' ';

            $result = $this->db->query($query)->fetch();

        } while (empty($result));

        return $ref;
	}
}

