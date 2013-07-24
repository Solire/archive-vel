<?php
/**
 * Datatable de gestion des régions
 *
 * @package    Vel
 * @subpackage Datatable
 * @author     Adrien <aimbert@solire.fr>
 * @license    CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */

$config = array(
    'plugins' => array(
        'ShinForm',
    ),
    'table' => array(
        'title' => 'Géstion des taxes',
        'title_item' => 'région',
        'suffix_genre' => '',
        'fixedheader' => false,
        'name' => 'region',
        'detail' => true,
    ),
    'extra' => array(
        'copy' => false,
        'print' => false,
        'pdf' => false,
        'csv' => false,
        'hide_columns' => false,
        'highlightedSearch' => false,
        'creable' => true,
        'editable' => true,
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
            'show' => false,
            'index' => true,
            'filter_field' => 'text',
            'title' => 'Id',
        ),
        array(
            'name' => 'nom',
            'show' => true,
            'filter_field' => 'text',
            'title' => 'Nom',
            'creable_field' => array(
                'type' => 'text',
                'validate' => array(
                    'rules' => array(
                        'required' => true,
                    ),
                    'messages' => array(
                        'required' => 'Ce champ est obligatoire.',
                    ),
                ),
            ),
        ),
        array(
            'content' => '<div rel="back/dashboard/start.html?'
                . 'name=taxe&nomain=1&nojs=1" data-filter='
                . '"filter[]=id_region|[INDEX]" class="ajax-load"></div>',
            'show_detail' => true,
            'title' => 'Options du filtre',
        ),
    ),
);

