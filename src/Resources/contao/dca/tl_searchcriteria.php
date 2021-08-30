<?php

declare(strict_types=1);

/*
 * This file is part of the Contao EstateManager extension "Lead Matching Tool".
 *
 * @link      https://www.contao-estatemanager.com/
 * @source    https://github.com/contao-estatemanager/lead-matching-tool
 * @copyright Copyright (c) 2021 Oveleon (https://www.oveleon.de)
 * @license   https://www.contao-estatemanager.com/lizenzbedingungen.html
 * @author    Daniele Sciannimanica (https://github.com/doishub)
 */

$GLOBALS['TL_DCA']['tl_searchcriteria'] = [
    // Config
    'config' => [
        'dataContainer' => 'Table',
        'switchToEdit' => true,
        'enableVersioning' => true,
        'markAsCopy' => 'title',
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'published' => 'index',
            ],
        ],
    ],

    // List
    'list' => [
        'sorting' => [
            'mode' => 2,
            'fields' => ['id'],
            'flag' => 1,
            'panelLayout' => 'filter;sort,search,limit',
        ],
        'label' => [
            'fields' => ['id', 'title', 'marketing', 'tstamp'],
            'showColumns' => true,
            'label_callback' => ['ContaoEstateManager\LeadMatchingTool\Contao\Dca\TlSearchCriteria', 'labelCallback'],
        ],
        'global_operations' => [
            'all' => [
                'label' => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"',
            ],
        ],
        'operations' => [
            'edit' => [
                'label' => &$GLOBALS['TL_LANG']['tl_searchcriteria']['edit'],
                'href' => 'act=edit',
                'icon' => 'edit.svg',
            ],
            'copy' => [
                'label' => &$GLOBALS['TL_LANG']['tl_searchcriteria']['copy'],
                'href' => 'act=copy',
                'icon' => 'copy.svg',
            ],
            'delete' => [
                'label' => &$GLOBALS['TL_LANG']['tl_searchcriteria']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.svg',
                'attributes' => 'onclick="if(!confirm(\''.$GLOBALS['TL_LANG']['MSC']['deleteConfirm'].'\'))return false;Backend.getScrollOffset()"',
            ],
            'toggle' => [
                'label' => &$GLOBALS['TL_LANG']['tl_searchcriteria']['toggle'],
                'attributes' => 'onclick="Backend.getScrollOffset();"',
                'haste_ajax_operation' => [
                    'field' => 'published',
                    'options' => [
                        [
                            'value' => '',
                            'icon' => 'invisible.gif',
                        ],
                        [
                            'value' => '1',
                            'icon' => 'visible.gif',
                        ],
                    ],
                ],
            ],
            'show' => [
                'label' => &$GLOBALS['TL_LANG']['tl_searchcriteria']['show'],
                'href' => 'act=show',
                'icon' => 'show.svg',
            ],
        ],
    ],

    // Palettes
    'palettes' => [
        'default' => '{title_legend},title,marketing;{config_legend},objectType,regions,room_from,room_to,area_from,area_to,price_from,price_to;{geo_legend},latitude,longitude,postalcode,city,range;{published_legend},published',
    ],

    // Fields
    'fields' => [
        'id' => [
            'label' => ['ID'],
            'sorting' => true,
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'sorting' => true,
        ],
        'title' => [
            'label' => &$GLOBALS['TL_LANG']['tl_searchcriteria']['title'],
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'marketing' => [
            'label' => &$GLOBALS['TL_LANG']['tl_searchcriteria']['marketing'],
            'inputType' => 'select',
            'sorting' => true,
            'options' => ['kauf', 'miete'],
            'eval' => ['includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(16) NOT NULL default ''",
        ],
        'objectType' => [
            'label' => &$GLOBALS['TL_LANG']['tl_searchcriteria']['objectType'],
            'inputType' => 'select',
            'foreignKey' => 'tl_object_type.title',
            'eval' => ['tl_class' => 'w50 clr wizard'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
        ],
        'regions' => [
            'label' => &$GLOBALS['TL_LANG']['tl_searchcriteria']['regions'],
            'inputType' => 'regionTree',
            'eval' => ['multiple' => true, 'fieldType' => 'checkbox', 'tl_class' => 'w50 clr'],
            'sql' => 'blob NULL',
            'save_callback' => [
                ['ContaoEstateManager\\RegionEntity\\Region', 'regionConnectionSaveCallback'],
            ],
        ],
        'room_from' => [
            'label' => &$GLOBALS['TL_LANG']['tl_searchcriteria']['room_from'],
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50 clr'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'room_to' => [
            'label' => &$GLOBALS['TL_LANG']['tl_searchcriteria']['room_to'],
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'area_from' => [
            'label' => &$GLOBALS['TL_LANG']['tl_searchcriteria']['area_from'],
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50 clr'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'area_to' => [
            'label' => &$GLOBALS['TL_LANG']['tl_searchcriteria']['area_to'],
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'price_from' => [
            'label' => &$GLOBALS['TL_LANG']['tl_searchcriteria']['price_from'],
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50 clr'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'price_to' => [
            'label' => &$GLOBALS['TL_LANG']['tl_searchcriteria']['price_to'],
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'latitude' => [
            'label' => &$GLOBALS['TL_LANG']['tl_searchcriteria']['latitude'],
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'longitude' => [
            'label' => &$GLOBALS['TL_LANG']['tl_searchcriteria']['longitude'],
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'postalcode' => [
            'label' => &$GLOBALS['TL_LANG']['tl_searchcriteria']['postalcode'],
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'city' => [
            'label' => &$GLOBALS['TL_LANG']['tl_searchcriteria']['city'],
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'range' => [
            'label' => &$GLOBALS['TL_LANG']['tl_searchcriteria']['range'],
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'published' => [
            'label' => &$GLOBALS['TL_LANG']['tl_searchcriteria']['published'],
            'exclude' => true,
            'filter' => true,
            'flag' => 1,
            'inputType' => 'checkbox',
            'eval' => ['doNotCopy' => true],
            'sql' => "char(1) NOT NULL default ''",
        ],
    ],
];
