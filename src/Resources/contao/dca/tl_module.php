<?php
/**
 * This file is part of Contao EstateManager.
 *
 * @link      https://www.contao-estatemanager.com/
 * @source    https://github.com/contao-estatemanager/lead-matching-tool
 * @copyright Copyright (c) 2019  Oveleon GbR (https://www.oveleon.de)
 * @license   https://www.contao-estatemanager.com/lizenzbedingungen.html
 */

// Load translations
\System::loadLanguageFile('tl_module');

// Load DataContainer
\Controller::loadDataContainer('tl_module');

// Add callback
$GLOBALS['TL_DCA']['tl_module']['config']['onload_callback'][]  = array('tl_module_lead_matching', 'translateFormLabel');

// Add palettes
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][]    = 'lmtMode';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][]    = 'addEstateForm';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][]    = 'addContactForm';

$GLOBALS['TL_DCA']['tl_module']['palettes']['leadMatching']      = '{title_legend},name,headline,type;{config_legend},lmtMode;{list_config},numberOfItems,perPage,lmtCountResults;{form_legend},addEstateForm,addContactForm;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';

$GLOBALS['TL_DCA']['tl_module']['subpalettes']['lmtMode_system'] = 'lmtMarketing,lmtMetaFields';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['addEstateForm']  = 'estateForm,forceList';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['addContactForm'] = 'form,forceContact';

// Add fields
$GLOBALS['TL_DCA']['tl_module']['fields']['lmtMode'] = array
(
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['lmtMode'],
    'inputType'                 => 'select',
    'sorting'                   => true,
    'options'                   => array('system'),
    'eval'                      => array('includeBlankOption'=>true, 'submitOnChange'=>true, 'tl_class'=>'w50'),
    'sql'                       => "varchar(16) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['lmtMetaFields'] = array
(
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['lmtMetaFields'],
    'inputType'                 => 'checkbox',
    'options'                   => array('room_from', 'room_to'),
    'eval'                      => array('multiple'=>true, 'tl_class'=>'w50 clr'),
    'sql'                       => "text NULL"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['lmtMarketing'] = array
(
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['lmtMarketing'],
    'inputType'                 => 'select',
    'options'                   => array('kauf', 'miete'),
    'eval'                      => array('includeBlankOption'=>true, 'tl_class'=>'w50'),
    'sql'                       => "varchar(16) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['lmtCountResults'] = array
(
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['lmtCountResults'],
    'inputType'               => 'checkbox',
    'eval'                    => array('tl_class'=>'w50'),
    'sql'                     => "char(1) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['forceList'] = array
(
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['forceList'],
    'inputType'               => 'checkbox',
    'eval'                    => array('tl_class'=>'w50 m12'),
    'sql'                     => "char(1) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['forceContact'] = array
(
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['forceContact'],
    'inputType'               => 'checkbox',
    'eval'                    => array('tl_class'=>'w50 m12'),
    'sql'                     => "char(1) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['addContactForm'] = array
(
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['addContactForm'],
    'inputType'               => 'checkbox',
    'eval'                    => array('submitOnChange'=>true, 'tl_class'=>'w50 clr'),
    'sql'                     => "char(1) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['addEstateForm'] = array
(
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['addEstateForm'],
    'inputType'               => 'checkbox',
    'eval'                    => array('submitOnChange'=>true, 'tl_class'=>'w50'),
    'sql'                     => "char(1) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['estateForm'] = array
(
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['estateForm'],
    'inputType'               => 'select',
    'foreignKey'              => 'tl_form.title',
    'options_callback'        => array('tl_module', 'getForms'),
    'eval'                    => array('chosen'=>true, 'tl_class'=>'w50 wizard'),
    'sql'                     => "int(10) unsigned NOT NULL default 0",
    'relation'                => array('type'=>'hasOne', 'load'=>'lazy')
);

/**
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
class tl_module_lead_matching extends Backend
{
    /**
     * Import the back end user object
     */
    public function __construct()
    {
        parent::__construct();
        $this->import('BackendUser', 'User');
    }

    public function translateFormLabel()
    {
        $GLOBALS['TL_LANG']['tl_module']['form'] = $GLOBALS['TL_LANG']['tl_module']['contactForm'];
    }
}
