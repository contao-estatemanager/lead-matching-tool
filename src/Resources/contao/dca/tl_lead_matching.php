<?php
use Contao\System;
use ContaoEstateManager\LeadMatchingTool\Controller\FrontendModule\LeadMatchingController;

System::loadLanguageFile('tl_lead_matching_meta');

$GLOBALS['TL_DCA']['tl_lead_matching'] = array
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
                'id' => 'primary'
            )
        )
    ),

    // List
    'list' => array
    (
        'sorting' => array
        (
            'mode'                    => 2,
            'fields'                  => array('title'),
            'flag'                    => 1,
            'panelLayout'             => 'filter;sort,search,limit'
        ),
        'label' => array
        (
            'fields'                  => array('title', 'marketingType'),
            'showColumns'             => true
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
                'label'               => &$GLOBALS['TL_LANG']['tl_lead_matching']['edit'],
                'href'                => 'act=edit',
                'icon'                => 'edit.svg'
            ),
            'copy' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_lead_matching']['copy'],
                'href'                => 'act=copy',
                'icon'                => 'copy.svg',
            ),
            'delete' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_lead_matching']['delete'],
                'href'                => 'act=delete',
                'icon'                => 'delete.svg',
                'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"',
            ),
            'show' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_lead_matching']['show'],
                'href'                => 'act=show',
                'icon'                => 'show.svg'
            )
        )
    ),

    // Palettes
    'palettes' => array
    (
        '__selector__'                => array('type', 'preciseRegionSearch', 'addEstateForm', 'addContactForm'),
        'default'                     => '{title_legend},title,type;',
        'system'                      => '{title_legend},title,type;{config_legend},marketingType;{data_legend},marketingTypes,addBlankMarketingType,objectTypes,addBlankObjectType,preciseRegionSearch;{searchcriteria_legend},listMetaFields,txtListHeadline,txtListDescription,numberOfItems,perPage,listItemTemplate,countResults;{estate_form_legend},addEstateForm;{contact_form_legend},addContactForm;'
    ),

    // Subpalettes
    'subpalettes' => array
    (
        'preciseRegionSearch'         => 'regions,addBlankRegion',
        'addEstateForm'               => 'txtEstateHeadline,forceList,txtEstateDescription,estateForm',
        'addContactForm'              => 'txtContactHeadline,forceContact,txtContactDescription,contactForm'
    ),

    // Fields
    'fields' => array
    (
        'id' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL auto_increment"
        ),
        'tstamp' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL default '0'",
        ),
        'title' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_matching']['title'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'type' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_matching']['type'],
            'exclude'                 => true,
            'inputType'               => 'select',
            'sorting'                 => true,
            'options'                 => array(LeadMatchingController::TYPE),
            'eval'                    => array('includeBlankOption'=>true, 'submitOnChange'=>true, 'tl_class'=>'w50'),
            'sql'                     => "varchar(16) NOT NULL default ''"
        ),
        'marketingType' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_matching']['marketingType'],
            'exclude'                 => true,
            'inputType'               => 'select',
            'sorting'                 => true,
            'options'                 => array('kauf', 'miete'),
            'reference'               => &$GLOBALS['TL_LANG']['tl_lead_matching_meta'],
            'eval'                    => array('includeBlankOption'=>true, 'tl_class'=>'w50'),
            'sql'                     => "varchar(16) NOT NULL default ''"
        ),
        'marketingTypes' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_matching']['marketingTypes'],
            'exclude'                 => true,
            'inputType'               => 'checkboxWizard',
            'options'                 => array('kauf', 'miete'),
            'reference'               => &$GLOBALS['TL_LANG']['tl_lead_matching_meta'],
            'save_callback'           => array(
                array('ContaoEstateManager\LeadMatchingTool\Contao\Dca\TlLeadMatching', 'saveMarketingTypes')
            ),
            'eval'                    => array('multiple'=>true, 'tl_class'=>'w50'),
            'sql'                     => "blob NULL",
        ),
        'marketingTypesData' => array
        (
            'sql'                       => "blob NULL",
        ),
        'addBlankMarketingType' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_matching']['addBlankMarketingType'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('tl_class'=>'w50 clr'),
            'sql'                     => "char(1) NOT NULL default ''"
        ),
        'objectTypes' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_matching']['objectTypes'],
            'exclude'                 => true,
            'inputType'               => 'checkboxWizard',
            'foreignKey'              => 'tl_object_type.title',
            'save_callback'           => array(
                array('ContaoEstateManager\LeadMatchingTool\Contao\Dca\TlLeadMatching', 'saveObjectTypes')
            ),
            'eval'                    => array('multiple'=>true, 'tl_class'=>'w50 clr wizard'),
            'sql'                     => "varchar(1022) NOT NULL default ''",
            'relation'                => array('type'=>'hasMany', 'load'=>'lazy')
        ),
        'objectTypesData' => array
        (
            'sql'                     => "blob NULL",
        ),
        'addBlankObjectType' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_matching']['addBlankObjectType'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('tl_class'=>'w50 clr'),
            'sql'                     => "char(1) NOT NULL default ''"
        ),
        'preciseRegionSearch' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_matching']['preciseRegionSearch'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('submitOnChange'=>true, 'tl_class'=>'w50 clr'),
            'sql'                     => "char(1) NOT NULL default ''"
        ),
        'regions' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_matching']['regions'],
            'exclude'                 => true,
            'inputType'               => 'regionTree',
            'eval'                    => array('multiple'=>true, 'fieldType'=>'checkbox', 'tl_class'=>'w50 clr'),
            'save_callback'           => array(
                array('ContaoEstateManager\LeadMatchingTool\Contao\Dca\TlLeadMatching', 'saveRegions')
            ),
            'sql'                     => "blob NULL",
        ),
        'regionsData' => array
        (
            'sql'                     => "blob NULL",
        ),
        'addBlankRegion' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_matching']['addBlankRegion'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('tl_class'=>'w50 clr'),
            'sql'                     => "char(1) NOT NULL default ''"
        ),
        'numberOfItems' => array
        (
            'label'                     => &$GLOBALS['TL_LANG']['tl_lead_matching']['numberOfItems'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'rgxp'=>'natural', 'tl_class'=>'w50 clr'),
            'sql'                     => "smallint(5) unsigned NOT NULL default 3"
        ),
        'perPage' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_matching']['perPage'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('rgxp'=>'natural', 'tl_class'=>'w50'),
            'sql'                     => "smallint(5) unsigned NOT NULL default 0"
        ),
        'listMetaFields' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_matching']['listMetaFields'],
            'exclude'                 => true,
            'inputType'               => 'checkboxWizard',
            'options_callback'        => array('ContaoEstateManager\LeadMatchingTool\Contao\Dca\TlLeadMatching', 'getListMetaFields'),
            'reference'               => &$GLOBALS['TL_LANG']['tl_lead_matching_meta'],
            'eval'                    => array('multiple'=>true, 'tl_class'=>'w50 clr wizard'),
            'sql'                     => "text NULL"
        ),
        // ToDo: Combine related fields in module
        'groupRelatedFields' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_matching']['groupRelatedFields'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('tl_class'=>'m12 w50'),
            'sql'                     => "char(1) NOT NULL default '1'"
        ),
        'estateForm' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_matching']['estateForm'],
            'exclude'                 => true,
            'inputType'               => 'select',
            'options_callback'        => array('ContaoEstateManager\LeadMatchingTool\Contao\Dca\TlLeadMatching', 'getForms'),
            'eval'                    => array('mandatory'=>true, 'chosen'=>true, 'submitOnChange'=>true, 'tl_class'=>'w50 wizard'),
            'wizard' => array
            (
                array('ContaoEstateManager\LeadMatchingTool\Contao\Dca\TlLeadMatching', 'editForm')
            ),
            'sql'                     => "int(10) unsigned NOT NULL default 0"
        ),
        'contactForm' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_matching']['estateForm'],
            'exclude'                 => true,
            'inputType'               => 'select',
            'options_callback'        => array('ContaoEstateManager\LeadMatchingTool\Contao\Dca\TlLeadMatching', 'getForms'),
            'eval'                    => array('mandatory'=>true, 'chosen'=>true, 'submitOnChange'=>true, 'tl_class'=>'w50 wizard'),
            'wizard' => array
            (
                array('ContaoEstateManager\LeadMatchingTool\Contao\Dca\TlLeadMatching', 'editForm')
            ),
            'sql'                     => "int(10) unsigned NOT NULL default 0"
        ),
        'countResults' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_matching']['countResults'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('tl_class'=>'w50 m12'),
            'sql'                     => "char(1) NOT NULL default ''"
        ),
        'addContactForm' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_matching']['addContactForm'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('submitOnChange'=>true, 'tl_class'=>'w50'),
            'sql'                     => "char(1) NOT NULL default ''"
        ),
        'addEstateForm' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_matching']['addEstateForm'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('submitOnChange'=>true, 'tl_class'=>'w50'),
            'sql'                     => "char(1) NOT NULL default ''"
        ),
        'forceList' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_matching']['forceList'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('tl_class'=>'w50 m12'),
            'sql'                     => "char(1) NOT NULL default ''"
        ),
        'forceContact' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_matching']['forceContact'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('tl_class'=>'w50 m12'),
            'sql'                     => "char(1) NOT NULL default ''"
        ),
        'listItemTemplate' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_module']['listItemTemplate'],
            'exclude'                 => true,
            'inputType'               => 'select',
            'options_callback'        => function (){
                return Contao\Controller::getTemplateGroup('lmt_item_');
            },
            'eval'                    => array('chosen'=>true, 'mandatory'=>true, 'tl_class'=>'w50 clr'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'txtEstateHeadline' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_matching']['txtEstateHeadline'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'txtEstateDescription' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_matching']['txtEstateDescription'],
            'exclude'                 => true,
            'inputType'               => 'textarea',
            'eval'                    => array('rte'=>'tinyMCE', 'tl_class'=>'clr'),
            'sql'                     => "mediumtext NULL"
        ),
        'txtListHeadline' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_matching']['txtListHeadline'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50 clr'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'txtListDescription' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_matching']['txtListDescription'],
            'exclude'                 => true,
            'inputType'               => 'textarea',
            'eval'                    => array('rte'=>'tinyMCE', 'tl_class'=>'clr'),
            'sql'                     => "mediumtext NULL"
        ),
        'txtContactHeadline' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_matching']['txtContactHeadline'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'txtContactDescription' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_matching']['txtContactDescription'],
            'exclude'                 => true,
            'inputType'               => 'textarea',
            'eval'                    => array('rte'=>'tinyMCE', 'tl_class'=>'clr'),
            'sql'                     => "mediumtext NULL"
        ),
    )
);
