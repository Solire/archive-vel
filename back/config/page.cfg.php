<?php
/**
 * Configuration de l'affichage des gabarits
 *
 * @package    Back
 * @subpackage Gabarit
 * @author     dev <dev@solire.fr>
 * @license    Solire http://www.solire.fr/
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
        'label' => 'Rubriques',
        'gabarits' => array($confVel->get('gabarit', 'idRubrique')),
        'display' => false,
    ),
);

unset($confVel);