<?php
/**
 * Datatable de gestion des taxes
 *
 * @package    Vel
 * @subpackage Datatable
 * @author     Adrien <aimbert@solire.fr>
 * @license    CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */

$config = array(
    'table' => array(
        'title' => 'Liste des taxes',
        'title_item' => 'taxe',
        'suffix_genre' => 'e',
        'fixedheader' => false,
        'name' => 'taxe',
    ),
    'extra' => array(
        'copy' => false,
        'print' => false,
        'pdf' => false,
        'csv' => false,
        'hide_columns' => false,
        'highlightedSearch' => false,
        'creable' => true,
        'editable' => false,
        'deletable' => true,
        'logical delete' => array(
            'column_bool' => 'suppr',
            'column_date' => 'date_modif',
        ),
    ),
    'style' => array(
        'form' => 'bootstrap',
    ),
    'columns' => array(
        array(
            'name' => 'id',
            'index' => true,
            'show' => false,
            'filter_field' => 'text',
            'title' => 'Id',
        ),
        array(
            'name' => 'valeur',
            'show' => true,
            'filter_field' => 'text',
            'title' => 'Montant (%)',
            'creable_field' => array(
                'type' => 'text',
            ),
        ),
        array(
            'name' => 'id_region',
            'show' => false,
            'filter_field' => 'text',
            'title' => 'id_region',
        )
    ),
);

