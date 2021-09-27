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

$GLOBALS['TL_DCA']['tl_module']['palettes']['lead_matching'] = '{title_legend},name,headline,type;{config_legend},lmtConfig;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';

// Add fields
$GLOBALS['TL_DCA']['tl_module']['fields']['lmtConfig'] = [
    'exclude' => true,
    'inputType' => 'select',
    'options_callback' => ['ContaoEstateManager\LeadMatchingTool\Contao\Dca\TlModule', 'getLeadMatchingConfiguration'],
    'eval' => ['chosen' => true, 'mandatory' => true, 'tl_class' => 'w50'],
    'sql' => "varchar(255) NOT NULL default ''",
];
