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

namespace ContaoEstateManager\LeadMatchingTool\Model;

use Contao\Model;

/**
 * @property int    $id
 * @property int    $tstamp
 * @property string $type
 * @property string $title
 * @property string $marketingType
 * @property string $marketingTypes
 * @property string $marketingTypesData
 * @property string $objectTypes
 * @property string $objectTypesData
 * @property string $regions
 * @property string $regionsData
 * @property string $numberOfItems
 * @property string $perPage
 * @property string $listMetaFields
 * @property string $estateFormMetaFields
 * @property string $estateFormMetaFieldsMandatory
 * @property string $contactFormMetaFields
 * @property string $contactFormMetaFieldsMandatory
 * @property string $contactFormCheckboxes
 * @property string $countResults
 * @property string $addBlankMarketingType
 * @property string $addBlankObjectType
 * @property string $addBlankSalutation
 * @property string $addBlankRegion
 * @property string $addContactForm
 * @property string $addEstateForm
 * @property string $forceList
 * @property string $forceContact
 * @property string $estateFormTemplate
 * @property string $contactFormTemplate
 * @property string $listItemTemplate
 * @property string $mailSubject
 * @property string $mailTo
 * @property string $txtEstateHeadline
 * @property string $txtEstateDescription
 * @property string $txtListHeadline
 * @property string $txtListDescription
 * @property string $txtContactHeadline
 * @property string $txtContactDescription
 */
class LeadMatchingModel extends Model
{
    /**
     * Table name.
     *
     * @var string
     */
    protected static $strTable = 'tl_lead_matching';
}
