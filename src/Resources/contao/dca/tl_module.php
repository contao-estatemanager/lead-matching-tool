<?php
$GLOBALS['TL_DCA']['tl_module']['palettes']['lead_matching'] = '{title_legend},name,headline,type;{config_legend},lmtConfig;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';

// Add fields
$GLOBALS['TL_DCA']['tl_module']['fields']['lmtConfig'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_module']['lmtConfig'],
    'exclude'                 => true,
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
class tl_module_lead_matching extends Contao\Backend
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
     * Returns an array of lead matching configurations
     *
     * @return array
     */
    public function getLeadMatchingConfiguration(): array
    {
        $arrOptions = array();

        $objConfigs = ContaoEstateManager\LeadMatchingTool\Model\LeadMatchingModel::findAll();

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
