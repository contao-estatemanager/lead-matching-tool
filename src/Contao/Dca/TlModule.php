<?php
declare(strict_types=1);

namespace ContaoEstateManager\LeadMatchingTool\Contao\Dca;

use ContaoEstateManager\LeadMatchingTool\Model\LeadMatchingModel;

class TlModule
{
    /**
     * Returns an array of lead matching configurations
     *
     * @return array
     */
    public function getLeadMatchingConfiguration(): array
    {
        $arrOptions = array();

        $objConfigs = LeadMatchingModel::findAll();

        if($objConfigs)
        {
            while($objConfigs->next())
            {
                $arrOptions[ $objConfigs->id ] = $objConfigs->title;
            }
        }

        return $arrOptions;
    }
}
