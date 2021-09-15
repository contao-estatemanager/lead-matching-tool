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

use Contao\Controller;
use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Database;
use Contao\DataContainer;
use Contao\Image;
use Contao\StringUtil;
use ContaoEstateManager\LeadMatchingTool\Controller\FrontendModule\LeadMatchingController;
use ContaoEstateManager\LeadMatchingTool\Model\LeadMatchingModel;
use ContaoEstateManager\ObjectTypeEntity\ObjectTypeModel;
use ContaoEstateManager\RegionEntity\RegionModel;

class TlLeadMatching
{
    /**
     * @var ContaoFramework
     */
    private $framework;

    /**
     * @var Database
     */
    private $database;

    /**
     * TlLeadMatching constructor.
     */
    public function __construct(ContaoFramework $framework)
    {
        $this->framework = $framework;

        /** @var Database $dbAdapter */
        $dbAdapter = $this->framework->getAdapter(Database::class);

        $this->database = $dbAdapter->getInstance();
    }

    /**
     * Save key value set of marketing types.
     *
     * @return string
     */
    public function saveMarketingTypes($varValue, DataContainer $dc): ?string
    {
        if (!$varValue || LeadMatchingController::TYPE !== $dc->activeRecord->type)
        {
            return $varValue;
        }

        $arrChosenTypes = StringUtil::deserialize($varValue);

        if (null === $arrChosenTypes)
        {
            return $varValue;
        }

        $arrOptions = [];

        foreach ($arrChosenTypes as $type)
        {
            $arrOptions[$type] = &$GLOBALS['TL_LANG']['tl_lead_matching_meta'][$type];
        }

        $this->database->prepare('UPDATE tl_lead_matching SET marketingTypesData=? WHERE id=?')
                       ->execute(serialize($arrOptions), $dc->id)
        ;

        return $varValue;
    }

    /**
     * Displays the field only if no marketingType selection has been made.
     */
    public function showMarketingTypes(DataContainer $dc = null): void
    {
        if (null !== $dc)
        {
            $objConfig = LeadMatchingModel::findByPk($dc->id);

            if (null !== $objConfig && $objConfig->marketingType)
            {
                // Remove marketingTypes from palette
                PaletteManipulator::create()
                    ->removeField('marketingTypes', 'data_legend')
                    ->applyToPalette('system', $dc->table)
                ;
            }
        }
    }

    /**
     * Displays the field only if regions mode "selection".
     */
    public function showRegions(DataContainer $dc = null): void
    {
        if (null !== $dc)
        {
            $objConfig = LeadMatchingModel::findByPk($dc->id);

            if (null !== $objConfig && 'selection' !== $objConfig->regionMode)
            {
                PaletteManipulator::create()
                    ->removeField('regions', 'data_legend')
                    ->applyToPalette('system', $dc->table)
                ;
            }
        }
    }

    /**
     * Save key value set of object types.
     *
     * @return string
     */
    public function saveObjectTypes($varValue, DataContainer $dc): ?string
    {
        if (!$varValue || LeadMatchingController::TYPE !== $dc->activeRecord->type)
        {
            return $varValue;
        }

        $arrChosenTypes = StringUtil::deserialize($varValue);

        if (null === $arrChosenTypes)
        {
            return $varValue;
        }

        $arrColumns = ["id IN('".implode("','", $arrChosenTypes)."')"];

        $objObjectTypes = ObjectTypeModel::findBy($arrColumns, []);

        if (null !== $objObjectTypes)
        {
            $arrOptions = [];

            while ($objObjectTypes->next())
            {
                $arrOptions[$objObjectTypes->id] = $objObjectTypes->title;
            }

            $this->database->prepare('UPDATE tl_lead_matching SET objectTypesData=? WHERE id=?')
                           ->execute(serialize($arrOptions), $dc->id)
            ;
        }

        return $varValue;
    }

    /**
     * Save key value set of regions.
     *
     * @return string
     */
    public function saveRegions($varValue, DataContainer $dc): ?string
    {
        if (!$varValue || LeadMatchingController::TYPE !== $dc->activeRecord->type)
        {
            return $varValue;
        }

        $arrChosenTypes = StringUtil::deserialize($varValue);

        if (null === $arrChosenTypes)
        {
            return $varValue;
        }

        $arrColumns = ["id IN('".implode("','", $arrChosenTypes)."')"];

        $objRegions = RegionModel::findBy($arrColumns, []);

        if (null !== $objRegions)
        {
            $arrOptions = [];

            while ($objRegions->next())
            {
                $arrOptions[$objRegions->id] = $objRegions->title;
            }

            $this->database->prepare('UPDATE tl_lead_matching SET regionsData=? WHERE id=?')
                           ->execute(serialize($arrOptions), $dc->id)
            ;
        }

        return $varValue;
    }

    /**
     * Returns an array of search criteria fields.
     */
    public function getListMetaFields(): array
    {
        $arrOptions = [];
        $arrSkip = ['published'];

        Controller::loadDataContainer('tl_search_criteria');

        foreach ($GLOBALS['TL_DCA']['tl_search_criteria']['fields'] as $key => $opt)
        {
            if (!\in_array($key, $arrSkip, true))
            {
                $arrOptions[] = $key;
            }
        }

        return $arrOptions;
    }

    /**
     * Return the edit form wizard.
     */
    public function editForm(DataContainer $dc): string
    {
        if ($dc->value < 1)
        {
            return '';
        }

        $title = sprintf($GLOBALS['TL_LANG']['tl_lead_matching']['editalias'], $dc->value);

        return ' <a href="contao/main.php?do=form&amp;table=tl_form_field&amp;id='.$dc->value.'&amp;popup=1&amp;nb=1&amp;rt='.REQUEST_TOKEN.'" title="'.StringUtil::specialchars($title).'" onclick="Backend.openModalIframe({\'title\':\''.StringUtil::specialchars(str_replace("'", "\\'", $title)).'\',\'url\':this.href});return false">'.Image::getHtml('alias.svg', $title).'</a>';
    }

    /**
     * Get all forms and return them as array.
     */
    public function getForms(): array
    {
        $arrForms = [];
        $objForms = $this->database->execute('SELECT id, title FROM tl_form ORDER BY title');

        while ($objForms->next())
        {
            $arrForms[$objForms->id] = $objForms->title.' (ID '.$objForms->id.')';
        }

        return $arrForms;
    }
}
