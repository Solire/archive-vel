<?php
/**
 * Configuration du datatable de filtre libre
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
        'title' => 'Liste des critères',
        'title_item' => 'critère',
        'suffix_genre' => '',
        'fixedheader' => false,
        'name' => 'critere',
        'detail' => false,
    ),
    'where' => array('libre = 1'),
    'file' => array(
        'upload_path' => 'public_html/medias/cli/Filtre',
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
            'content' => '<div rel="dashboard/start.html?'
                . 'name=filtre_option&nomain=1&nojs=1" data-filter='
                . '"filter[]=id_filtre|[INDEX]" class="ajax-load"></div>',
            'show_detail' => true,
            'title' => 'Options du filtre',
        ),
        array(
            'name' => 'libre',
            'show' => false,
            'creable_field' => array(
                'value' => 1,
            ),
        ),
    ),
);

