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

namespace ContaoEstateManager\LeadMatchingTool\Contao\Dca;

use Contao\Config;
use Contao\DataContainer;
use ContaoEstateManager\ObjectTypeEntity\ObjectTypeModel;

class TlSearchCriteria
{
    /**
     * Add and validate information.
     */
    public function labelCallback(array $row, string $label, DataContainer $dc, array $args): array
    {
        $objObjectType = ObjectTypeModel::findById($args[3]);
        $strObjectType = '-';

        if (null !== $objObjectType)
        {
            $strObjectType = $objObjectType->title;
        }

        $args[3] = $strObjectType;
        $args[4] = date(Config::get('datimFormat'), (int) $args[4]);

        return $args;
    }
}
