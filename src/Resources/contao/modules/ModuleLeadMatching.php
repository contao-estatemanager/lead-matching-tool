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
        if ($this->numberOfItems > 0)
        {
            $limit = $this->numberOfItems;
        }

        $this->Template->items = array();
        $this->Template->empty = $GLOBALS['TL_LANG']['MSC']['emptyList'];

        if ($intTotal < 1)
        {
            return;
        }

        $total = $intTotal - $offset;

        // Split the results
        if ($this->perPage > 0 && (!isset($limit) || $this->numberOfItems > $this->perPage))
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
            if ($page < 1 || $page > max(ceil($total/$this->perPage), 1))
            {
                throw new PageNotFoundException('Page not found: ' . \Environment::get('uri'));
            }

            // Set limit and offset
            $limit = $this->perPage;
            $offset += (max($page, 1) - 1) * $this->perPage;

            // Overall limit
            if ($offset + $limit > $total)
            {
                $limit = $total - $offset;
            }

            // Add the pagination menu
            $objPagination = new \Pagination($total, $this->perPage, \Config::get('maxPaginationLinks'), $id);
            $this->Template->pagination = $objPagination->generate("\n  ");
        }

        $objItems = $this->fetch(($limit ?: 0), $offset);

        // Add the articles
        if ($objItems !== null)
        {
            $this->Template->items = $this->parseItems($objItems);
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
                $varValue = $objItem->{$field} ? $objItem->{$field} : $GLOBALS['TL_LANG']['tl_lead_matching_meta']['emptyField'];

                $arrFields[] = array(
                    'class' => $field,
                    'label' => $varLabel,
                    'value' => $varValue
                );
            }
        }

        $objTemplate->fields = $arrFields;

        // HOOK: add custom logic
        if (isset($GLOBALS['TL_HOOKS']['parseLeadMatchingItem']) && \is_array($GLOBALS['TL_HOOKS']['parseLeadMatchingItem']))
        {
            foreach ($GLOBALS['TL_HOOKS']['parseLeadMatchingItem'] as $callback)
            {
                $this->import($callback[0]);
                $this->{$callback[0]}->{$callback[1]}($objTemplate, $this->config, $this);
            }
        }

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
        $strHidden = '';
        $doNotSubmit = false;

        switch ($strMode)
        {
            case 'contact':
                $strFormId   = 'form_contact_' . $this->id;
                $strSubmit   = $GLOBALS['TL_LANG']['tl_lead_matching_meta']['submitContact'];

                $strTemplate  = $this->config->contactFormTemplate;
                $arrEditable  = \StringUtil::deserialize($this->config->contactFormMetaFields, true);
                $arrMandatory = \StringUtil::deserialize($this->config->contactFormMetaFieldsMandatory, true);

                // Add estate fields as hidden fields
                if($this->Template->addEstateForm)
                {
                    if($arrHidden = \StringUtil::deserialize($this->config->estateFormMetaFields))
                    {
                        foreach ($arrHidden as $hField)
                        {
                            $objWidget = new \FormHidden(\FormHidden::getAttributesFromDca(['inputType'=>'hidden'], $hField, $_SESSION['LEAD_MATCHING']['estate'][ $hField ], '', '', $this));
                            $strHidden .= $objWidget->parse();
                        }
                    }
                }
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
        $objTemplate->hidden = $strHidden;
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

                    $arrOptions = \StringUtil::deserialize($this->config->objectTypes);

                    if($arrOptions !== null)
                    {
                        /** @var $objObjectTypes */
                        $objObjectTypes = ObjectTypeModel::findMultipleByIds($arrOptions);

                        if($objObjectTypes !== null)
                        {
                            while ($objObjectTypes->next())
                            {
                                $arrData['options'][ $objObjectTypes->id ] = $objObjectTypes->title;
                            }
                        }
                    }
                    break;

                case 'regions':
                    $arrData['inputType'] = 'select';
                    $arrData['options'] = array();

                    if($this->config->addBlankRegion)
                    {
                        $arrData['options'] = array('' => '-');
                    }

                    $arrRegions = \StringUtil::deserialize($this->config->regions);

                    if($arrRegions !== null)
                    {
                        /** @var $objRegions */
                        $objRegions = RegionModel::findMultipleByIds($arrRegions);

                        if($objRegions !== null)
                        {
                            while ($objRegions->next())
                            {
                                $arrData['options'][ $objRegions->id ] = $objRegions->title;
                            }
                        }
                    }
                    break;

                default:
                    $arrData['inputType'] = 'text';
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

                    // Todo: send mail

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
        // HOOK: add custom logic
        if (isset($GLOBALS['TL_HOOKS']['countLeadMatching']) && \is_array($GLOBALS['TL_HOOKS']['countLeadMatching']))
        {
            foreach ($GLOBALS['TL_HOOKS']['countLeadMatching'] as $callback)
            {
                $this->import($callback[0]);
                return $this->{$callback[0]}->{$callback[1]}($this->config, $this);
            }
        }

        return SearchcriteriaModel::countPublishedByFilteredAttributes($this->config);
    }

    /**
     * Fetch items
     *
     * @param $limit
     * @param $offset
     *
     * @return \Contao\Model\Collection|null
     */
    public function fetch($limit, $offset)
    {
        // HOOK: add custom logic
        if (isset($GLOBALS['TL_HOOKS']['fetchLeadMatching']) && \is_array($GLOBALS['TL_HOOKS']['fetchLeadMatching']))
        {
            foreach ($GLOBALS['TL_HOOKS']['fetchLeadMatching'] as $callback)
            {
                $this->import($callback[0]);
                return $this->{$callback[0]}->{$callback[1]}($limit, $offset, $this->config, $this);
            }
        }

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
}
