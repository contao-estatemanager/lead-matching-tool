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

        return parent::generate();
    }

    /**
     * Generate the module
     */
    protected function compile()
    {
        $isFilterSubmitted = false;

        $this->Template->addCunter      = !!$this->lmtCountResults;
        $this->Template->addEstateForm  = !!$this->addEstateForm;
        $this->Template->addContactForm = (!$this->addContactForm || $isFilterSubmitted) || (!!$this->addContactForm && !!$this->forceContact);
        $this->Template->addList        = (!$this->addEstateForm || $isFilterSubmitted) || (!!$this->addEstateForm && !!$this->forceList);

        if($this->Template->addEstateForm)
        {
            #$this->Template->estateForm = $this->getForm($this->estateForm);
            $this->Template->estateForm = $this->prepareEstateForm($this->estateForm);
        }

        if($this->Template->addContactForm)
        {
            $this->Template->contactForm = $this->getForm($this->form);
        }

        $arrColumns = array('published=1');
        $arrValues  = array();
        $arrOptions = array();

        if($this->lmtMode === 'system')
        {
            // system specific logic
            if($this->lmtMarketing)
            {
                $arrColumns[] = 'marketing=?';
                $arrValues[]  = $this->lmtMarketing;
            }
        }
        else
        {
            // HOOK: add custom logic
            if (isset($GLOBALS['TL_HOOKS']['preCompileLeadMatching']) && \is_array($GLOBALS['TL_HOOKS']['preCompileLeadMatching']))
            {
                foreach ($GLOBALS['TL_HOOKS']['preCompileLeadMatching'] as $callback)
                {
                    $this->import($callback[0]);
                    $this->{$callback[0]}->{$callback[1]}( $this->Template, $arrColumns, $arrValues, $arrOptions, $this);
                }
            }
        }

        $cntAbsolute = $this->count($arrColumns, $arrValues, $arrOptions);

        if($this->Template->addCunter)
        {
            $this->Template->cntAbsolute = $cntAbsolute;
        }

        if($this->Template->addList)
        {
            // ToDo List and Pagination
        }
    }

    /**
     * Count items
     *
     * @param $arrColumns
     *
     * @param null $arrValues
     *
     * @param null $arrOptions
     *
     * @return int
     */
    public function count($arrColumns, $arrValues=null, $arrOptions=null)
    {
        // HOOK: add custom logic
        if (isset($GLOBALS['TL_HOOKS']['countLeadMatching']) && \is_array($GLOBALS['TL_HOOKS']['countLeadMatching']))
        {
            foreach ($GLOBALS['TL_HOOKS']['countLeadMatching'] as $callback)
            {
                $this->import($callback[0]);
                $this->{$callback[0]}->{$callback[1]}($arrColumns, $arrValues, $arrOptions, $this);
            }
        }

        if($this->lmtMode === 'system')
        {
            return LeadMatchingModel::countBy($arrColumns, $arrValues, $arrOptions);
        }
    }

    /**
     * Generate list
     */
    protected function generateResultList()
    {

    }

    /**
     * Prepare and parse the estate form
     *
     * @param $formId
     *
     * @param string $strColumn
     *
     * @return string
     */
    public function prepareEstateForm($formId, $strColumn = 'main')
    {
        $objFormModel = \FormModel::findByIdOrAlias($formId);

        if($objFormModel === null)
        {
            \Controller::log('Form with id "' . $formId . '" does not exist', __METHOD__, TL_ERROR);
            return '';
        }

        $objFormModel->typePrefix = 'ce_';
        $objFormModel->form = $objFormModel->id;

        $objForm = new \Form($objFormModel, $strColumn);

        // ToDo: Add fields dynamic: hidden submit (, submit button?)
        // ToDo: Add fields as hidden to the contact form

        return $objForm->generate();
    }
}
