<?php
/**
 * This file is part of Contao EstateManager.
 *
 * @link      https://www.contao-estatemanager.com/
 * @source    https://github.com/contao-estatemanager/lead-matching-tool
 * @copyright Copyright (c) 2019  Oveleon GbR (https://www.oveleon.de)
 * @license   https://www.contao-estatemanager.com/lizenzbedingungen.html
 */

Contao\System::loadLanguageFile('tl_lead_matching_meta');

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
        '__selector__'                => array('type', 'preciseRegionSearch', 'addEstateForm', 'addContactForm'),
        'default'                     => '{title_legend},title,type;',
        'system'                      => '{title_legend},title,type;{config_legend},marketingType;{data_legend},marketingTypes,addBlankMarketingType,objectTypes,addBlankObjectType,preciseRegionSearch;{searchcriteria_legend},listMetaFields,txtListHeadline,txtListDescription,numberOfItems,perPage,listItemTemplate,countResults;{estate_form_legend},addEstateForm;{contact_form_legend},addContactForm;'
    ),

    // Subpalettes
    'subpalettes' => array
    (
        'preciseRegionSearch'         => 'regions,addBlankRegion',
        'addEstateForm'               => 'txtEstateHeadline,forceList,txtEstateDescription,estateFormMetaFields,estateFormMetaFieldsMandatory,rangeOptions,estateFormTemplate',
        'addContactForm'              => 'txtContactHeadline,forceContact,txtContactDescription,contactFormMetaFields,contactFormMetaFieldsMandatory,contactFormCheckboxes,salutationFields,addBlankSalutation,contactFormTemplate,mailSubject,mailTo,jumpTo'
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
            'options'                 => array('system'),
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
                array('tl_lead_matching', 'saveMarketingTypes')
            ),
            'eval'                    => array('multiple'=>true, 'tl_class'=>'w50'),
            'sql'                     => "blob NULL",
        ),
        'marketingTypesData' => array
        (
            'sql'                       => "blob NULL",
        ),
        'objectTypes' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_matching']['objectTypes'],
            'exclude'                 => true,
            'inputType'               => 'checkboxWizard',
            'foreignKey'              => 'tl_object_type.title',
            'save_callback'           => array(
                array('tl_lead_matching', 'saveObjectTypes')
            ),
            'eval'                    => array('multiple'=>true, 'tl_class'=>'w50 clr wizard'),
            'sql'                     => "varchar(1022) NOT NULL default ''",
            'relation'                => array('type'=>'hasMany', 'load'=>'lazy')
        ),
        'objectTypesData' => array
        (
            'sql'                     => "blob NULL",
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
                array('tl_lead_matching', 'saveRegions')
            ),
            'sql'                     => "blob NULL",
        ),
        'regionsData' => array
        (
            'sql'                     => "blob NULL",
        ),
        'salutationFields' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_matching']['salutationFields'],
            'exclude'                 => true,
            'inputType'               => 'keyValueWizard',
            'eval'                    => array('multiple'=>true, 'tl_class'=>'w50 clr'),
            'sql'                     => "blob NULL",
        ),
        'rangeOptions' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_matching']['rangeOptions'],
            'exclude'                 => true,
            'inputType'               => 'keyValueWizard',
            'eval'                    => array('multiple'=>true, 'tl_class'=>'w50 clr'),
            'sql'                     => "blob NULL",
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
            'options_callback'        => array('tl_lead_matching', 'getListMetaFields'),
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
        'estateFormMetaFields' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_matching']['estateFormMetaFields'],
            'exclude'                 => true,
            'inputType'               => 'checkboxWizard',
            'options'                 => array('marketingType', 'objectTypes', 'regions', 'range', 'room', 'area', 'price'),
            'reference'               => &$GLOBALS['TL_LANG']['tl_lead_matching_meta'],
            'eval'                    => array('multiple'=>true, 'mandatory'=>true, 'tl_class'=>'w50 wizard clr'),
            'sql'                     => "text NULL"
        ),
        'estateFormMetaFieldsMandatory' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_matching']['estateFormMetaFieldsMandatory'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'options'                 => array('marketingType', 'objectTypes', 'regions', 'range', 'room', 'area', 'price'),
            'reference'               => &$GLOBALS['TL_LANG']['tl_lead_matching_meta'],
            'eval'                    => array('multiple'=>true, 'tl_class'=>'w50'),
            'sql'                     => "text NULL"
        ),
        'contactFormMetaFields' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_matching']['contactFormMetaFields'],
            'exclude'                 => true,
            'inputType'               => 'checkboxWizard',
            'options_callback'        => array('tl_lead_matching', 'getContactFormFields'),
            'reference'               => &$GLOBALS['TL_LANG']['tl_lead_matching_meta'],
            'eval'                    => array('multiple'=>true, 'mandatory'=>true, 'tl_class'=>'w50 wizard clr'),
            'sql'                     => "text NULL"
        ),
        'contactFormMetaFieldsMandatory' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_matching']['contactFormMetaFieldsMandatory'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'options_callback'        => array('tl_lead_matching', 'getContactFormFields'),
            'reference'               => &$GLOBALS['TL_LANG']['tl_lead_matching_meta'],
            'eval'                    => array('multiple'=>true, 'tl_class'=>'w50'),
            'sql'                     => "text NULL"
        ),
        'contactFormCheckboxes' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_matching']['contactFormCheckboxes'],
            'exclude'                 => true,
            'inputType'               => 'keyValueWizard',
            'eval'                    => array('multiple'=>true, 'tl_class'=>'w50 clr'),
            'sql'                     => "blob NULL",
        ),
        'countResults' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_matching']['countResults'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('tl_class'=>'w50 m12'),
            'sql'                     => "char(1) NOT NULL default ''"
        ),
        'addBlankMarketingType' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_matching']['addBlankMarketingType'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('tl_class'=>'w50 clr'),
            'sql'                     => "char(1) NOT NULL default ''"
        ),
        'addBlankObjectType' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_matching']['addBlankObjectType'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('tl_class'=>'w50 clr'),
            'sql'                     => "char(1) NOT NULL default ''"
        ),
        'addBlankRegion' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_matching']['addBlankRegion'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('tl_class'=>'w50 clr'),
            'sql'                     => "char(1) NOT NULL default ''"
        ),
        'addBlankSalutation' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_matching']['addBlankSalutation'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('tl_class'=>'w50 clr'),
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
        'estateFormTemplate' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_matching']['estateFormTemplate'],
            'exclude'                 => true,
            'inputType'               => 'select',
            'options_callback'        => function (){
                return Contao\Controller::getTemplateGroup('lmt_estate_');
            },
            'eval'                    => array('chosen'=>true, 'mandatory'=>true, 'tl_class'=>'w50 clr'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'contactFormTemplate' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_module']['contactFormTemplate'],
            'exclude'                 => true,
            'inputType'               => 'select',
            'options_callback'        => function (){
                return Contao\Controller::getTemplateGroup('lmt_contact_');
            },
            'eval'                    => array('chosen'=>true, 'mandatory'=>true, 'tl_class'=>'w50 clr'),
            'sql'                     => "varchar(255) NOT NULL default ''"
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
        'mailSubject' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_matching']['mailSubject'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50 clr'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'mailTo' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_matching']['mailTo'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50', 'rgxp'=>'email'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'jumpTo' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_lead_matching']['jumpTo'],
            'exclude'                 => true,
            'inputType'               => 'pageTree',
            'foreignKey'              => 'tl_page.title',
            'eval'                    => array('fieldType'=>'radio', 'tl_class'=>'clr'),
            'sql'                     => "int(10) unsigned NOT NULL default 0",
            'relation'                => array('type'=>'hasOne', 'load'=>'lazy')
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


/**
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
class tl_lead_matching extends Contao\Backend
{

    /**
     * Import the back end user object
     */
    public function __construct()
    {
        parent::__construct();
        $this->import('Contao\BackendUser', 'User');
    }

    /**
     * Returns an array of object types
     *
     * @param Contao\DataContainer $dc
     *
     * @return array
     */
    public function getContactFormFields(Contao\DataContainer $dc): array
    {
        $arrOptions = array('salutation', 'firstname', 'name', 'email', 'phone');

        $arrCheckboxes = Contao\StringUtil::deserialize($dc->activeRecord->contactFormCheckboxes);

        if($arrCheckboxes !== null)
        {
            foreach ($arrCheckboxes as $arrCheckbox)
            {
                $arrOptions[] = $arrCheckbox['key'];
            }
        }

        return $arrOptions;
    }

    /**
     * Save key value set of object types
     *
     * @param mixed                $varValue
     * @param Contao\DataContainer $dc
     *
     * @return string
     */
    public function saveObjectTypes($varValue, Contao\DataContainer $dc): string
    {
        if(!$varValue || $dc->activeRecord->type !== 'system')
        {
            return $varValue;
        }

        $arrChoosedTypes = Contao\StringUtil::deserialize($varValue);

        if($arrChoosedTypes === null)
        {
            return $varValue;
        }

        $arrColumns = array("id IN('" . implode("','", $arrChoosedTypes) . "')");

        $objObjectTypes = ContaoEstateManager\ObjectTypeEntity\ObjectTypeModel::findBy($arrColumns, array());

        if($objObjectTypes !== null)
        {
            $arrOptions = array();

            while($objObjectTypes->next())
            {
                $arrOptions[ $objObjectTypes->id ] = $objObjectTypes->title;
            }

            // Store the new object type data
            $this->Database->prepare("UPDATE tl_lead_matching SET objectTypesData=? WHERE id=?")
                           ->execute(serialize($arrOptions), $dc->id);
        }

        return $varValue;
    }

    /**
     * Save key value set of marketing types
     *
     * @param mixed                $varValue
     * @param Contao\DataContainer $dc
     *
     * @return string
     */
    public function saveMarketingTypes($varValue, Contao\DataContainer $dc): string
    {
        if(!$varValue || $dc->activeRecord->type !== 'system')
        {
            return $varValue;
        }

        $arrChoosedTypes = Contao\StringUtil::deserialize($varValue);

        if($arrChoosedTypes === null)
        {
            return $varValue;
        }

        $arrOptions = array();

        foreach ($arrChoosedTypes as $type)
        {
            $arrOptions[ $type ] = &$GLOBALS['TL_LANG']['tl_lead_matching_meta'][ $type ];
        }

        // Store the new object type data
        $this->Database->prepare("UPDATE tl_lead_matching SET marketingTypesData=? WHERE id=?")
                       ->execute(serialize($arrOptions), $dc->id);

        return $varValue;
    }

    /**
     * Save key value set of regions
     *
     * @param mixed                $varValue
     * @param Contao\DataContainer $dc
     *
     * @return string
     */
    public function saveRegions($varValue, Contao\DataContainer $dc): string
    {
        if(!$varValue || $dc->activeRecord->type !== 'system')
        {
            return $varValue;
        }

        $arrChoosedTypes = Contao\StringUtil::deserialize($varValue);

        if($arrChoosedTypes === null)
        {
            return $varValue;
        }

        $arrColumns = array("id IN('" . implode("','", $arrChoosedTypes) . "')");

        $objRegions = ContaoEstateManager\RegionEntity\RegionModel::findBy($arrColumns, array());

        if($objRegions !== null)
        {
            $arrOptions = array();

            while($objRegions->next())
            {
                $arrOptions[ $objRegions->id ] = $objRegions->title;
            }

            // Store the new object type data
            $this->Database->prepare("UPDATE tl_lead_matching SET regionsData=? WHERE id=?")
                           ->execute(serialize($arrOptions), $dc->id);
        }

        return $varValue;
    }

    /**
     * Returns an array of search criteria fields
     *
     * @return array
     */
    public function getListMetaFields(): array
    {
        $arrOptions = array();
        $arrSkip = array('published');

        Contao\Controller::loadDataContainer('tl_searchcriteria');

        foreach($GLOBALS['TL_DCA']['tl_searchcriteria']['fields'] as $key=>$opt)
        {
            if(!in_array($key, $arrSkip))
            {
                $arrOptions[] = $key;
            }
        }

        return $arrOptions;
    }
}
