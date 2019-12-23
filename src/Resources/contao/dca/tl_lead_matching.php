<?php
/**
 * This file is part of Contao EstateManager.
 *
 * @link      https://www.contao-estatemanager.com/
 * @source    https://github.com/contao-estatemanager/lead-matching-tool
 * @copyright Copyright (c) 2019  Oveleon GbR (https://www.oveleon.de)
 * @license   https://www.contao-estatemanager.com/lizenzbedingungen.html
 */

\System::loadLanguageFile('tl_lead_matching_meta');

$GLOBALS['TL_DCA']['tl_lead_matching'] = array
(

    // Config
    'config' => array
    (
        'dataContainer'               => 'Table',
        'switchToEdit'                => true,
        'enableVersioning'            => true,
        'markAsCopy'                  => 'title',
        'onload_callback' => array
        (
            array('tl_lead_matching', 'checkPermission')
        ),
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
            'fields'                  => array('id'),
            'flag'                    => 1,
            'panelLayout'             => 'filter;sort,search,limit'
        ),
        'label' => array
        (
            'fields'                  => array('id', 'title', 'marketingType'),
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
        '__selector__'                => array('type', 'addEstateForm', 'addContactForm'),
        'default'                     => '{title_legend},title,type;',
        'system'                      => '{title_legend},title,type;{config_legend},marketingType,objectTypes,addBlankObjectType,regions,addBlankRegion;{searchcriteria_legend},listMetaFields,countResults,listItemTemplate;{estate_form_legend},addEstateForm;{contact_form_legend},addContactForm;'
    ),

    // Subpalettes
    'subpalettes' => array
    (
        'addEstateForm'               => 'estateFormTemplate,forceList,estateFormMetaFields,estateFormMetaFieldsMandatory',
        'addContactForm'              => 'contactFormTemplate,forceContact,contactFormMetaFields,contactFormMetaFieldsMandatory,salutationFields,addBlankSalutation'
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
        ),
        'title' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_matching']['title'],
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'type' => array
        (
            'label'                     => &$GLOBALS['TL_LANG']['tl_lead_matching']['type'],
            'inputType'                 => 'select',
            'sorting'                   => true,
            'options'                   => array('system'),
            'eval'                      => array('includeBlankOption'=>true, 'submitOnChange'=>true, 'tl_class'=>'w50'),
            'sql'                       => "varchar(16) NOT NULL default ''"
        ),
        'marketingType' => array
        (
            'label'                     => &$GLOBALS['TL_LANG']['tl_lead_matching']['marketingType'],
            'inputType'                 => 'select',
            'sorting'                   => true,
            'options'                   => array('kauf', 'miete'),
            'eval'                      => array('includeBlankOption'=>true, 'tl_class'=>'w50'),
            'sql'                       => "varchar(16) NOT NULL default ''"
        ),
        'objectTypes' => array
        (
            'label'                     => &$GLOBALS['TL_LANG']['tl_lead_matching']['objectTypes'],
            'inputType'                 => 'keyValueWizard',
            'eval'                      => array('multiple'=>true, 'tl_class'=>'w50 clr'),
            'sql'                       => "blob NULL",
        ),
        'regions' => array
        (
            'label'                     => &$GLOBALS['TL_LANG']['tl_lead_matching']['regions'],
            'inputType'                 => 'regionTree',
            'eval'                      => array('multiple'=>true, 'fieldType'=>'checkbox', 'tl_class'=>'w50 clr'),
            'sql'                       => "blob NULL",
        ),
        'salutationFields' => array
        (
            'label'                     => &$GLOBALS['TL_LANG']['tl_lead_matching']['salutationFields'],
            'inputType'                 => 'keyValueWizard',
            'eval'                      => array('multiple'=>true, 'tl_class'=>'w50 clr'),
            'sql'                       => "blob NULL",
        ),
        'listMetaFields' => array
        (
            'label'                     => &$GLOBALS['TL_LANG']['tl_lead_matching']['listMetaFields'],
            'inputType'                 => 'checkboxWizard',
            'options_callback'          => array('tl_lead_matching', 'getListMetaFields'),
            'reference'                 => &$GLOBALS['TL_LANG']['tl_lead_matching_meta'],
            'eval'                      => array('multiple'=>true, 'tl_class'=>'w50 clr wizard'),
            'sql'                       => "text NULL"
        ),
        'estateFormMetaFields' => array
        (
            'label'                     => &$GLOBALS['TL_LANG']['tl_lead_matching']['estateFormMetaFields'],
            'inputType'                 => 'checkboxWizard',
            'options'                   => array('objecttypes', 'regions', 'room_from', 'area_from', 'price_from'),
            'reference'                 => &$GLOBALS['TL_LANG']['tl_lead_matching_meta'],
            'eval'                      => array('multiple'=>true, 'mandatory'=>true, 'tl_class'=>'w50 wizard clr'),
            'sql'                       => "text NULL"
        ),
        'estateFormMetaFieldsMandatory' => array
        (
            'label'                     => &$GLOBALS['TL_LANG']['tl_lead_matching']['estateFormMetaFieldsMandatory'],
            'inputType'                 => 'checkbox',
            'options'                   => array('objecttypes', 'regions', 'room_from', 'area_from', 'price_from'),
            'reference'                 => &$GLOBALS['TL_LANG']['tl_lead_matching_meta'],
            'eval'                      => array('multiple'=>true, 'tl_class'=>'w50'),
            'sql'                       => "text NULL"
        ),
        'contactFormMetaFields' => array
        (
            'label'                     => &$GLOBALS['TL_LANG']['tl_lead_matching']['contactFormMetaFields'],
            'inputType'                 => 'checkboxWizard',
            'options'                   => array('salutation', 'firstname', 'name', 'email', 'phone'),
            'reference'                 => &$GLOBALS['TL_LANG']['tl_lead_matching_meta'],
            'eval'                      => array('multiple'=>true, 'mandatory'=>true, 'tl_class'=>'w50 wizard clr'),
            'sql'                       => "text NULL"
        ),
        'contactFormMetaFieldsMandatory' => array
        (
            'label'                     => &$GLOBALS['TL_LANG']['tl_lead_matching']['contactFormMetaFieldsMandatory'],
            'inputType'                 => 'checkbox',
            'options'                   => array('salutation', 'firstname', 'name', 'email', 'phone'),
            'reference'                 => &$GLOBALS['TL_LANG']['tl_lead_matching_meta'],
            'eval'                      => array('multiple'=>true, 'tl_class'=>'w50'),
            'sql'                       => "text NULL"
        ),
        'countResults' => array
        (
            'label'                     => &$GLOBALS['TL_LANG']['tl_lead_matching']['countResults'],
            'inputType'                 => 'checkbox',
            'eval'                      => array('tl_class'=>'w50'),
            'sql'                       => "char(1) NOT NULL default ''"
        ),
        'addBlankObjectType' => array
        (
            'label'                     => &$GLOBALS['TL_LANG']['tl_lead_matching']['addBlankObjectType'],
            'inputType'                 => 'checkbox',
            'eval'                      => array('tl_class'=>'w50 clr'),
            'sql'                       => "char(1) NOT NULL default ''"
        ),
        'addBlankRegion' => array
        (
            'label'                     => &$GLOBALS['TL_LANG']['tl_lead_matching']['addBlankRegion'],
            'inputType'                 => 'checkbox',
            'eval'                      => array('tl_class'=>'w50 clr'),
            'sql'                       => "char(1) NOT NULL default ''"
        ),
        'addBlankSalutation' => array
        (
            'label'                     => &$GLOBALS['TL_LANG']['tl_lead_matching']['addBlankSalutation'],
            'inputType'                 => 'checkbox',
            'eval'                      => array('tl_class'=>'w50 clr'),
            'sql'                       => "char(1) NOT NULL default ''"
        ),
        'addContactForm' => array
        (
            'label'                     => &$GLOBALS['TL_LANG']['tl_lead_matching']['addContactForm'],
            'inputType'                 => 'checkbox',
            'eval'                      => array('submitOnChange'=>true, 'tl_class'=>'w50'),
            'sql'                       => "char(1) NOT NULL default ''"
        ),
        'addEstateForm' => array
        (
            'label'                     => &$GLOBALS['TL_LANG']['tl_lead_matching']['addEstateForm'],
            'inputType'                 => 'checkbox',
            'eval'                      => array('submitOnChange'=>true, 'tl_class'=>'w50'),
            'sql'                       => "char(1) NOT NULL default ''"
        ),
        'forceList' => array
        (
            'label'                     => &$GLOBALS['TL_LANG']['tl_lead_matching']['forceList'],
            'inputType'                 => 'checkbox',
            'eval'                      => array('tl_class'=>'w50 m12'),
            'sql'                       => "char(1) NOT NULL default ''"
        ),
        'forceContact' => array
        (
            'label'                     => &$GLOBALS['TL_LANG']['tl_lead_matching']['forceContact'],
            'inputType'                 => 'checkbox',
            'eval'                      => array('tl_class'=>'w50 m12'),
            'sql'                       => "char(1) NOT NULL default ''"
        ),
        'estateFormTemplate' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_matching']['estateFormTemplate'],
            'inputType'               => 'select',
            'options_callback'        => array('tl_lead_matching', 'getEstateFormTemplates'),
            'eval'                    => array('chosen'=>true, 'mandatory'=>true, 'tl_class'=>'w50'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'contactFormTemplate' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_module']['contactFormTemplate'],
            'inputType'               => 'select',
            'options_callback'        => array('tl_lead_matching', 'getContactFormTemplates'),
            'eval'                    => array('chosen'=>true, 'mandatory'=>true, 'tl_class'=>'w50'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'listItemTemplate' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_module']['listItemTemplate'],
            'inputType'               => 'select',
            'options_callback'        => array('tl_lead_matching', 'getListItemTemplates'),
            'eval'                    => array('chosen'=>true, 'mandatory'=>true, 'tl_class'=>'w50 clr'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        )
    )
);

/**
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
class tl_lead_matching extends Backend
{

    /**
     * Import the back end user object
     */
    public function __construct()
    {
        parent::__construct();
        $this->import('BackendUser', 'User');
    }

    /**
     * Check permissions to edit table tl_lead_matching
     *
     * @throws Contao\CoreBundle\Exception\AccessDeniedException
     */
    public function checkPermission()
    {
        return;
    }

    /**
     * Returns an array of search criteria fields
     *
     * @return array
     */
    public function getListMetaFields()
    {
        $arrOptions = array();
        $arrSkip = array('published');

        \Controller::loadDataContainer('tl_searchcriteria');

        foreach($GLOBALS['TL_DCA']['tl_searchcriteria']['fields'] as $key=>$opt)
        {
            if(!in_array($key, $arrSkip))
            {
                $arrOptions[] = $key;
            }
        }

        return $arrOptions;
    }

    /**
     * Return all estate form templates as array
     *
     * @return array
     */
    public function getEstateFormTemplates()
    {
        return $this->getTemplateGroup('lmt_estate_');
    }

    /**
     * Return all estate form templates as array
     *
     * @return array
     */
    public function getContactFormTemplates()
    {
        return $this->getTemplateGroup('lmt_contact_');
    }

    /**
     * Return all list item templates as array
     *
     * @return array
     */
    public function getListItemTemplates()
    {
        return $this->getTemplateGroup('lmt_item_');
    }
}
