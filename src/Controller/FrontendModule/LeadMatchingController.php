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

namespace ContaoEstateManager\LeadMatchingTool\Controller\FrontendModule;

use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\ServiceAnnotation\FrontendModule;
use Contao\Input;
use Contao\Model;
use Contao\Model\Collection;
use Contao\ModuleModel;
use Contao\StringUtil;
use Contao\Template;
use ContaoEstateManager\LeadMatchingTool\Model\LeadMatchingModel;
use ContaoEstateManager\LeadMatchingTool\Model\SearchCriteriaModel;
use ContaoEstateManager\ObjectTypeEntity\ObjectTypeModel;
use ContaoEstateManager\RegionEntity\RegionConnectionModel;
use ContaoEstateManager\RegionEntity\RegionModel;
use Haste\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @FrontendModule(category="estatemanager")
 */
class LeadMatchingController extends AbstractFrontendModuleController
{
    /**
     * Default type.
     */
    public const TYPE = 'system';

    /**
     * Dynamic estate form fields.
     */
    public const FIELD_MARKETING = 'marketingType';
    public const FIELD_OBJECT_TYPES = 'objectTypes';
    public const FIELD_REGIONS = 'regions';

    /**
     * Configuration model.
     */
    protected LeadMatchingModel $config;

    /**
     * Real estate form.
     */
    protected Form $objFormEstate;

    /**
     * Contact form.
     */
    protected Form $objFormContact;

    /**
     * Holds the current filter data
     */
    protected array $filterData;

    /**
     * Defines whether the contact form should be displayed.
     */
    protected bool $blnContactForm = false;

    /**
     * Frontend module.
     */
    protected function getResponse(Template $template, ModuleModel $model, Request $request): ?Response
    {
        // Get lead matching configuration
        $this->config = LeadMatchingModel::findByIdOrAlias($model->lmtConfig);

        if (null === $this->config)
        {
            return null;
        }

        // Pass configuration data to the template
        $template->setData($this->config->row());

        // Generate forms
        $this->objFormEstate = new Form('estate', 'POST', fn ($objForm) => $this->isFormSubmitted($objForm));
        $this->objFormContact = new Form('contact', 'POST', fn ($objForm) => $this->isFormSubmitted($objForm));

        $this->blnContactForm = (bool) $this->config->addContactForm && ((bool) $this->config->forceContact || ($this->objFormEstate->isValid() && $this->objFormEstate->isSubmitted()));

        // Build estate form
        if ((bool) $this->config->addEstateForm)
        {
            $template->formEstate = $this->buildEstateForm();
        }

        // Build contact form
        if ($this->blnContactForm)
        {
            $template->formContact = $this->buildContactForm();
        }

        $template->count = $this->count();
        $this->find();
        $template->list = '';

        return $template->getResponse();
    }

    /**
     * Generate estate form and return it as string.
     */
    protected function buildEstateForm(): string
    {
        // Create form fields
        $arrFields = StringUtil::deserialize($this->config->estateFormMetaFields, true);
        $arrFieldOptions = $GLOBALS['TL_DCA']['tl_lead_matching']['fields']['estateFormMetaFields']['fieldOptions'] ?? [];

        // Create fields
        foreach ($arrFields as $fieldName)
        {
            // Skip marketing field if marketing value set by config
            if ($this->config->marketingType && self::FIELD_MARKETING === $fieldName)
            {
                continue;
            }

            // Field label
            $strLabel = $GLOBALS['TL_LANG']['tl_lead_matching_meta'][$fieldName] ?? '';

            // Field options
            $fieldDefaults = [
                'label' => $strLabel,
                'inputType' => 'text',
                'eval' => [
                    'mandatory' => false,
                ],
            ];

            $fieldOptions = $arrFieldOptions[$fieldName] ?? [];

            // Add options / values
            switch ($fieldName)
            {
                case self::FIELD_MARKETING:
                    $fieldOptions['options'] = StringUtil::deserialize($this->config->marketingTypes);
                    $fieldOptions['reference'] = $GLOBALS['TL_LANG']['tl_lead_matching_meta'];
                    break;

                case self::FIELD_OBJECT_TYPES:
                    $relatedOptionIds = StringUtil::deserialize($this->config->objectTypes);
                    $arrOptions = [];

                    if (null !== $relatedOptionIds)
                    {
                        $objTypes = ObjectTypeModel::findMultipleByIds($relatedOptionIds);

                        if (null !== $objTypes)
                        {
                            foreach ($objTypes as $objType)
                            {
                                $arrOptions[$objType->id] = $objType->title;
                            }
                        }
                    }

                    $fieldOptions['options'] = $arrOptions;
                    break;

                case self::FIELD_REGIONS:
                    // Skip if proximity search is active
                    if (!$this->config->preciseRegionSearch)
                    {
                        break;
                    }

                    $relatedOptionIds = StringUtil::deserialize($this->config->regions);
                    $arrOptions = [];

                    if (null !== $relatedOptionIds)
                    {
                        $objRegions = RegionModel::findMultipleByIds($relatedOptionIds);

                        if (null !== $objRegions)
                        {
                            foreach ($objRegions as $objRegion)
                            {
                                $arrOptions[$objRegion->id] = $objRegion->title;
                            }
                        }
                    }

                    $fieldOptions['inputType'] = 'select';
                    $fieldOptions['options'] = $arrOptions;
                    break;
            }

            // Add field to form
            $this->objFormEstate->addFormField($fieldName, array_merge(
                $fieldDefaults,
                $fieldOptions
            ));
        }

        // Add submit button
        $this->objFormEstate->addSubmitFormField('submit', $GLOBALS['TL_LANG']['tl_lead_matching_meta']['submitEstate']);

        // Validate
        if ($this->objFormEstate->validate())
        {
            $this->saveFilterData();

            if ($this->blnContactForm)
            {
                $this->transferFields();
            }
        }

        return $this->objFormEstate->generate();
    }

    /**
     * Generate contact form and return it as string.
     */
    protected function buildContactForm(): string
    {
        // Create form fields by generator id
        $this->objFormContact->addFieldsFromFormGenerator($this->config->contactForm);

        // Validate form
        if ($this->objFormContact->validate())
        {
            // ToDo: Validate dynamic fields
            // ToDo: Send form?
        }

        return $this->objFormContact->generate();
    }

    /**
     * Adds estate form fields as hidden fields to the contact form.
     */
    protected function transferFields(): void
    {
        $this->objFormEstate->fetchAll(function ($strName, $objWidget): void {
            $strValue = Input::postRaw($strName);

            if ($strValue)
            {
                $this->objFormContact->addFormField($strName, [
                    'inputType' => 'hidden',
                    'name' => $strName,
                    'value' => $strValue,
                ]);
            }
        });
    }

    protected function saveFilterData(): void
    {
        $this->filterData = [];

        $this->objFormEstate->fetchAll(function ($strName, $objWidget): void
        {
            $this->filterData[ $strName ] = $objWidget->value;
        });
    }

    /**
     * Count list items.
     */
    protected function count(): int
    {
        $strTable = SearchCriteriaModel::getTable();
        $strSelect = 'SELECT COUNT('.$strTable.'.id) as numberOfItems FROM '.$strTable;

        // Create filter query
        [$query, $parameter] = SearchCriteriaModel::createFilterQuery($strSelect, $this->config, $this->filterData);

        // Execute filter query
        $objSearchCriteria = SearchCriteriaModel::execute($query, $parameter);

        // Return number of items
        return (int) ($objSearchCriteria->numberOfItems ?? 0);
    }

    /**
     * Find list items.
     */
    protected function find(?int $limit = null, ?int $offset = null): ?Collection
    {
        $strTable = SearchCriteriaModel::getTable();
        $strSelect = 'SELECT ' . $strTable . '.* FROM ' . $strTable;

        // Create filter query
        [$query, $parameter] = SearchCriteriaModel::createFilterQuery($strSelect, $this->config, $this->filterData);

        // Set options
        $arrOptions = [];

        if ($limit)
            $arrOptions['limit'] = $limit;

        if ($offset)
            $arrOptions['offset'] = $offset;

        // Execute filter query and return collection
        return SearchCriteriaModel::execute($query, $parameter, true, $arrOptions);
    }

    /**
     * Check if a form is submitted.
     *
     * @param $objForm
     *
     * @return bool
     */
    private function isFormSubmitted($objForm): bool
    {
        return Input::post('FORM_SUBMIT') === $objForm->getFormId();
    }
}
