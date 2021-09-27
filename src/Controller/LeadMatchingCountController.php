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

namespace ContaoEstateManager\LeadMatchingTool\Controller;

use Contao\CoreBundle\Framework\ContaoFramework;
use ContaoEstateManager\LeadMatchingTool\Model\LeadMatchingModel;
use ContaoEstateManager\LeadMatchingTool\Model\SearchCriteriaModel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_scope" = "frontend"})
 */
class LeadMatchingCountController
{
    /**
     * @var ContaoFramework
     */
    private $framework;

    public function __construct(ContaoFramework $framework)
    {
        $this->framework = $framework;
    }

    /**
     * Count results.
     *
     * @Route("/leadmatching/count/{configId}", name="leadmatching_count")
     */
    public function count(Request $request, int $configId): JsonResponse
    {
        $this->framework->initialize();

        // Get lead matching configuration
        $objConfig = LeadMatchingModel::findByIdOrAlias($configId);

        // Prepare query
        $strTable = SearchCriteriaModel::getTable();
        $strSelect = 'SELECT COUNT('.$strTable.'.id) as numberOfItems FROM '.$strTable;

        // Create filter query
        [$query, $parameter] = SearchCriteriaModel::createFilterQuery($strSelect, $objConfig, $request->request->all());

        // Execute filter query
        $objSearchCriteria = SearchCriteriaModel::execute($query, $parameter);

        return new JsonResponse([
            'count' => $objSearchCriteria->numberOfItems ?? 0,
        ]);
    }
}
