<?php
// ESTATEMANAGER
$GLOBALS['TL_ESTATEMANAGER_ADDONS'][] = array('ContaoEstateManager\LeadMatchingTool\EstateManager', 'AddonManager');

use ContaoEstateManager\LeadMatchingTool\Model\SearchcriteriaModel;
use ContaoEstateManager\LeadMatchingTool\Model\LeadMatchingModel;

if(ContaoEstateManager\LeadMatchingTool\EstateManager\AddonManager::valid()) {

    // Back end modules
    array_insert($GLOBALS['BE_MOD']['real_estate'], count($GLOBALS['BE_MOD']['real_estate'] ?? 3), [
        'searchcriteria' => [
            'tables' => ['tl_searchcriteria']
        ],
        'leadmatching' => [
            'tables' => ['tl_lead_matching']
        ]
    ]);

    // Models
    $GLOBALS['TL_MODELS']['tl_searchcriteria'] = SearchcriteriaModel::class;
    $GLOBALS['TL_MODELS']['tl_lead_matching']  = LeadMatchingModel::class;
}
