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
use ContaoEstateManager\LeadMatchingTool\Controller\FrontendModule\LeadMatchingController;

System::loadLanguageFile('tl_lead_matching_meta');

$GLOBALS['TL_DCA']['tl_lead_matching'] = [
    // Config
    'config' => [
        'dataContainer' => 'Table',
        'switchToEdit' => true,
        'enableVersioning' => true,
        'markAsCopy' => 'title',
        'sql' => [
            'keys' => [
                'id' => 'primary',
            ],
        ],
    ],

    // List
    'list' => [
        'sorting' => [
            'mode' => 2,
            'fields' => ['title'],
            'flag' => 1,
            'panelLayout' => 'filter;sort,search,limit',
        ],
        'label' => [
            'fields' => ['title', 'marketingType'],
            'showColumns' => true,
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
            'show' => [
                'href' => 'act=show',
                'icon' => 'show.svg',
            ],
        ],
    ],

    // Palettes
    'palettes' => [
        '__selector__' => ['type', 'preciseRegionSearch', 'addEstateForm', 'addContactForm'],
        'default' => '{title_legend},title,type;',
        'system' => '{title_legend},title,type;{config_legend},marketingType;{data_legend},marketingTypes,objectTypes,preciseRegionSearch;{searchcriteria_legend},listMetaFields,txtListHeadline,txtListDescription,numberOfItems,perPage,listItemTemplate,countResults;{estate_form_legend},addEstateForm;{contact_form_legend},addContactForm;',
    ],

    // Subpalettes
    'subpalettes' => [
        'preciseRegionSearch' => 'regions',
        'addEstateForm' => 'txtEstateHeadline,forceList,txtEstateDescription,estateFormMetaFields',
        'addContactForm' => 'txtContactHeadline,forceContact,txtContactDescription,contactForm',
    ],

    // Fields
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'title' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'type' => [
            'exclude' => true,
            'inputType' => 'select',
            'sorting' => true,
            'options' => [LeadMatchingController::TYPE],
            'eval' => ['includeBlankOption' => true, 'submitOnChange' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(16) NOT NULL default ''",
        ],
        'marketingType' => [
            'exclude' => true,
            'inputType' => 'select',
            'sorting' => true,
            'options' => ['kauf', 'miete'],
            'reference' => &$GLOBALS['TL_LANG']['tl_lead_matching_meta'],
            'eval' => ['includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(16) NOT NULL default ''",
        ],
        'marketingTypes' => [
            'exclude' => true,
            'inputType' => 'checkboxWizard',
            'options' => ['kauf', 'miete'],
            'reference' => &$GLOBALS['TL_LANG']['tl_lead_matching_meta'],
            'save_callback' => [
                ['ContaoEstateManager\LeadMatchingTool\Contao\Dca\TlLeadMatching', 'saveMarketingTypes'],
            ],
            'eval' => ['multiple' => true, 'tl_class' => 'w50'],
            'sql' => 'blob NULL',
        ],
        'marketingTypesData' => [
            'sql' => 'blob NULL',
        ],
        'objectTypes' => [
            'exclude' => true,
            'inputType' => 'checkboxWizard',
            'foreignKey' => 'tl_object_type.title',
            'save_callback' => [
                ['ContaoEstateManager\LeadMatchingTool\Contao\Dca\TlLeadMatching', 'saveObjectTypes'],
            ],
            'eval' => ['multiple' => true, 'tl_class' => 'w50 clr wizard'],
            'sql' => "varchar(1022) NOT NULL default ''",
            'relation' => ['type' => 'hasMany', 'load' => 'lazy'],
        ],
        'objectTypesData' => [
            'sql' => 'blob NULL',
        ],
        'preciseRegionSearch' => [
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['submitOnChange' => true, 'tl_class' => 'w50 clr'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'regions' => [
            'exclude' => true,
            'inputType' => 'regionTree',
            'eval' => ['multiple' => true, 'fieldType' => 'checkbox', 'tl_class' => 'w50 clr'],
            'save_callback' => [
                ['ContaoEstateManager\LeadMatchingTool\Contao\Dca\TlLeadMatching', 'saveRegions'],
            ],
            'sql' => 'blob NULL',
        ],
        'regionsData' => [
            'sql' => 'blob NULL',
        ],
        'numberOfItems' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'rgxp' => 'natural', 'tl_class' => 'w50 clr'],
            'sql' => 'smallint(5) unsigned NOT NULL default 3',
        ],
        'perPage' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['rgxp' => 'natural', 'tl_class' => 'w50'],
            'sql' => 'smallint(5) unsigned NOT NULL default 0',
        ],
        'listMetaFields' => [
            'exclude' => true,
            'inputType' => 'checkboxWizard',
            'options_callback' => ['ContaoEstateManager\LeadMatchingTool\Contao\Dca\TlLeadMatching', 'getListMetaFields'],
            'reference' => &$GLOBALS['TL_LANG']['tl_lead_matching_meta'],
            'eval' => ['multiple' => true, 'tl_class' => 'w50 clr wizard'],
            'sql' => 'text NULL',
        ],
        'groupRelatedFields' => [
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'm12 w50'],
            'sql' => "char(1) NOT NULL default '1'",
        ],
        'estateFormMetaFields' => [
            'exclude' => true,
            'inputType' => 'checkboxWizard',
            'options' => ['marketingType', 'objectTypes', 'regions', 'range', 'room', 'area', 'price'],
            'reference' => &$GLOBALS['TL_LANG']['tl_lead_matching_meta'],
            'eval' => ['multiple' => true, 'mandatory' => true, 'tl_class' => 'w50 wizard clr'],
            'sql' => 'text NULL',
            'leadMatching' => [
                'marketingType' => [
                    'fieldOptions' => [
                        'inputType' => 'select',
                        'eval' => [
                            'mandatory' => true,
                            'includeBlankOption' => true,
                        ],
                    ],
                ],
                'objectTypes' => [
                    'fieldOptions' => [
                        'inputType' => 'select',
                        'eval' => [
                            'mandatory' => true,
                            'includeBlankOption' => true,
                        ],
                    ],
                    'filter' => [
                        'fieldName' => 'objectType',
                    ],
                ],
                'regions' => [
                    'fieldOptions' => [
                        'eval' => [
                            'mandatory' => true,
                            'includeBlankOption' => true,
                        ],
                    ],
                    'additionalFields' => [
                        'region_lat' => [
                            'inputType' => 'hidden',
                        ],
                        'region_lng' => [
                            'inputType' => 'hidden',
                        ],
                    ],
                ],
                'range' => [
                    'filter' => [
                        'skip' => true,
                    ],
                ],
            ],
        ],
        'contactForm' => [
            'exclude' => true,
            'inputType' => 'select',
            'options_callback' => ['ContaoEstateManager\LeadMatchingTool\Contao\Dca\TlLeadMatching', 'getForms'],
            'eval' => ['mandatory' => true, 'chosen' => true, 'submitOnChange' => true, 'tl_class' => 'w50 wizard'],
            'wizard' => [
                ['ContaoEstateManager\LeadMatchingTool\Contao\Dca\TlLeadMatching', 'editForm'],
            ],
            'sql' => 'int(10) unsigned NOT NULL default 0',
        ],
        'countResults' => [
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50 m12'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'addContactForm' => [
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['submitOnChange' => true, 'tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'addEstateForm' => [
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['submitOnChange' => true, 'tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'forceList' => [
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50 m12'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'forceContact' => [
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50 m12'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'listItemTemplate' => [
            'exclude' => true,
            'inputType' => 'select',
            'options_callback' => function () {
                return Contao\Controller::getTemplateGroup('search_criteria_item_');
            },
            'eval' => ['chosen' => true, 'mandatory' => true, 'tl_class' => 'w50 clr'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'txtEstateHeadline' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'txtEstateDescription' => [
            'exclude' => true,
            'inputType' => 'textarea',
            'eval' => ['rte' => 'tinyMCE', 'tl_class' => 'clr'],
            'sql' => 'mediumtext NULL',
        ],
        'txtListHeadline' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50 clr'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'txtListDescription' => [
            'exclude' => true,
            'inputType' => 'textarea',
            'eval' => ['rte' => 'tinyMCE', 'tl_class' => 'clr'],
            'sql' => 'mediumtext NULL',
        ],
        'txtContactHeadline' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'txtContactDescription' => [
            'exclude' => true,
            'inputType' => 'textarea',
            'eval' => ['rte' => 'tinyMCE', 'tl_class' => 'clr'],
            'sql' => 'mediumtext NULL',
        ],
    ],
];
