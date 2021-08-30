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

use ContaoEstateManager\LeadMatchingTool\Model\LeadMatchingModel;

class TlModule
{
    /**
     * Returns an array of lead matching configurations.
     */
    public function getLeadMatchingConfiguration(): array
    {
        $arrOptions = [];
        $objConfigs = LeadMatchingModel::findAll();

        if ($objConfigs)
        {
            while ($objConfigs->next())
            {
                $arrOptions[$objConfigs->id] = $objConfigs->title;
            }
        }

        return $arrOptions;
    }
}
