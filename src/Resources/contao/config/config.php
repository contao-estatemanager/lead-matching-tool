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
$GLOBALS['TL_ESTATEMANAGER_ADDONS'][] = array('ContaoEstateManager\\LeadMatchingTool', 'AddonManager');

if(ContaoEstateManager\LeadMatchingTool\AddonManager::valid()) {

    // Back end modules
    array_insert($GLOBALS['BE_MOD'], -1, array
    (
        'tools' => array
        (
            'searchcriteria' => array
            (
                'tables'                => array('tl_searchcriteria'),
            )
        )
    ));

    // Front end modules
    $GLOBALS['FE_MOD']['miscellaneous']['leadMatching'] = '\\ContaoEstateManager\\LeadMatchingTool\\ModuleLeadMatching';

    // Models
    $GLOBALS['TL_MODELS']['tl_searchcriteria'] = '\\ContaoEstateManager\\LeadMatchingTool\\LeadMatchingModel';

    // Style sheet
    if (TL_MODE == 'BE')
    {
        $GLOBALS['TL_CSS'][] = 'bundles/estatemanagerleadmatchingtool/tools.css|static';
    }
}
