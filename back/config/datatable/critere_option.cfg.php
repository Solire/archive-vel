<?php
/**
 * Configuration du datatable de filtre
 *
 * @package    Vel
 * @subpackage Datatable
 * @author     Adrien <aimbert@solire.fr>
 * @license    CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */

$config = array(
    'table' => array(
        'title' => 'Liste des options de critères',
        'title_item' => 'option',
        'suffix_genre' => 'e',
        'fixedheader' => false,
        'name' => 'critere_option',
    ),
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
            'index' => true,
            'show' => false,
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
            ),
        ),
        array(
            'name' => 'id_critere',
            'show' => false,
            'filter_field' => 'text',
            'title' => 'Id_critere',
        )
    ),
);

