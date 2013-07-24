<?php
/**
 * Configuration de l'affichage des gabarits
 *
 * @package    Back
 * @subpackage Gabarit
 * @author     Adrien <aimbert@solire.fr>
 * @license    CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */

/** Récupération de la configuration de la base **/
$path = \Slrfw\FrontController::search('config/sqlVel.ini', false);
$confVel = new \Slrfw\Config($path);
unset($path);

$config = array(
    101 => array(
        'label' => 'Produits',
        'gabarits' => array($confVel->get('gabarit', 'idProduit')),
        'display' => false,
    ),
    102 => array(
        'label' => 'Liste des Rubriques',
        'gabarits' => array($confVel->get('gabarit', 'idRubrique')),
        'display' => false,
        'noType' => true,
        'urlRedir' => 'back/produits/start.html?filter[]=gab_page.id_parent|',
        'urlAjax' => 'back/page/children.html?c=102',
        'childName' => '',
    ),
);

unset($confVel);