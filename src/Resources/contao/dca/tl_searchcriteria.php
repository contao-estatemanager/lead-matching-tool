<?php
/**
 * This file is part of Contao EstateManager.
 *
 * @link      https://www.contao-estatemanager.com/
 * @source    https://github.com/contao-estatemanager/lead-matching-tool
 * @copyright Copyright (c) 2019  Oveleon GbR (https://www.oveleon.de)
 * @license   https://www.contao-estatemanager.com/lizenzbedingungen.html
 */

$GLOBALS['TL_DCA']['tl_searchcriteria'] = array
(

    // Config
    'config' => array
    (
        'dataContainer'               => 'Table',
        'switchToEdit'                => true,
        'enableVersioning'            => true,
        'markAsCopy'                  => 'title',
        'onload_callback' => array
        (
            array('tl_searchcriteria', 'checkPermission')
        ),
        'sql' => array
        (
            'keys' => array
            (
                'id' => 'primary',
                'published' => 'index'
            )
        )
    ),

    // List
    'list' => array
    (
        'sorting' => array
        (
            'mode'                    => 2,
            'fields'                  => array('id'),
            'flag'                    => 1,
            'panelLayout'             => 'filter;sort,search,limit'
        ),
        'label' => array
        (
            'fields'                  => array('id', 'title', 'marketing', 'tstamp'),
            'showColumns'             => true,
            'label_callback'          => array('tl_searchcriteria', 'labelCallback')
        ),
        'global_operations' => array
        (
            'all' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'                => 'act=select',
                'class'               => 'header_edit_all',
                'attributes'          => 'onclick="Backend.getScrollOffset()" accesskey="e"'
            )
        ),
        'operations' => array
        (
            'edit' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_searchcriteria']['edit'],
                'href'                => 'act=edit',
                'icon'                => 'edit.svg'
            ),
            'copy' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_searchcriteria']['copy'],
                'href'                => 'act=copy',
                'icon'                => 'copy.svg',
            ),
            'delete' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_searchcriteria']['delete'],
                'href'                => 'act=delete',
                'icon'                => 'delete.svg',
                'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"',
            ),
            'toggle' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_searchcriteria']['toggle'],
                'icon'                => 'visible.svg',
                'attributes'          => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s,\'tl_searchcriteria\')"',
                'button_callback'     => array('tl_searchcriteria', 'toggleIcon')
            ),
            'show' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_searchcriteria']['show'],
                'href'                => 'act=show',
                'icon'                => 'show.svg'
            )
        )
    ),

    // Palettes
    'palettes' => array
    (
        'default'                     => '{title_legend},title,marketing;{config_legend},regions,room_from,room_to,area_from,area_to,price_from,price_to;{published_legend},published'
    ),

    // Fields
    'fields' => array
    (
        'id' => array
        (
            'label'                   => array('ID'),
            'sorting'                 => true,
            'sql'                     => "int(10) unsigned NOT NULL auto_increment"
        ),
        'tstamp' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL default '0'",
            'sorting'                 => true
        ),
        'title' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_searchcriteria']['title'],
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'marketing' => array
        (
            'label'                     => &$GLOBALS['TL_LANG']['tl_searchcriteria']['marketing'],
            'inputType'                 => 'select',
            'sorting'                   => true,
            'options'                   => array('kauf', 'miete'),
            'eval'                      => array('includeBlankOption'=>true, 'tl_class'=>'w50'),
            'sql'                       => "varchar(16) NOT NULL default ''"
        ),
        'regions' => array
        (
            'label'                     => &$GLOBALS['TL_LANG']['tl_searchcriteria']['regions'],
            'inputType'                 => 'regionTree',
            'eval'                      => array('multiple'=>true, 'fieldType'=>'checkbox', 'tl_class'=>'w50 clr'),
            'sql'                       => "blob NULL",
            'save_callback'             => array(
                array("ContaoEstateManager\\RegionEntity\\Region", "regionConnectionSaveCallback")
            )
        ),
        'room_from' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_searchcriteria']['room_from'],
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50 clr'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'room_to' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_searchcriteria']['room_to'],
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'area_from' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_searchcriteria']['area_from'],
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50 clr'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'area_to' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_searchcriteria']['area_to'],
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'price_from' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_searchcriteria']['price_from'],
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50 clr'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'price_to' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_searchcriteria']['price_to'],
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'published' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_searchcriteria']['published'],
            'exclude'                 => true,
            'filter'                  => true,
            'flag'                    => 1,
            'inputType'               => 'checkbox',
            'eval'                    => array('doNotCopy'=>true),
            'sql'                     => "char(1) NOT NULL default ''"
        ),
    )
);

/**
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
class tl_searchcriteria extends Backend
{

    /**
     * Import the back end user object
     */
    public function __construct()
    {
        parent::__construct();
        $this->import('BackendUser', 'User');
    }

    /**
     * Check permissions to edit table tl_searchcriteria
     *
     * @throws Contao\CoreBundle\Exception\AccessDeniedException
     */
    public function checkPermission()
    {
        return;
    }

    /**
     * Add and validate information
     *
     * @param array                $row
     * @param string               $label
     * @param Contao\DataContainer $dc
     * @param array                $args
     *
     * @return array
     */
    public function labelCallback($row, $label, Contao\DataContainer $dc, $args)
    {
        $args[3] = date(\Config::get('datimFormat'), $args[3]);

        return $args;
    }

    /**
     * Return the "toggle visibility" button
     *
     * @param array  $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     */
    public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
    {
        if (\strlen(Input::get('tid')))
        {
            $this->toggleVisibility(Input::get('tid'), (Input::get('state') == 1), (@func_get_arg(12) ?: null));
            $this->redirect($this->getReferer());
        }

        // Check permissions AFTER checking the tid, so hacking attempts are logged
        if (!$this->User->hasAccess('tl_searchcriteria::published', 'alexf'))
        {
            return '';
        }

        $href .= '&amp;tid='.$row['id'].'&amp;state='.($row['published'] ? '' : 1);

        if (!$row['published'])
        {
            $icon = 'invisible.svg';
        }

        return '<a href="'.$this->addToUrl($href).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label, 'data-state="' . ($row['published'] ? 1 : 0) . '"').'</a> ';
    }

    /**
     * Toggle the visibility of a search criteria
     *
     * @param integer       $intId
     * @param boolean       $blnVisible
     * @param DataContainer $dc
     */
    public function toggleVisibility($intId, $blnVisible, DataContainer $dc=null)
    {
        // Set the ID and action
        Input::setGet('id', $intId);
        Input::setGet('act', 'toggle');

        if ($dc)
        {
            $dc->id = $intId; // see #8043
        }

        // Trigger the onload_callback
        if (\is_array($GLOBALS['TL_DCA']['tl_searchcriteria']['config']['onload_callback']))
        {
            foreach ($GLOBALS['TL_DCA']['tl_searchcriteria']['config']['onload_callback'] as $callback)
            {
                if (\is_array($callback))
                {
                    $this->import($callback[0]);
                    $this->{$callback[0]}->{$callback[1]}($dc);
                }
                elseif (\is_callable($callback))
                {
                    $callback($dc);
                }
            }
        }

        // Check the field access
        if (!$this->User->hasAccess('tl_searchcriteria::published', 'alexf'))
        {
            throw new Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to publish/unpublish search criteria ID ' . $intId . '.');
        }

        // Set the current record
        if ($dc)
        {
            $objRow = $this->Database->prepare("SELECT * FROM tl_searchcriteria WHERE id=?")
                ->limit(1)
                ->execute($intId);

            if ($objRow->numRows)
            {
                $dc->activeRecord = $objRow;
            }
        }

        $time = time();

        // Update the database
        $this->Database->prepare("UPDATE tl_searchcriteria SET tstamp=$time, published='" . ($blnVisible ? '1' : '') . "' WHERE id=?")
            ->execute($intId);

        if ($dc)
        {
            $dc->activeRecord->tstamp = $time;
            $dc->activeRecord->published = ($blnVisible ? '1' : '');
        }

        // Trigger the onsubmit_callback
        if (\is_array($GLOBALS['TL_DCA']['tl_searchcriteria']['config']['onsubmit_callback']))
        {
            foreach ($GLOBALS['TL_DCA']['tl_searchcriteria']['config']['onsubmit_callback'] as $callback)
            {
                if (\is_array($callback))
                {
                    $this->import($callback[0]);
                    $this->{$callback[0]}->{$callback[1]}($dc);
                }
                elseif (\is_callable($callback))
                {
                    $callback($dc);
                }
            }
        }
    }
}
