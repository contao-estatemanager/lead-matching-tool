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
use Contao\ContentElement;
use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\ServiceAnnotation\FrontendModule;
use Contao\Form;
use Contao\FormModel;
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
use Haste\Form\Form as HasteForm;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;

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
     * Session bag key.
     */
    public const SESSION_BAG_KEY = 'lead_matching';

    /**
     * Configuration model.
     */
    protected LeadMatchingModel $config;

    /**
     * Filter form.
     */
    protected HasteForm $objFormFilter;

    /**
     * Holds the current filter data.
     */
    protected ?array $filterData = null;

    /**
     * Session bag.
     */
    private AttributeBagInterface $objSessionBag;

    /**
     * Frontend module.
     */
    protected function getResponse(Template $template, ModuleModel $model, Request $request): ?Response
    {
        // Get configuration
        $this->config = LeadMatchingModel::findByIdOrAlias($model->lmtConfig);

        if (null === $this->config)
        {
            return null;
        }

        /* @var AttributeBagInterface $objSessionBag */
        $this->objSessionBag = System::getContainer()->get('session')->getBag('contao_frontend');

        // Generate filter form
        $this->objFormFilter = new HasteForm('estate', 'POST', fn ($objForm) => $this->isFormSubmitted($objForm));

        // Check whether sections may be output
        $template->showFilterForm = $this->config->addFilterForm;
        $template->showContactForm = (bool) $this->config->addContactForm && ((bool) $this->config->forceContact || ($this->objFormFilter->isValid() && $this->objFormFilter->isSubmitted()));
        $template->showList = (bool) $this->config->forceList || !$this->config->addFilterForm || ($this->objFormFilter->isValid() && $this->objFormFilter->isSubmitted());

        if ((bool) $this->config->addFilterForm)
        {
            $template->formFilter = $this->generateFilterForm();
        }

        if ((bool) $this->config->addContactForm)
        {
            $template->formContact = $this->generateContactForm();
        }

        $template->count = $this->count();
        $template->list = '';
        $template->pagination = '';

        if ($template->showList)
        {
            $this->generateList($template);
        }

        // Texts from module
        $template->filterHeadline = $this->config->txtEstateHeadline;
        $template->filterDescription = $this->config->txtEstateDescription;
        $template->listHeadline = $this->config->txtListHeadline;
        $template->listDescription = $this->config->txtListDescription;
        $template->contactHeadline = $this->config->txtContactHeadline;
        $template->contactDescription = $this->config->txtContactDescription;

        // Texts from translations
        $template->labelEmptyList = $GLOBALS['TL_LANG']['tl_lead_matching_meta']['emptyList'];
        $template->labelNumberOfItems = $GLOBALS['TL_LANG']['tl_lead_matching_meta']['numberOfItems'];

        // Config
        $template->proximityEngine = $this->config->regionMode;
        $template->isProximitySearch = 'selection' === $this->config->regionMode ? 0 : 1;
        $template->isLiveCounting = $this->config->countResults ? 1 : 0;
        $template->googleApiKey = $this->config->googleApiKey;
        $template->config = $this->config;

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
                $options = $GLOBALS['TL_DCA']['tl_search_criteria']['fields'][$field]['leadMatching'] ?? null;
                $format = (array) ($options['format'] ?? []);

                // Trigger the format callback
                foreach ($format as $callback)
                {
                    if (\is_string($callback))
                    {
                        $varValue = $this->formatValue($callback, $varValue);
                    }
                    elseif (\is_array($callback))
                    {
                        $varValue = System::importStatic($callback[0])->{$callback[1]}($varValue, $this);
                    }
                    elseif (\is_callable($callback))
                    {
                        $varValue = $callback($varValue, $this);
                    }
                }

                $arrFields[$field] = [
                    'class' => $field,
                    'label' => $varLabel,
                    'value' => $varValue,
                ];

                $groupOptions = $options['group'] ?? null;

                if ($groupOptions)
                {
                    $groupName = $groupOptions['name'];
                    $groupAppend = $groupOptions['append'] ?? '';
                    $groupSeparator = $groupOptions['separator'] ?? ' - ';

                    if (!\array_key_exists($groupName, $arrGroups))
                    {
                        $arrGroups[$groupName] = [
                            'label' => $GLOBALS['TL_LANG']['tl_lead_matching_meta'][$groupName] ?? $varLabel,
                            'values' => [],
                            'fields' => [],
                            'separator' => $groupSeparator,
                            'append' => $groupAppend,
                        ];
                    }

                    $arrGroups[$groupName]['values'][] = $varValue;
                    $arrGroups[$groupName]['fields'][] = $field;
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
            $groupedFields[$groupName] = [
                'class' => implode(' ', $group['fields']).' '.$groupName,
                'label' => $group['label'],
                'value' => implode($group['separator'], $arrValues).$group['append'],
            ];

            // Delete fields
            foreach ($group['fields'] as $field)
            {
                unset($arrFields[$field]);
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
    protected function generateFilterForm(): string
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
            $additionalFields = $arrFieldOptions[$fieldName]['additionalFields'] ?? null;

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
                    if ('selection' !== $this->config->regionMode)
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
            $this->objFormFilter->addFormField($fieldName, array_merge(
                $fieldDefaults,
                $fieldOptions
            ));

            // Add additional fields
            if ($additionalFields)
            {
                foreach ($additionalFields as $aFieldName => $aFieldOptions)
                {
                    $this->objFormFilter->addFormField($aFieldName, array_merge(
                        $fieldDefaults,
                        $aFieldOptions
                    ));
                }
            }
        }

        // Add submit button
        $this->objFormFilter->addSubmitFormField('submit', $GLOBALS['TL_LANG']['tl_lead_matching_meta']['submitEstate']);

        // Validate
        if ($this->objFormFilter->validate())
        {
            $this->saveFilterData();
        }

        return $this->objFormFilter->generate();
    }

    /**
     * Create and return the Form from config.
     *
     * @return Form|null
     */
    protected function generateContactForm(): ?string
    {
        $objRow = FormModel::findByIdOrAlias($this->config->contactForm);

        if (null === $objRow)
        {
            return null;
        }

        $strClass = ContentElement::findClass('form');

        if (!class_exists($strClass))
        {
            return null;
        }

        $objRow->typePrefix = 'ce_';
        $objRow->form = $objRow->id;

        /** @var Form $objElement */
        $objForm = new $strClass($objRow);

        return $objForm->generate();
    }

    /**
     * Save and holds the current filter data.
     */
    protected function saveFilterData(): void
    {
        $this->filterData = [];

        $this->objFormFilter->fetchAll(function ($strName, $objWidget): void {
            if ('submit' === $strName)
            {
                return;
            }

            $this->filterData[$strName] = [
                'label' => $GLOBALS['TL_LANG']['tl_lead_matching_meta'][$strName] ?? $strName,
                'raw' => $objWidget->value,
                'value' => $this->formatValue($strName, $objWidget->value),
            ];
        });

        $bag = $this->objSessionBag->get(self::SESSION_BAG_KEY);
        $bag[$this->config->contactForm] = $this->filterData;
        $this->objSessionBag->set(self::SESSION_BAG_KEY, $bag);
    }

    /**
     * Count list items.
     */
    protected function count(): int
    {
        $strTable = SearchCriteriaModel::getTable();
        $strSelect = 'SELECT COUNT('.$strTable.'.id) as numberOfItems FROM '.$strTable;
        $arrData = [];

        // Prepare form data
        if ($this->filterData)
        {
            $arrData = array_map(function ($a) {
                return $a['raw'];
            }, $this->filterData);
        }

        // Create filter query
        [$query, $parameter] = SearchCriteriaModel::createFilterQuery($strSelect, $this->config, $arrData);

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
        $arrData = [];

        // Prepare form data
        if ($this->filterData)
        {
            $arrData = array_map(function ($a) {
                return $a['raw'];
            }, $this->filterData);
        }

        // Create filter query
        [$query, $parameter] = SearchCriteriaModel::createFilterQuery($strSelect, $this->config, $arrData);

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
     * Format values.
     *
     * @param $format
     * @param $varValue
     */
    protected function formatValue($format, $varValue): string
    {
        switch ($format)
        {
            case self::FIELD_OBJECT_TYPES:
                $objObjectType = ObjectTypeModel::findById($varValue);

                if (null !== $objObjectType)
                {
                    $varValue = $objObjectType->title;
                }
                break;

            case self::FIELD_REGIONS:
                // Skip if proximity search is active
                if ('selection' !== $this->config->regionMode)
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

                $varValue = implode(', ', $arrOptions);
                break;

            default:
                $varValue = $GLOBALS['TL_LANG']['tl_lead_matching_meta'][$varValue] ?? $varValue;
        }

        return $varValue;
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
