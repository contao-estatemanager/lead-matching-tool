<?php
$GLOBALS['TL_DCA']['tl_searchcriteria'] = array
(

    // Config
    'config' => array
    (
        'dataContainer'               => 'Table',
        'switchToEdit'                => true,
        'enableVersioning'            => true,
        'markAsCopy'                  => 'title',
        'sql' => array
        (
            'keys' => array
            (
                'id' => 'primary',
                'published' => 'index'
            )
        )
    ),

    // List
    'list' => array
    (
        'sorting' => array
        (
            'mode'                    => 2,
            'fields'                  => array('id'),
            'flag'                    => 1,
            'panelLayout'             => 'filter;sort,search,limit'
        ),
        'label' => array
        (
            'fields'                  => array('id', 'title', 'marketing', 'tstamp'),
            'showColumns'             => true,
            'label_callback'          => array('ContaoEstateManager\LeadMatchingTool\Contao\Dca\TlSearchCriteria', 'labelCallback')
        ),
        'global_operations' => array
        (
            'all' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'                => 'act=select',
                'class'               => 'header_edit_all',
                'attributes'          => 'onclick="Backend.getScrollOffset()" accesskey="e"'
            )
        ),
        'operations' => array
        (
            'edit' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_searchcriteria']['edit'],
                'href'                => 'act=edit',
                'icon'                => 'edit.svg'
            ),
            'copy' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_searchcriteria']['copy'],
                'href'                => 'act=copy',
                'icon'                => 'copy.svg',
            ),
            'delete' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_searchcriteria']['delete'],
                'href'                => 'act=delete',
                'icon'                => 'delete.svg',
                'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"',
            ),
            'toggle' => array
            (
                'label'                => &$GLOBALS['TL_LANG']['tl_searchcriteria']['toggle'],
                'attributes'           => 'onclick="Backend.getScrollOffset();"',
                'haste_ajax_operation' => [
                    'field'     => 'published',
                    'options'    => [
                        [
                            'value'    => '',
                            'icon'     => 'invisible.gif'
                        ],
                        [
                            'value'    => '1',
                            'icon'     => 'visible.gif'
                        ]
                    ]
                ]
            ),
            'show' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_searchcriteria']['show'],
                'href'                => 'act=show',
                'icon'                => 'show.svg'
            )
        )
    ),

    // Palettes
    'palettes' => array
    (
        'default'                     => '{title_legend},title,marketing;{config_legend},objectType,regions,room_from,room_to,area_from,area_to,price_from,price_to;{geo_legend},latitude,longitude,postalcode,city,range;{published_legend},published'
    ),

    // Fields
    'fields' => array
    (
        'id' => array
        (
            'label'                   => array('ID'),
            'sorting'                 => true,
            'sql'                     => "int(10) unsigned NOT NULL auto_increment"
        ),
        'tstamp' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL default '0'",
            'sorting'                 => true
        ),
        'title' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_searchcriteria']['title'],
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'marketing' => array
        (
            'label'                     => &$GLOBALS['TL_LANG']['tl_searchcriteria']['marketing'],
            'inputType'                 => 'select',
            'sorting'                   => true,
            'options'                   => array('kauf', 'miete'),
            'eval'                      => array('includeBlankOption'=>true, 'tl_class'=>'w50'),
            'sql'                       => "varchar(16) NOT NULL default ''"
        ),
        'objectType' => array
        (
            'label'                     => &$GLOBALS['TL_LANG']['tl_searchcriteria']['objectType'],
            'inputType'                 => 'select',
            'foreignKey'                => 'tl_object_type.title',
            'eval'                      => array('tl_class'=>'w50 clr wizard'),
            'sql'                       => "int(10) unsigned NOT NULL default '0'",
            'relation'                  => array('type'=>'hasOne', 'load'=>'lazy')
        ),
        'regions' => array
        (
            'label'                     => &$GLOBALS['TL_LANG']['tl_searchcriteria']['regions'],
            'inputType'                 => 'regionTree',
            'eval'                      => array('multiple'=>true, 'fieldType'=>'checkbox', 'tl_class'=>'w50 clr'),
            'sql'                       => "blob NULL",
            'save_callback'             => array(
                array("ContaoEstateManager\RegionEntity\Region", "regionConnectionSaveCallback")
            )
        ),
        'room_from' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_searchcriteria']['room_from'],
            'inputType'               => 'text',
            'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50 clr'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'room_to' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_searchcriteria']['room_to'],
            'inputType'               => 'text',
            'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'area_from' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_searchcriteria']['area_from'],
            'inputType'               => 'text',
            'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50 clr'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'area_to' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_searchcriteria']['area_to'],
            'inputType'               => 'text',
            'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'price_from' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_searchcriteria']['price_from'],
            'inputType'               => 'text',
            'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50 clr'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'price_to' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_searchcriteria']['price_to'],
            'inputType'               => 'text',
            'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'latitude' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_searchcriteria']['latitude'],
            'inputType'               => 'text',
            'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'longitude' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_searchcriteria']['longitude'],
            'inputType'               => 'text',
            'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'postalcode' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_searchcriteria']['postalcode'],
            'inputType'               => 'text',
            'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'city' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_searchcriteria']['city'],
            'inputType'               => 'text',
            'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'range' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_searchcriteria']['range'],
            'inputType'               => 'text',
            'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'published' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_searchcriteria']['published'],
            'exclude'                 => true,
            'filter'                  => true,
            'flag'                    => 1,
            'inputType'               => 'checkbox',
            'eval'                    => array('doNotCopy'=>true),
            'sql'                     => "char(1) NOT NULL default ''"
        ),
    )
);
