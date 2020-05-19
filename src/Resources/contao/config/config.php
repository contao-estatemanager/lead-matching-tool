<?php
/**
 * This file is part of Contao EstateManager.
 *
 * @link      https://www.contao-estatemanager.com/
 * @source    https://github.com/contao-estatemanager/lead-matching-tool
 * @copyright Copyright (c) 2019  Oveleon GbR (https://www.oveleon.de)
 * @license   https://www.contao-estatemanager.com/lizenzbedingungen.html
 */

// ESTATEMANAGER
$GLOBALS['TL_ESTATEMANAGER_ADDONS'][] = array('ContaoEstateManager\LeadMatchingTool', 'AddonManager');

if(ContaoEstateManager\LeadMatchingTool\AddonManager::valid()) {

    // Back end modules
    $GLOBALS['BE_MOD']['real_estate']['searchcriteria'] = array
    (
        'tables' => array('tl_searchcriteria'),
    );

    $GLOBALS['BE_MOD']['system']['leadmatching'] = array
    (
        'tables' => array('tl_lead_matching')
    );

    // Front end modules
    $GLOBALS['FE_MOD']['miscellaneous']['leadMatching'] = 'ContaoEstateManager\LeadMatchingTool\ModuleLeadMatching';

    // Models
    $GLOBALS['TL_MODELS']['tl_searchcriteria'] = 'ContaoEstateManager\LeadMatchingTool\SearchcriteriaModel';
    $GLOBALS['TL_MODELS']['tl_lead_matching']  = 'ContaoEstateManager\LeadMatchingTool\LeadMatchingModel';
}
