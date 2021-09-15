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

use Contao\System;

System::loadLanguageFile('tl_lead_matching_meta');

$GLOBALS['TL_DCA']['tl_search_criteria'] = [
    // Config
    'config' => [
        'dataContainer' => 'Table',
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
            'fields' => ['id', 'title', 'marketingType', 'objectType', 'tstamp'],
            'showColumns' => true,
            'label_callback' => ['ContaoEstateManager\LeadMatchingTool\Contao\Dca\TlSearchCriteria', 'labelCallback'],
        ],
        'global_operations' => [
            'all' => [
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"',
            ],
        ],
        'operations' => [
            'edit' => [
                'href' => 'act=edit',
                'icon' => 'edit.svg',
            ],
            'copy' => [
                'href' => 'act=copy',
                'icon' => 'copy.svg',
            ],
            'delete' => [
                'href' => 'act=delete',
                'icon' => 'delete.svg',
                'attributes' => 'onclick="if(!confirm(\''.$GLOBALS['TL_LANG']['MSC']['deleteConfirm'].'\'))return false;Backend.getScrollOffset()"',
            ],
            'toggle' => [
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
                'href' => 'act=show',
                'icon' => 'show.svg',
            ],
        ],
    ],

    // Palettes
    'palettes' => [
        'default' => '{title_legend},title,marketingType;{config_legend},objectType,room_from,room_to,area_from,area_to,price_from,price_to;{region_legend},regions;{geo_legend},latitude,longitude,postalcode,city,range;{published_legend},published',
    ],

    // Fields
    'fields' => [
        'id' => [
            'sorting' => true,
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'sorting' => true,
        ],
        'title' => [
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'marketingType' => [
            'inputType' => 'select',
            'sorting' => true,
            'options' => ['kauf', 'miete'],
            'reference' => &$GLOBALS['TL_LANG']['tl_lead_matching_meta'],
            'eval' => ['includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(16) NOT NULL default ''",
            'leadMatching' => [
                'format' => ['translate'],
            ],
        ],
        'objectType' => [
            'inputType' => 'select',
            'foreignKey' => 'tl_object_type.title',
            'eval' => ['tl_class' => 'w50 clr wizard'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
            'leadMatching' => [
                'format' => ['objectTypes'],
            ],
        ],
        'regions' => [
            'inputType' => 'picker',
            'eval' => ['multiple' => true, 'fieldType' => 'checkbox', 'tl_class' => 'w50 clr'],
            'relation' => ['type' => 'hasOne', 'load' => 'lazy', 'table' => 'tl_region'],
            'sql' => 'blob NULL',
            'save_callback' => [
                ['ContaoEstateManager\RegionEntity\Region', 'regionConnectionSaveCallback'],
            ],
            'leadMatching' => [
                'format' => ['regions'],
            ],
        ],
        'room_from' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50 clr'],
            'sql' => "varchar(255) NOT NULL default ''",
            'leadMatching' => [
                'group' => [
                    'name' => 'room',
                ],
            ],
        ],
        'room_to' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
            'leadMatching' => [
                'group' => [
                    'name' => 'room',
                ],
            ],
        ],
        'area_from' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50 clr'],
            'sql' => "varchar(255) NOT NULL default ''",
            'leadMatching' => [
                'group' => [
                    'name' => 'area',
                    'append' => ' m²',
                ],
            ],
        ],
        'area_to' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
            'leadMatching' => [
                'group' => [
                    'name' => 'area',
                    'append' => ' m²',
                ],
            ],
        ],
        'price_from' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50 clr'],
            'sql' => "varchar(255) NOT NULL default ''",
            'leadMatching' => [
                'group' => [
                    'name' => 'price',
                    'append' => ' '.$GLOBALS['TL_LANG']['tl_lead_matching_meta']['currency'],
                ],
                'format' => function ($varValue) {
                    return number_format((float) $varValue, 0, ',', '.');
                },
            ],
        ],
        'price_to' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
            'leadMatching' => [
                'group' => [
                    'name' => 'price',
                    'append' => ' '.$GLOBALS['TL_LANG']['tl_lead_matching_meta']['currency'],
                ],
                'format' => function ($varValue) {
                    return number_format((float) $varValue, 0, ',', '.');
                },
            ],
        ],
        'latitude' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'longitude' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'postalcode' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
            'leadMatching' => [
                'group' => [
                    'name' => 'location',
                    'separator' => ' ',
                ],
            ],
        ],
        'city' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
            'leadMatching' => [
                'group' => [
                    'name' => 'location',
                    'separator' => ' ',
                ],
            ],
        ],
        'range' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'published' => [
            'exclude' => true,
            'filter' => true,
            'flag' => 1,
            'inputType' => 'checkbox',
            'eval' => ['doNotCopy' => true],
            'sql' => "char(1) NOT NULL default ''",
        ],
    ],
];
