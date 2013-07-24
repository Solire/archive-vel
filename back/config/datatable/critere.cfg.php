<?php
/**
 * Configuration du datatable de filtre
 *
 * @package    Vel
 * @subpackage Datatable
 * @author     Adrien <aimbert@solire.fr>
 * @license    Solire http://www.solire.fr/
 */

$config = array(
    'plugins' => array(
        'ShinForm',
    ),
    'table' => array(
        'title' => 'Liste des critères',
        'title_item' => 'critère',
        'suffix_genre' => '',
        'fixedheader' => false,
        'name' => 'critere',
        'detail' => true,
    ),
    'where' => array('libre = 0'),
    'file' => array(
        'upload_path' => '',
        'upload_temp' => 'temp',
        'upload_vignette' => 'mini',
        'upload_apercu' => 'apercu',
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
            'name' => 'name',
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
                . 'name=critere_option&nomain=1&nojs=1" data-filter='
                . '"filter[]=id_critere|[INDEX]" class="ajax-load"></div>',
            'show_detail' => true,
            'title' => 'Options du filtre',
        ),
        array(
            'name' => 'libre',
            'show' => false,
            'creable_field' => array(
                'value' => 0,
            ),
        ),
    ),
);

