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

class TlSearchCriteria
{
    /**
     * Add and validate information.
     */
    public function labelCallback(array $row, string $label, DataContainer $dc, array $args): array
    {
        $args[3] = date(Config::get('datimFormat'), (int) $args[3]);

        return $args;
    }
}
