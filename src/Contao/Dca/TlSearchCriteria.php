<?php

namespace ContaoEstateManager\LeadMatchingTool\Contao\Dca;

use Contao\Config;
use Contao\DataContainer;

class TlSearchCriteria
{
    /**
     * Add and validate information
     *
     * @param array                $row
     * @param string               $label
     * @param DataContainer        $dc
     * @param array                $args
     *
     * @return array
     */
    public function labelCallback(array $row, string $label, DataContainer $dc, array $args): array
    {
        $args[3] = date(Config::get('datimFormat'), $args[3]);

        return $args;
    }
}
