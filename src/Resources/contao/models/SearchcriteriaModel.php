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
 * Reads and writes search criteria
 *
 * @property integer $id
 * @property integer $tstamp
 * @property string  $title
 * @property string  $marketing
 * @property boolean $published
 *
 * @method static SearchcriteriaModel|null findById($id, array $opt=array())
 * @method static SearchcriteriaModel|null findOneBy($col, $val, $opt=array())
 * @method static SearchcriteriaModel|null findOneByTstamp($col, $val, $opt=array())
 * @method static SearchcriteriaModel|null findOneByMarketing($col, $val, $opt=array())
 * @method static SearchcriteriaModel|null findOneByTitle($col, $val, $opt=array())
 * @method static SearchcriteriaModel|null findOneByPublished($col, $val, $opt=array())
 *
 * @method static \Model\Collection|SearchcriteriaModel[]|SearchcriteriaModel|null findMultipleByIds($val, array $opt=array())
 * @method static \Model\Collection|SearchcriteriaModel[]|SearchcriteriaModel|null findByTstamp($val, array $opt=array())
 * @method static \Model\Collection|SearchcriteriaModel[]|SearchcriteriaModel|null findByMarketing($val, array $opt=array())
 * @method static \Model\Collection|SearchcriteriaModel[]|SearchcriteriaModel|null findByTitle($val, array $opt=array())
 * @method static \Model\Collection|SearchcriteriaModel[]|SearchcriteriaModel|null findByPublished($val, array $opt=array())
 *
 * @method static integer countById($id, array $opt=array())
 * @method static integer countByTstamp($id, array $opt=array())
 * @method static integer countByMarketing($id, array $opt=array())
 * @method static integer countByTitle($id, array $opt=array())
 * @method static integer countByPublished($id, array $opt=array())
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */

class SearchcriteriaModel extends \Model
{

    /**
     * Table name
     * @var string
     */
    protected static $strTable = 'tl_searchcriteria';
}
