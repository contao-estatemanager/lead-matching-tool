<?php
$GLOBALS['TL_DCA']['tl_module']['palettes']['lead_matching'] = '{title_legend},name,headline,type;{config_legend},lmtConfig;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';

// Add fields
$GLOBALS['TL_DCA']['tl_module']['fields']['lmtConfig'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_module']['lmtConfig'],
    'exclude'                 => true,
    'inputType'               => 'select',
    'options_callback'        => array('ContaoEstateManager\LeadMatchingTool\Contao\Dca\TlModule', 'getLeadMatchingConfiguration'),
    'eval'                    => array('chosen'=>true, 'mandatory'=>true, 'tl_class'=>'w50'),
    'sql'                     => "varchar(255) NOT NULL default ''"
);
