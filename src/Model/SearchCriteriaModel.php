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

namespace ContaoEstateManager\LeadMatchingTool\Model;

use Contao\Database;
use Contao\Model;
use Contao\Model\Collection;
use ContaoEstateManager\LeadMatchingTool\Controller\FrontendModule\LeadMatchingController;
use ContaoEstateManager\RegionEntity\RegionConnectionModel;

/**
 * @property int    $id
 * @property int    $tstamp
 * @property string $title
 * @property string $marketing
 * @property bool   $published
 */
class SearchCriteriaModel extends Model
{
    /**
     * Table name.
     *
     * @var string
     */
    protected static $strTable = 'tl_search_criteria';

    /**
     * Execute query with given parameters.
     *
     * @return Database\Result|Collection
     */
    public static function execute(string $strQuery, array $arrParameter, bool $blnCollection = false, ?array $arrOptions = null)
    {
        $db = Database::getInstance()->prepare($strQuery);

        if ($arrOptions['limit'] && $arrOptions['offset'])
        {
            $db->limit($arrOptions['limit'], $arrOptions['offset']);
        }
        elseif ($arrOptions['limit'])
        {
            $db->limit($arrOptions['limit']);
        }

        $res = $db->execute($arrParameter);

        if (!$blnCollection)
        {
            return $res;
        }

        return static::createCollectionFromDbResult($res, static::$strTable);
    }

    /**
     * Generate filter query string.
     */
    public static function createFilterQuery(string $strSelect, LeadMatchingModel $objConfig, ?array $formData = null): array
    {
        $strTable = static::$strTable;
        $arrFieldOptions = $GLOBALS['TL_DCA']['tl_lead_matching']['fields']['estateFormMetaFields']['leadMatching'] ?? [];

        // Query part builder
        $q = function (string $strField, string $o = '=') use ($strTable) {
            return vsprintf("$strTable.%s%s?", [$strField, $o]);
        };

        // Create default query parts
        $arrCollection = [
            'published' => [$q('published'), [1]],
        ];

        // Add marketing type
        if ($objConfig->marketingType)
        {
            $arrCollection[LeadMatchingController::FIELD_MARKETING] = [$q(LeadMatchingController::FIELD_MARKETING), [$objConfig->marketingType]];
        }

        if (null !== $formData)
        {
            foreach ($formData as $strName => $varValue)
            {
                // Check if the field must be skipped in filtering
                $blnSkip = (bool) ($arrFieldOptions[$strName]['filter']['skip'] ?? false);

                // Check if the field has a different name
                $strField = ($arrFieldOptions[$strName]['filter']['fieldName'] ?? null) ?? $strName;

                if ($varValue && !$blnSkip)
                {
                    switch ($strName)
                    {
                        case LeadMatchingController::FIELD_MARKETING:
                        case LeadMatchingController::FIELD_OBJECT_TYPES:
                            $arrCollection[$strName] = [$q($strField), [$varValue]];
                            break;

                        case LeadMatchingController::FIELD_REGIONS:
                            if ((bool) $objConfig->preciseRegionSearch)
                            {
                                $regionTable = RegionConnectionModel::getTable();

                                $strSelect .= ' LEFT JOIN '.$regionTable.' ON '.$regionTable.'.pid='.$strTable.'.id';
                                $arrCollection[LeadMatchingController::FIELD_REGIONS] = [
                                    '(('.$regionTable.'.rid=? AND '.$regionTable.'.ptable=?) OR '.$strTable.'.regions IS NULL)',
                                    [
                                        $varValue,
                                        $strTable,
                                    ],
                                ];
                            }
                            break;

                        default:
                            $arrCollection[$strField] = [
                                '('.$q($strField.'_from', '<=').' OR '.$q($strField.'_from').') AND ('.$q($strField.'_to', '>=').' OR '.$q($strField.'_to', '=').')',
                                [
                                    (float) $varValue,
                                    '',
                                    (float) $varValue,
                                    '',
                                ],
                            ];
                    }
                }
            }
        }

        $arrQuery = [];
        $arrValues = [];

        foreach ($arrCollection as $queryPair)
        {
            [$query, $values] = $queryPair;

            $arrQuery[] = $query;

            $arrValues = [
                ...$arrValues,
                ...$values,
            ];
        }

        return [$strSelect.' WHERE '.implode(' AND ', $arrQuery), $arrValues];
    }
}
