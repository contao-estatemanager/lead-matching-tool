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


/**
 * Reads and writes lead matching
 *
 * @property integer $id
 * @property integer $tstamp
 * @property string  $type
 * @property string  $title
 * @property string  $marketingType
 * @property string  $objectTypes
 * @property string  $regions
 * @property string  $listMetaFields
 * @property string  $estateFormMetaFields
 * @property string  $estateFormMetaFieldsMandatory
 * @property string  $contactFormMetaFields
 * @property string  $contactFormMetaFieldsMandatory
 * @property string  $countResults
 * @property string  $addBlankObjectType
 * @property string  $addBlankSalutation
 * @property string  $addBlankRegion
 * @property string  $addContactForm
 * @property string  $addEstateForm
 * @property string  $forceList
 * @property string  $forceContact
 * @property string  $estateFormTemplate
 * @property string  $contactFormTemplate
 * @property string  $listItemTemplate
 *
 * @method static LeadMatchingModel|null findById($id, array $opt=array())
 * @method static LeadMatchingModel|null findOneBy($col, $val, $opt=array())
 * @method static LeadMatchingModel|null findOneByTstamp($col, $val, $opt=array())
 * @method static LeadMatchingModel|null findOneByType($col, $val, $opt=array())
 * @method static LeadMatchingModel|null findOneByMarketingType($col, $val, $opt=array())
 * @method static LeadMatchingModel|null findOneByObjectTypes($col, $val, $opt=array())
 * @method static LeadMatchingModel|null findOneByTitle($col, $val, $opt=array())
 * @method static LeadMatchingModel|null findOneByRegions($col, $val, $opt=array())
 * @method static LeadMatchingModel|null findOneByCountResults($col, $val, $opt=array())
 * @method static LeadMatchingModel|null findOneByAddContactForm($col, $val, $opt=array())
 * @method static LeadMatchingModel|null findOneByAddEstateForm($col, $val, $opt=array())
 * @method static LeadMatchingModel|null findOneByForceList($col, $val, $opt=array())
 * @method static LeadMatchingModel|null findOneByForceContact($col, $val, $opt=array())
 * @method static LeadMatchingModel|null findOneByEstateFormTemplate($col, $val, $opt=array())
 * @method static LeadMatchingModel|null findOneByContactFormTemplate($col, $val, $opt=array())
 *
 * @method static \Model\Collection|LeadMatchingModel[]|LeadMatchingModel|null findMultipleByIds($val, array $opt=array())
 * @method static \Model\Collection|LeadMatchingModel[]|LeadMatchingModel|null findByTstamp($val, array $opt=array())
 * @method static \Model\Collection|LeadMatchingModel[]|LeadMatchingModel|null findByType($val, array $opt=array())
 * @method static \Model\Collection|LeadMatchingModel[]|LeadMatchingModel|null findByMarketingType($val, array $opt=array())
 * @method static \Model\Collection|LeadMatchingModel[]|LeadMatchingModel|null findByObjectTypes($val, array $opt=array())
 * @method static \Model\Collection|LeadMatchingModel[]|LeadMatchingModel|null findByTitle($val, array $opt=array())
 * @method static \Model\Collection|LeadMatchingModel[]|LeadMatchingModel|null findByRegions($val, array $opt=array())
 * @method static \Model\Collection|LeadMatchingModel[]|LeadMatchingModel|null findByCountResults($val, array $opt=array())
 * @method static \Model\Collection|LeadMatchingModel[]|LeadMatchingModel|null findByAddContactForm($val, array $opt=array())
 * @method static \Model\Collection|LeadMatchingModel[]|LeadMatchingModel|null findByAddEstateForm($val, array $opt=array())
 * @method static \Model\Collection|LeadMatchingModel[]|LeadMatchingModel|null findByForceList($val, array $opt=array())
 * @method static \Model\Collection|LeadMatchingModel[]|LeadMatchingModel|null findByForceContact($val, array $opt=array())
 * @method static \Model\Collection|LeadMatchingModel[]|LeadMatchingModel|null findByEstateFormTemplate($val, array $opt=array())
 * @method static \Model\Collection|LeadMatchingModel[]|LeadMatchingModel|null findByContactFormTemplate($val, array $opt=array())
 *
 * @method static integer countById($id, array $opt=array())
 * @method static integer countByTstamp($id, array $opt=array())
 * @method static integer countByType($id, array $opt=array())
 * @method static integer countByMarketingType($id, array $opt=array())
 * @method static integer countByObjectTypes($id, array $opt=array())
 * @method static integer countByTitle($id, array $opt=array())
 * @method static integer countByRegions($id, array $opt=array())
 * @method static integer countByCountResults($id, array $opt=array())
 * @method static integer countByAddContactForm($id, array $opt=array())
 * @method static integer countByAddEstateForm($id, array $opt=array())
 * @method static integer countByForceList($id, array $opt=array())
 * @method static integer countByForceContact($id, array $opt=array())
 * @method static integer countByEstateFormTemplate($id, array $opt=array())
 * @method static integer countByContactFormTemplate($id, array $opt=array())
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */

class LeadMatchingModel extends \Model
{

    /**
     * Table name
     * @var string
     */
    protected static $strTable = 'tl_lead_matching';
}
