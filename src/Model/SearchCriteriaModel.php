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
use Contao\System;
use ContaoEstateManager\LeadMatchingTool\Controller\FrontendModule\LeadMatchingController;
use ContaoEstateManager\RegionEntity\RegionConnectionModel;

/**
 * @property int    $id
 * @property string $vid
 * @property int    $tstamp
 * @property string $title
 * @property string $objectType
 * @property string $marketingType
 * @property string $room_from
 * @property string $room_to
 * @property string $area_from
 * @property string $area_to
 * @property string $price_from
 * @property string $price_to
 * @property string $latitude
 * @property string $longitude
 * @property string $postalcode
 * @property string $city
 * @property string $country
 * @property string $range
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
        $validFields = $GLOBALS['TL_DCA']['tl_lead_matching']['fields']['estateFormMetaFields']['options'] ?? [];

        // Create default query parts
        $arrCollection = [
            'published' => [
                self::createFragment('published'),
                [1],
            ],
        ];

        // Add marketing type
        if ($objConfig->marketingType)
        {
            $arrCollection[LeadMatchingController::FIELD_MARKETING] = [
                self::createFragment(LeadMatchingController::FIELD_MARKETING),
                [$objConfig->marketingType],
            ];
        }

        if (null !== $formData)
        {
            foreach ($formData as $strName => $varValue)
            {
                // Check if the field must be skipped in filtering
                $blnSkip = (bool) ($arrFieldOptions[$strName]['filter']['skip'] ?? false) || !\in_array($strName, $validFields, true);

                // Fields with callback
                $callback = $arrFieldOptions[$strName]['filter']['callback'] ?? false;

                // Check if the field has a different name
                $strField = ($arrFieldOptions[$strName]['filter']['fieldName'] ?? null) ?? $strName;

                if ($varValue && !$blnSkip)
                {
                    // Use field callback
                    if ($callback)
                    {
                        if ('range' === $callback)
                        {
                            $arrCollection[$strField] = [
                                static::createRangeFragment($strField),
                                [
                                    (float) $varValue,
                                    (float) $varValue,
                                ],
                            ];
                        }
                        elseif (\is_array($callback))
                        {
                            $arrCollection[$strField] = System::importStatic($callback[0])->{$callback[1]}($strName, $varValue, static::$strTable);
                        }
                        elseif (\is_callable($callback))
                        {
                            $arrCollection[$strField] = $callback($strName, $varValue, static::$strTable);
                        }
                    }
                    else
                    {
                        switch ($strName)
                        {
                            case LeadMatchingController::FIELD_MARKETING:
                            case LeadMatchingController::FIELD_OBJECT_TYPES:
                                $arrCollection[$strName] = [
                                    self::createFragment($strField),
                                    [$varValue],
                                ];
                                break;

                            case LeadMatchingController::FIELD_REGIONS:
                                if ('selection' === $objConfig->regionMode)
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
                                elseif ($formData['region_lat'] && $formData['region_lng'])
                                {
                                    $arrCollection[LeadMatchingController::FIELD_REGIONS] = [
                                        '('.$strTable.'.latitude!=0 AND '.$strTable.'.longitude!=0 AND (6371*acos(cos(radians(?))*cos(radians('.$strTable.'.latitude))*cos(radians('.$strTable.'.longitude)-radians(?))+sin(radians(?))*sin(radians('.$strTable.'.latitude)))) <= ?)',
                                        [
                                            $formData['region_lat'],
                                            $formData['region_lng'],
                                            $formData['region_lat'],
                                            $formData['range'] ?: 100,
                                        ],
                                    ];
                                }
                                break;
                        }
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

    public static function createFragment(string $strField, string $operator = '=', ?string $value = null): string
    {
        $t = static::$strTable;
        $v = $value ?? '?';

        return vsprintf("$t.%s%s%s", [$strField, $operator, $v]);
    }

    public static function createRangeFragment(string $strField): string
    {
        return vsprintf('(%s OR %s) AND (%s OR %s)', [
            self::createFragment($strField.'_from', '<='),
            self::createFragment($strField.'_from', '=', '""'),
            self::createFragment($strField.'_to', '>='),
            self::createFragment($strField.'_to', '=', '""'),
        ]);
    }
}
