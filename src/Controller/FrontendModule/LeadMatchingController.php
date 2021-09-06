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

use Contao\Config;
use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\ServiceAnnotation\FrontendModule;
use Contao\FrontendTemplate;
use Contao\Input;
use Contao\Model\Collection;
use Contao\ModuleModel;
use Contao\Pagination;
use Contao\StringUtil;
use Contao\System;
use Contao\Template;
use ContaoEstateManager\LeadMatchingTool\Model\LeadMatchingModel;
use ContaoEstateManager\LeadMatchingTool\Model\SearchCriteriaModel;
use ContaoEstateManager\ObjectTypeEntity\ObjectTypeModel;
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
     * Holds the current filter data.
     */
    protected ?array $filterData = null;

    /**
     * Defines whether the contact form should be displayed.
     */
    protected bool $blnContactForm = false;

    /**
     * Defines whether the list should be displayed.
     */
    protected bool $blnList = false;

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
        $this->blnList = (bool) $this->config->forceList || ($this->objFormEstate->isValid() && $this->objFormEstate->isSubmitted());

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

        // Generate search criteria list
        $template->count = $this->count();
        $template->list = '';
        $template->pagination = '';

        if ($this->blnList)
        {
            $this->generateList($template);
        }

        $template->empty = $GLOBALS['TL_LANG']['tl_lead_matching_meta']['emptyList'];

        return $template->getResponse();
    }

    /**
     * Generate list.
     */
    protected function generateList(Template &$template): void
    {
        $intTotal = $template->count;
        $limit = null;
        $offset = 0;

        // Maximum number of items
        if ($this->config->numberOfItems > 0)
        {
            $limit = $this->config->numberOfItems;
        }

        if ($intTotal < 1)
        {
            return;
        }

        $total = $intTotal - $offset;

        // Split the results
        if ($this->config->perPage > 0 && (!isset($limit) || $this->config->numberOfItems > $this->config->perPage))
        {
            // Adjust the overall limit
            if (isset($limit))
            {
                $total = min($limit, $total);
            }

            // Get the current page
            $id = 'page_lm'.$this->config->id;
            $page = Input::get($id) ?? 1;

            // Do not index or cache the page if the page number is outside the range
            if ($page < 1 || $page > max(ceil($total / $this->config->perPage), 1))
            {
                throw new PageNotFoundException('Page not found: '.\Environment::get('uri'));
            }

            // Set limit and offset
            $limit = $this->config->perPage;
            $offset += (max($page, 1) - 1) * $this->config->perPage;

            // Overall limit
            if ((int) $offset + (int) $limit > $total)
            {
                $limit = $total - $offset;
            }

            // Add the pagination menu
            $objPagination = new Pagination($total, $this->config->perPage, Config::get('maxPaginationLinks'), $id);
            $template->pagination = $objPagination->generate("\n  ");
        }

        $objItems = $this->fetch(((int) $limit ?: 0), (int) $offset);
        $template->list = $this->parseItems($objItems);
    }

    /**
     * Parse items.
     *
     * @param $objItems
     */
    protected function parseItems($objItems): string
    {
        $limit = $objItems->count();

        if ($limit < 1)
        {
            return '';
        }

        $count = 0;
        $arrItems = [];

        foreach ($objItems as $objItem)
        {
            $arrItems[] = $this->parseItem($objItem, ((1 === ++$count) ? ' first' : '').(($count === $limit) ? ' last' : '').((($count % 2) === 0) ? ' odd' : ' even'), $count);
        }

        return implode('', $arrItems);
    }

    /**
     * Parse items.
     *
     * @param $objItem
     */
    protected function parseItem($objItem, string $strClass = '', int $intCount = 0): string
    {
        $objTemplate = new FrontendTemplate($this->config->listItemTemplate);
        $objTemplate->setData($objItem->row());
        $objTemplate->class = $strClass;

        $arrFields = [];
        $arrGroups = [];
        $listFields = StringUtil::deserialize($this->config->listMetaFields);

        if (null !== $listFields)
        {
            foreach ($listFields as $field)
            {
                $varLabel = $GLOBALS['TL_LANG']['tl_lead_matching_meta'][$field];
                $varValue = $objItem->{$field};
                $options  = $GLOBALS['TL_DCA']['tl_search_criteria']['fields'][$field]['leadMatching'] ?? null;
                $format   = (array) ($options['format'] ?? []);

                // Trigger the format callback
                foreach ($format as $callback)
                {
                    if(is_string($callback))
                    {
                        $varValue = $this->formatValue($callback, $varValue);
                    }
                    elseif (is_array($callback))
                    {
                        $varValue = System::importStatic($callback[0])->{$callback[1]}($varValue, $this);
                    }
                    elseif (is_callable($callback))
                    {
                        $varValue = $callback($varValue, $this);
                    }
                }

                $arrFields[ $field ] = [
                    'class' => $field,
                    'label' => $varLabel,
                    'value' => $varValue,
                ];

                $groupOptions = $options['group'] ?? null;

                if($groupOptions)
                {
                    $groupName = $groupOptions['name'];
                    $groupAppend = $groupOptions['append'] ?? '';
                    $groupSeparator = $groupOptions['separator'] ?? ' - ';

                    if(!array_key_exists($groupName, $arrGroups))
                    {
                        $arrGroups[ $groupName ] = [
                            'label'  => $GLOBALS['TL_LANG']['tl_lead_matching_meta'][$groupName] ?? $varLabel,
                            'values' => [],
                            'fields' => [],
                            'separator' => $groupSeparator,
                            'append' => $groupAppend,
                        ];
                    }

                    $arrGroups[ $groupName ]['values'][] = $varValue;
                    $arrGroups[ $groupName ]['fields'][] = $field;
                }
            }
        }

        // Group fields
        $groupedFields = [];

        foreach ($arrGroups as $groupName => $group)
        {
            // Remove empty fields
            $arrValues = array_filter($group['values']);

            // Combine fields
            $groupedFields[ $groupName ] = [
                'class' => implode(' ', $group['fields']) . ' ' . $groupName,
                'label' => $group['label'],
                'value' => implode($group['separator'], $arrValues) . $group['append']
            ];

            // Delete fields
            foreach ($group['fields'] as $field)
            {
                unset($arrFields[ $field ]);
            }

            // Add combined fields
            $arrFields = array_merge(
                $arrFields,
                $groupedFields
            );
        }

        $objTemplate->fields = $arrFields;

        return $objTemplate->parse();
    }

    /**
     * Generate estate form and return it as string.
     */
    protected function buildEstateForm(): string
    {
        // Create form fields
        $arrFields = StringUtil::deserialize($this->config->estateFormMetaFields, true);
        $arrFieldOptions = $GLOBALS['TL_DCA']['tl_lead_matching']['fields']['estateFormMetaFields']['leadMatching'] ?? [];

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

            $fieldOptions = $arrFieldOptions[$fieldName]['fieldOptions'] ?? [];

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

    /**
     * Save and holds the current filter data.
     */
    protected function saveFilterData(): void
    {
        $this->filterData = [];

        $this->objFormEstate->fetchAll(function ($strName, $objWidget): void
        {
            $this->filterData[$strName] = $objWidget->value;
        });
    }

    /**
     * Format values
     *
     * @param $format
     * @param $varValue
     *
     * @return string
     */
    public function formatValue($format, $varValue): string
    {
        switch($format)
        {
            case self::FIELD_OBJECT_TYPES:
                $objObjectType = ObjectTypeModel::findById($varValue);

                if(null !== $objObjectType)
                {
                    $varValue = $objObjectType->title;
                }
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

                $varValue = implode(", ", $arrOptions);
                break;

            case 'translate':
                $varValue = $GLOBALS['TL_LANG']['tl_lead_matching_meta'][$varValue] ?? $varValue;
                break;
        }

        return $varValue;
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
    protected function fetch(?int $limit = null, ?int $offset = null): ?Collection
    {
        $strTable = SearchCriteriaModel::getTable();
        $strSelect = 'SELECT '.$strTable.'.* FROM '.$strTable;

        // Create filter query
        [$query, $parameter] = SearchCriteriaModel::createFilterQuery($strSelect, $this->config, $this->filterData);

        // Set options
        $arrOptions = [];

        if ($limit)
        {
            $arrOptions['limit'] = $limit;
        }

        if ($offset)
        {
            $arrOptions['offset'] = $offset;
        }

        // Execute filter query and return collection
        return SearchCriteriaModel::execute($query, $parameter, true, $arrOptions);
    }

    /**
     * Check if a form is submitted.
     *
     * @param $objForm
     */
    private function isFormSubmitted($objForm): bool
    {
        return Input::post('FORM_SUBMIT') === $objForm->getFormId();
    }
}
