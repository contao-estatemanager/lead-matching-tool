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
                'label' => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"',
            ],
        ],
        'operations' => [
            'edit' => [
                'label' => &$GLOBALS['TL_LANG']['tl_lead_matching']['edit'],
                'href' => 'act=edit',
                'icon' => 'edit.svg',
            ],
            'copy' => [
                'label' => &$GLOBALS['TL_LANG']['tl_lead_matching']['copy'],
                'href' => 'act=copy',
                'icon' => 'copy.svg',
            ],
            'delete' => [
                'label' => &$GLOBALS['TL_LANG']['tl_lead_matching']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.svg',
                'attributes' => 'onclick="if(!confirm(\''.$GLOBALS['TL_LANG']['MSC']['deleteConfirm'].'\'))return false;Backend.getScrollOffset()"',
            ],
            'show' => [
                'label' => &$GLOBALS['TL_LANG']['tl_lead_matching']['show'],
                'href' => 'act=show',
                'icon' => 'show.svg',
            ],
        ],
    ],

    // Palettes
    'palettes' => [
        '__selector__' => ['type', 'preciseRegionSearch', 'addEstateForm', 'addContactForm'],
        'default' => '{title_legend},title,type;',
        'system' => '{title_legend},title,type;{config_legend},marketingType;{data_legend},marketingTypes,addBlankMarketingType,objectTypes,addBlankObjectType,preciseRegionSearch;{searchcriteria_legend},listMetaFields,txtListHeadline,txtListDescription,numberOfItems,perPage,listItemTemplate,countResults;{estate_form_legend},addEstateForm;{contact_form_legend},addContactForm;',
    ],

    // Subpalettes
    'subpalettes' => [
        'preciseRegionSearch' => 'regions,addBlankRegion',
        'addEstateForm' => 'txtEstateHeadline,forceList,txtEstateDescription,estateForm',
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
            'label' => &$GLOBALS['TL_LANG']['tl_lead_matching']['title'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'type' => [
            'label' => &$GLOBALS['TL_LANG']['tl_lead_matching']['type'],
            'exclude' => true,
            'inputType' => 'select',
            'sorting' => true,
            'options' => [LeadMatchingController::TYPE],
            'eval' => ['includeBlankOption' => true, 'submitOnChange' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(16) NOT NULL default ''",
        ],
        'marketingType' => [
            'label' => &$GLOBALS['TL_LANG']['tl_lead_matching']['marketingType'],
            'exclude' => true,
            'inputType' => 'select',
            'sorting' => true,
            'options' => ['kauf', 'miete'],
            'reference' => &$GLOBALS['TL_LANG']['tl_lead_matching_meta'],
            'eval' => ['includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(16) NOT NULL default ''",
        ],
        'marketingTypes' => [
            'label' => &$GLOBALS['TL_LANG']['tl_lead_matching']['marketingTypes'],
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
        'addBlankMarketingType' => [
            'label' => &$GLOBALS['TL_LANG']['tl_lead_matching']['addBlankMarketingType'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50 clr'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'objectTypes' => [
            'label' => &$GLOBALS['TL_LANG']['tl_lead_matching']['objectTypes'],
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
        'addBlankObjectType' => [
            'label' => &$GLOBALS['TL_LANG']['tl_lead_matching']['addBlankObjectType'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50 clr'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'preciseRegionSearch' => [
            'label' => &$GLOBALS['TL_LANG']['tl_lead_matching']['preciseRegionSearch'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['submitOnChange' => true, 'tl_class' => 'w50 clr'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'regions' => [
            'label' => &$GLOBALS['TL_LANG']['tl_lead_matching']['regions'],
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
        'addBlankRegion' => [
            'label' => &$GLOBALS['TL_LANG']['tl_lead_matching']['addBlankRegion'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50 clr'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'numberOfItems' => [
            'label' => &$GLOBALS['TL_LANG']['tl_lead_matching']['numberOfItems'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'rgxp' => 'natural', 'tl_class' => 'w50 clr'],
            'sql' => 'smallint(5) unsigned NOT NULL default 3',
        ],
        'perPage' => [
            'label' => &$GLOBALS['TL_LANG']['tl_lead_matching']['perPage'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['rgxp' => 'natural', 'tl_class' => 'w50'],
            'sql' => 'smallint(5) unsigned NOT NULL default 0',
        ],
        'listMetaFields' => [
            'label' => &$GLOBALS['TL_LANG']['tl_lead_matching']['listMetaFields'],
            'exclude' => true,
            'inputType' => 'checkboxWizard',
            'options_callback' => ['ContaoEstateManager\LeadMatchingTool\Contao\Dca\TlLeadMatching', 'getListMetaFields'],
            'reference' => &$GLOBALS['TL_LANG']['tl_lead_matching_meta'],
            'eval' => ['multiple' => true, 'tl_class' => 'w50 clr wizard'],
            'sql' => 'text NULL',
        ],
        // ToDo: Combine related fields in module
        'groupRelatedFields' => [
            'label' => &$GLOBALS['TL_LANG']['tl_lead_matching']['groupRelatedFields'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'm12 w50'],
            'sql' => "char(1) NOT NULL default '1'",
        ],
        'estateForm' => [
            'label' => &$GLOBALS['TL_LANG']['tl_lead_matching']['estateForm'],
            'exclude' => true,
            'inputType' => 'select',
            'options_callback' => ['ContaoEstateManager\LeadMatchingTool\Contao\Dca\TlLeadMatching', 'getForms'],
            'eval' => ['mandatory' => true, 'chosen' => true, 'submitOnChange' => true, 'tl_class' => 'w50 wizard'],
            'wizard' => [
                ['ContaoEstateManager\LeadMatchingTool\Contao\Dca\TlLeadMatching', 'editForm'],
            ],
            'sql' => 'int(10) unsigned NOT NULL default 0',
        ],
        'contactForm' => [
            'label' => &$GLOBALS['TL_LANG']['tl_lead_matching']['estateForm'],
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
            'label' => &$GLOBALS['TL_LANG']['tl_lead_matching']['countResults'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50 m12'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'addContactForm' => [
            'label' => &$GLOBALS['TL_LANG']['tl_lead_matching']['addContactForm'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['submitOnChange' => true, 'tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'addEstateForm' => [
            'label' => &$GLOBALS['TL_LANG']['tl_lead_matching']['addEstateForm'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['submitOnChange' => true, 'tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'forceList' => [
            'label' => &$GLOBALS['TL_LANG']['tl_lead_matching']['forceList'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50 m12'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'forceContact' => [
            'label' => &$GLOBALS['TL_LANG']['tl_lead_matching']['forceContact'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50 m12'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'listItemTemplate' => [
            'label' => &$GLOBALS['TL_LANG']['tl_module']['listItemTemplate'],
            'exclude' => true,
            'inputType' => 'select',
            'options_callback' => function () {
                return Contao\Controller::getTemplateGroup('search_criteria_item_');
            },
            'eval' => ['chosen' => true, 'mandatory' => true, 'tl_class' => 'w50 clr'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'txtEstateHeadline' => [
            'label' => &$GLOBALS['TL_LANG']['tl_lead_matching']['txtEstateHeadline'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'txtEstateDescription' => [
            'label' => &$GLOBALS['TL_LANG']['tl_lead_matching']['txtEstateDescription'],
            'exclude' => true,
            'inputType' => 'textarea',
            'eval' => ['rte' => 'tinyMCE', 'tl_class' => 'clr'],
            'sql' => 'mediumtext NULL',
        ],
        'txtListHeadline' => [
            'label' => &$GLOBALS['TL_LANG']['tl_lead_matching']['txtListHeadline'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50 clr'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'txtListDescription' => [
            'label' => &$GLOBALS['TL_LANG']['tl_lead_matching']['txtListDescription'],
            'exclude' => true,
            'inputType' => 'textarea',
            'eval' => ['rte' => 'tinyMCE', 'tl_class' => 'clr'],
            'sql' => 'mediumtext NULL',
        ],
        'txtContactHeadline' => [
            'label' => &$GLOBALS['TL_LANG']['tl_lead_matching']['txtContactHeadline'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'txtContactDescription' => [
            'label' => &$GLOBALS['TL_LANG']['tl_lead_matching']['txtContactDescription'],
            'exclude' => true,
            'inputType' => 'textarea',
            'eval' => ['rte' => 'tinyMCE', 'tl_class' => 'clr'],
            'sql' => 'mediumtext NULL',
        ],
    ],
];
