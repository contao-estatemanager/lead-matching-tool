<?php
/**
 * This file is part of Contao EstateManager.
 *
 * @link      https://www.contao-estatemanager.com/
 * @source    https://github.com/contao-estatemanager/lead-matching-tool
 * @copyright Copyright (c) 2019  Oveleon GbR (https://www.oveleon.de)
 * @license   https://www.contao-estatemanager.com/lizenzbedingungen.html
 */

$GLOBALS['TL_DCA']['tl_module']['palettes']['leadMatching'] = '{title_legend},name,headline,type;{config_legend},lmtConfig;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';

// Add fields
$GLOBALS['TL_DCA']['tl_module']['fields']['lmtConfig'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_module']['lmtConfig'],
    'inputType'               => 'select',
    'options_callback'        => array('tl_module_lead_matching', 'getLeadMatchingConfiguration'),
    'eval'                    => array('chosen'=>true, 'mandatory'=>true, 'tl_class'=>'w50'),
    'sql'                     => "varchar(255) NOT NULL default ''"
);

/**
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */

use ContaoEstateManager\LeadMatchingTool\LeadMatchingModel;

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

    public function getLeadMatchingConfiguration()
    {
        $arrOptions = array();

        $objConfigs = LeadMatchingModel::findAll();

        if($objConfigs)
        {
            while($objConfigs->next())
            {
                $arrOptions[ $objConfigs->id ] = $objConfigs->title;
            }
        }

        return $arrOptions;
    }

}
