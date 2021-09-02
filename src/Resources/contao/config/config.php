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

$GLOBALS['TL_ESTATEMANAGER_ADDONS'][] = ['ContaoEstateManager\LeadMatchingTool\EstateManager', 'AddonManager'];

use ContaoEstateManager\LeadMatchingTool\Model\LeadMatchingModel;
use ContaoEstateManager\LeadMatchingTool\Model\SearchCriteriaModel;

if (ContaoEstateManager\LeadMatchingTool\EstateManager\AddonManager::valid())
{
    // Back end modules
    array_insert($GLOBALS['BE_MOD']['real_estate'], \count($GLOBALS['BE_MOD']['real_estate'] ?? 3), [
        'searchcriteria' => [
            'tables' => ['tl_search_criteria'],
        ],
        'leadmatching' => [
            'tables' => ['tl_lead_matching'],
        ],
    ]);

    // Models
    $GLOBALS['TL_MODELS']['tl_search_criteria'] = SearchCriteriaModel::class;
    $GLOBALS['TL_MODELS']['tl_lead_matching'] = LeadMatchingModel::class;
}
