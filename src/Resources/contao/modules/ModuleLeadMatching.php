<?php
/**
 * This file is part of Contao EstateManager.
 *
 * @link      https://www.contao-estatemanager.com/
 * @source    https://github.com/contao-estatemanager/lead-matching-tool
 * @copyright Copyright (c) 2019  Oveleon GbR (https://www.oveleon.de)
 * @license   https://www.contao-estatemanager.com/lizenzbedingungen.html
 */

namespace ContaoEstateManager\LeadMatchingTool;

use Contao\CoreBundle\Exception\PageNotFoundException;
use ContaoEstateManager\ObjectTypeEntity\ObjectTypeModel;
use ContaoEstateManager\RegionEntity\RegionModel;
use Patchwork\Utf8;

/**
 * Class for lead matching tool module.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
class ModuleLeadMatching extends \Module
{
    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_lead_matching';

    /**
     * Table
     * @var string
     */
    protected $strTable = 'tl_searchcriteria';

    /**
     * Configuration
     * @var LeadMatchingModel
     */
    private $config = null;

    /**
     * Generate the module
     *
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE')
        {
            $objTemplate = new \BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### ' . Utf8::strtoupper($GLOBALS['TL_LANG']['FMD']['leadMatching'][0]) . ' ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        \System::loadLanguageFile('tl_lead_matching_meta');

        return parent::generate();
    }

    /**
     * Generate the module
     */
    protected function compile()
    {
        if(!$this->lmtConfig)
        {
            return '';
        }

        $this->config = LeadMatchingModel::findById($this->lmtConfig);

        if(!$this->config)
        {
            return '';
        }

        $this->Template->addCunter      = !!$this->config->countResults;
        $this->Template->addEstateForm  = !!$this->config->addEstateForm;

        // estate form
        if($this->Template->addEstateForm)
        {
            $this->Template->estateForm = $this->generateForm('estate');
        }

        $this->Template->addContactForm = (!$this->config->addContactForm || !!$_SESSION['LEAD_MATCHING']['SUBMIT']) || (!!$this->config->addContactForm && !!$this->config->forceContact);
        $this->Template->addList        = (!$this->config->addEstateForm || !!$_SESSION['LEAD_MATCHING']['SUBMIT']) || (!!$this->config->addEstateForm && !!$this->config->forceList);

        // contact form
        if($this->Template->addContactForm)
        {
            $this->Template->contactForm = $this->generateForm('contact');
        }

        $cntTotal = $this->count();

        if($this->Template->addCunter)
        {
            $this->Template->cntTotal = $cntTotal;
        }

        if($this->Template->addList)
        {
            $this->generateList($cntTotal);
        }

        $moduleId = 'lmt_' . $this->id;

        if(empty($this->cssID[0]))
        {
            $this->cssID = array(
                $moduleId,
                $this->cssID[1]
            );
        }

        $this->Template->configId = $this->lmtConfig;
        $this->Template->moduleId = $this->cssID[0];
    }

    /**
     * Generate list
     *
     * @param $intTotal
     */
    protected function generateList($intTotal)
    {
        $limit = null;
        $offset = 0;

        // Maximum number of items
        if ($this->config->numberOfItems > 0)
        {
            $limit = $this->config->numberOfItems;
        }

        $this->Template->items = array();
        $this->Template->empty = $GLOBALS['TL_LANG']['MSC']['emptyList'];

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
            $id = 'page_lm' . $this->id;
            $page = \Input::get($id) ?? 1;

            // Do not index or cache the page if the page number is outside the range
            if ($page < 1 || $page > max(ceil($total/$this->config->perPage), 1))
            {
                throw new PageNotFoundException('Page not found: ' . \Environment::get('uri'));
            }

            // Set limit and offset
            $limit = $this->config->perPage;
            $offset += (max($page, 1) - 1) * $this->config->perPage;

            // Overall limit
            if ($offset + $limit > $total)
            {
                $limit = $total - $offset;
            }

            // Add the pagination menu
            $objPagination = new \Pagination($total, $this->config->perPage, \Config::get('maxPaginationLinks'), $id);
            $this->Template->pagination = $objPagination->generate("\n  ");
        }

        $objItems = $this->fetch(($limit ?: 0), $offset);

        if($this->config->type === 'system')
        {
            if ($objItems !== null)
            {
                $this->Template->items = $this->parseItems($objItems);
            }
        }
        else
        {
            // HOOK: add custom logic
            if (isset($GLOBALS['TL_HOOKS']['parseLeadMatchingItems']) && \is_array($GLOBALS['TL_HOOKS']['parseLeadMatchingItems']))
            {
                foreach ($GLOBALS['TL_HOOKS']['parseLeadMatchingItems'] as $callback)
                {
                    $this->import($callback[0]);
                    $this->Template->items = $this->{$callback[0]}->{$callback[1]}($this->config, $objItems, $this);
                }
            }
        }
    }

    /**
     * Parse items
     *
     * @param $objItems
     *
     * @return array
     */
    public function parseItems($objItems){
        $limit = $objItems->count();

        if ($limit < 1)
        {
            return array();
        }

        $count = 0;
        $arrItems = array();

        while ($objItems->next())
        {
            /** @var SearchcriteriaModel $objItem */
            $objItem = $objItems->current();

            $arrItems[] = $this->parseItem($objItem, ((++$count == 1) ? ' first' : '') . (($count == $limit) ? ' last' : '') . ((($count % 2) == 0) ? ' odd' : ' even'), $count);
        }

        return $arrItems;
    }

    /**
     * Parse items
     *
     * @param $objItem
     * @param string $strClass
     * @param int $intCount
     *
     * @return string
     */
    public function parseItem($objItem, $strClass='', $intCount=0){
        $objTemplate = new \FrontendTemplate($this->config->listItemTemplate);
        $objTemplate->setData($objItem->row());
        $objTemplate->class = $strClass;

        $arrFields = array();
        $listFields = \StringUtil::deserialize($this->config->listMetaFields);

        if($listFields !== null)
        {
            foreach ($listFields as $field)
            {
                $varLabel = $GLOBALS['TL_LANG']['tl_lead_matching_meta'][ $field ];
                $varValue = $objItem->{$field} ?: $GLOBALS['TL_LANG']['tl_lead_matching_meta']['emptyField'];

                switch($field)
                {
                    case 'objectTypes':
                        $objectTypeIds = \StringUtil::deserialize($varValue);
                        $objectTypes   = $this->getObjectTypeTitlesByIds($objectTypeIds);

                        if($objectTypes !== null)
                        {
                            $varValue = implode(", ", $objectTypes);
                        }
                        else
                        {
                            $varValue = $GLOBALS['TL_LANG']['tl_lead_matching_meta']['emptyField'];
                        }

                        break;

                    case 'regions':
                        $regionIds = \StringUtil::deserialize($varValue);
                        $regions   = $this->getRegionTitlesByIds($regionIds);

                        if($regions !== null)
                        {
                            $varValue = implode(", ", $regions);
                        }
                        else
                        {
                            $varValue = $GLOBALS['TL_LANG']['tl_lead_matching_meta']['emptyField'];
                        }
                        break;

                    case 'price_from':
                    case 'price_to':
                        $varValue = number_format($varValue, 0 , ',', '.') . ' €';
                        break;

                    case 'area_from':
                    case 'area_to':
                        $varValue = number_format($varValue, 2 , ',', '.') . ' m²';
                        break;

                    case 'tstamp':
                        $varValue = date(\Config::get('dateFormat'), $varValue);
                        break;

                    case 'marketing':
                        $varValue = $GLOBALS['TL_LANG']['tl_lead_matching_meta'][ $varValue ];
                        break;
                }

                $arrFields[] = array(
                    'class' => $field,
                    'label' => $varLabel,
                    'value' => $varValue
                );
            }
        }

        $objTemplate->fields = $arrFields;

        return $objTemplate->parse();
    }

    /**
     * Prepare and parse forms
     *
     * @param $strMode
     *
     * @return string
     */
    public function generateForm($strMode)
    {
        $doNotSubmit = false;
        $arrSubmitted = array();

        switch ($strMode)
        {
            case 'contact':
                $strFormId   = 'form_contact_' . $this->id;
                $strSubmit   = $GLOBALS['TL_LANG']['tl_lead_matching_meta']['submitContact'];

                $strTemplate  = $this->config->contactFormTemplate;
                $arrEditable  = \StringUtil::deserialize($this->config->contactFormMetaFields, true);
                $arrMandatory = \StringUtil::deserialize($this->config->contactFormMetaFieldsMandatory, true);
                break;

            case 'estate':
                $strFormId   = 'form_estate_' . $this->id;
                $strSubmit   = $GLOBALS['TL_LANG']['tl_lead_matching_meta']['submitEstate'];

                $strTemplate  = $this->config->estateFormTemplate;
                $arrEditable  = \StringUtil::deserialize($this->config->estateFormMetaFields, true);
                $arrMandatory = \StringUtil::deserialize($this->config->estateFormMetaFieldsMandatory, true);
                break;

            default:
                return '';
        }

        $objTemplate = new \FrontendTemplate($strTemplate);

        $objTemplate->action = \Environment::get('requestUri');
        $objTemplate->formId = $strFormId;
        $objTemplate->submit = $strSubmit;

        foreach ($arrEditable as $field)
        {
            $arrData = array(
                'label' => $GLOBALS['TL_LANG']['tl_lead_matching_meta'][ $field ],
                'eval'  => [
                    'mandatory' => in_array($field, $arrMandatory)
                ]
            );

            switch($field)
            {
                case 'marketingType':
                    if($this->config->marketingType)
                    {
                        // skip if the marketing type was set in the config
                        continue;
                    }

                    $arrData['inputType'] = 'select';
                    $arrData['options'] = array();

                    if($this->config->addBlankMarketingType)
                    {
                        $arrData['options'] = array('' => '-');
                    }

                    $arrOptions  = \StringUtil::deserialize($this->config->marketingTypesData, true);

                    foreach ($arrOptions as $key => $value)
                    {
                        $arrData['options'][ $key ] = $value;
                    }

                    break;

                case 'salutation':
                    $arrData['inputType'] = 'select';
                    $arrData['options'] = array();

                    if($this->config->addBlankSalutation)
                    {
                        $arrData['options'] = array('' => '-');
                    }

                    $arrOptions = \StringUtil::deserialize($this->config->salutationFields, true);

                    if(!empty($arrOptions))
                    {
                        foreach ($arrOptions as $opt)
                        {
                            $arrData['options'][ $opt['key'] ] = $opt['value'];
                        }
                    }

                    break;

                case 'objectTypes':
                    $arrData['inputType'] = 'select';
                    $arrData['options'] = array();

                    if($this->config->addBlankObjectType)
                    {
                        $arrData['options'] = array('' => '-');
                    }

                    $arrOptions  = \StringUtil::deserialize($this->config->objectTypesData, true);

                    foreach ($arrOptions as $key => $value)
                    {
                        $arrData['options'][ $key ] = $value;
                    }

                    break;

                case 'regions':
                    $arrData['inputType'] = 'select';
                    $arrData['options'] = array();

                    if($this->config->addBlankRegion)
                    {
                        $arrData['options'] = array('' => '-');
                    }

                    $arrOptions = \StringUtil::deserialize($this->config->regionsData);

                    foreach ($arrOptions as $key => $value)
                    {
                        $arrData['options'][ $key ] = $value;
                    }

                    break;

                default:
                    $arrData['inputType'] = 'text';
            }

            // HOOK: add custom logic
            if (isset($GLOBALS['TL_HOOKS']['parseLeadMatchingFormField']) && \is_array($GLOBALS['TL_HOOKS']['parseLeadMatchingFormField']))
            {
                foreach ($GLOBALS['TL_HOOKS']['parseLeadMatchingFormField'] as $callback)
                {
                    $this->import($callback[0]);
                    $this->{$callback[0]}->{$callback[1]}($field, $arrData, $strMode, $this->config, $this);
                }
            }

            /** @var \Widget $strClass */
            $strClass = $GLOBALS['TL_FFL'][ $arrData['inputType'] ];

            // Continue if the class is not defined
            if (!class_exists($strClass))
            {
                continue;
            }

            $objWidget = new $strClass($strClass::getAttributesFromDca($arrData, $field, $_SESSION['LEAD_MATCHING'][ $strMode ][ $field ], '', '', $this));

            $objWidget->id .= '_' . $this->id;
            $objWidget->storeValues = true;

            // Validate input
            if (\Input::post('FORM_SUBMIT') == $strFormId)
            {
                $objWidget->validate();
                $varValue = $objWidget->value;

                if($objWidget->hasErrors())
                {
                    $doNotSubmit = true;
                }
                elseif ($objWidget->submitInput())
                {
                    // Store the form data
                    $_SESSION['FORM_DATA'][$field] = $varValue;
                    $_SESSION['LEAD_MATCHING'][ $strMode ][$field] = $varValue;

                    $arrSubmitted[$field] = $varValue;
                }
            }

            $objTemplate->fields .= $objWidget->parse();
        }

        // Handle submitted forms
        if(\Input::post('FORM_SUBMIT') == $strFormId && !$doNotSubmit)
        {
            switch($strMode)
            {
                case 'contact':
                    // ToDo: Testing
                    $objEmail = new \Email();
                    $objEmail->from = $GLOBALS['TL_ADMIN_EMAIL'];
                    $objEmail->fromName = $GLOBALS['TL_ADMIN_NAME'];
                    $objEmail->subject = $this->config->mailSubject;

                    $message = "";

                    foreach ($arrSubmitted as $k=>$v)
                    {
                        $message .= ($GLOBALS['TL_LANG']['tl_lead_matching_meta'][$k] ?? ucfirst($k)) . ': ' . (\is_array($v) ? implode(', ', $v) : $v) . "\n";
                    }

                    $message .= "\n\n";

                    foreach ($_SESSION['LEAD_MATCHING']['estate'] as $kk=>$vv)
                    {
                        // ToDo: Get readable names of regions and object types
                        $message .= ($GLOBALS['TL_LANG']['tl_lead_matching_meta'][$kk] ?? ucfirst($kk)) . ': ' . (\is_array($vv) ? implode(', ', $vv) : $vv) . "\n";
                    }

                    $objEmail->text = $message . "\n";
                    $objEmail->sendTo($GLOBALS['TL_ADMIN_EMAIL']);
                    break;

                case 'estate':
                    $_SESSION['LEAD_MATCHING']['SUBMIT'] = 1;
                    break;
            }
        }
        elseif($doNotSubmit && $strMode === 'estate')
        {
            $_SESSION['LEAD_MATCHING']['SUBMIT'] = 0;
        }

        return $objTemplate->parse();
    }

    /**
     * Count items
     *
     * @return int
     */
    public function count()
    {
        if($this->config->type === 'system')
        {
            return SearchcriteriaModel::countPublishedByFilteredAttributes($this->config);
        }
        else
        {
            // HOOK: add custom logic
            if (isset($GLOBALS['TL_HOOKS']['countLeadMatching']) && \is_array($GLOBALS['TL_HOOKS']['countLeadMatching']))
            {
                foreach ($GLOBALS['TL_HOOKS']['countLeadMatching'] as $callback)
                {
                    $this->import($callback[0]);
                    return $this->{$callback[0]}->{$callback[1]}($this->config, $this);
                }
            }
        }

        return 0;
    }

    /**
     * Fetch items
     *
     * @param $limit
     * @param $offset
     *
     * @return mixed|null
     */
    public function fetch($limit, $offset)
    {
        if($this->config->type === 'system')
        {
            $arrOptions = array();

            if($limit)
            {
                $arrOptions['limit'] = $limit;
            }

            if($offset)
            {
                $arrOptions['offset'] = $offset;
            }

            return SearchcriteriaModel::findPublishedByFilteredAttributes($this->config, $arrOptions);
        }
        else
        {
            // HOOK: add custom logic
            if (isset($GLOBALS['TL_HOOKS']['fetchLeadMatching']) && \is_array($GLOBALS['TL_HOOKS']['fetchLeadMatching']))
            {
                foreach ($GLOBALS['TL_HOOKS']['fetchLeadMatching'] as $callback)
                {
                    $this->import($callback[0]);
                    return $this->{$callback[0]}->{$callback[1]}($this->config, $limit, $offset, $this);
                }
            }
        }

        return null;
    }

    /**
     * Return a key-value list of object types
     *
     * @param $arrIds
     *
     * @return array|null
     */
    private function getObjectTypeTitlesByIds($arrIds)
    {
        if($arrIds === null)
        {
            return null;
        }

        $values = array();

        /** @var $objObjectTypes */
        $objObjectTypes = ObjectTypeModel::findMultipleByIds($arrIds);

        if($objObjectTypes === null)
        {
            return null;
        }

        while ($objObjectTypes->next())
        {
            $values[ $objObjectTypes->id ] = $objObjectTypes->title;
        }

        return $values;
    }

    /**
     * Return a key-value list of regions
     *
     * @param $arrIds
     *
     * @return array|null
     */
    private function getRegionTitlesByIds($arrIds)
    {
        if($arrIds === null)
        {
            return null;
        }

        $values = array();

        /** @var $objRegions */
        $objRegions = RegionModel::findMultipleByIds($arrIds);

        if($objRegions === null)
        {
            return null;
        }

        while ($objRegions->next())
        {
            $values[ $objRegions->id ] = $objRegions->title;
        }

        return $values;
    }
}
