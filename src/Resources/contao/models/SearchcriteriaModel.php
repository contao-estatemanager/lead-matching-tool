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

    /**
     * Return filtered search criteria
     *
     * @param $config
     * @param $arrOptions
     *
     * @return \Contao\Model\Collection|null
     */
    public static function findPublishedByFilteredAttributes($config, $arrOptions)
    {
        $strTable = static::$strTable;
        $strQuery = static::buildFilterQuery($config);

        $objResult = \Database::getInstance()->prepare('SELECT ' . $strTable . '.* FROM ' . $strTable . $strQuery);

        if ($arrOptions['limit'] && $arrOptions['offset'])
        {
            $objResult->limit($arrOptions['limit'], $arrOptions['offset']);
        }
        elseif ($arrOptions['limit'])
        {
            $objResult->limit($arrOptions['limit']);
        }

        $dbResult = $objResult->execute();

        if ($dbResult->numRows < 1)
        {
            return null;
        }

        return static::createCollectionFromDbResult($dbResult, $strTable);
    }

    /**
     * Return the number of filtered results
     *
     * @param $config
     *
     * @return int
     */
    public static function countPublishedByFilteredAttributes($config)
    {
        $strTable = static::$strTable;
        $strQuery = static::buildFilterQuery($config);

        $objCount = \Database::getInstance()->execute('SELECT COUNT(' . $strTable . '.id) FROM ' . $strTable . $strQuery);

        return $objCount->numRows;
    }

    /**
     * Build query string
     *
     * @param $config
     *
     * @return string
     */
    private static function buildFilterQuery($config)
    {
        $strTable = static::$strTable;
        $arrQuery = array($strTable . '.published=1');
        $strQuery = '';

        if($config->marketingType)
        {
            $arrQuery[] = 'AND ' . $strTable . '.marketing=' . $config->marketingType;
        }

        if(is_array($_SESSION['LEAD_MATCHING']['estate']))
        {
            foreach ($_SESSION['LEAD_MATCHING']['estate'] as $field => $value)
            {
                if($value)
                {
                    switch($field)
                    {
                        case 'objectTypes':
                            $strConnectTable = 'tl_object_type_connection';
                            $strQuery .= ' LEFT JOIN ' . $strConnectTable .' ON ' . $strConnectTable . '.pid=' . $strTable . '.id';
                            $arrQuery[] = 'AND ((' . $strConnectTable . '.oid=' . $value . ' AND ' . $strConnectTable . '.ptable="' . $strTable . '") OR ' . $strTable . '.objectTypes IS NULL)';
                            break;

                        case 'regions':
                            $strConnectTable = 'tl_region_connection';
                            $strQuery .= ' LEFT JOIN ' . $strConnectTable .' ON ' . $strConnectTable . '.pid=' . $strTable . '.id';
                            $arrQuery[] = 'AND ((' . $strConnectTable . '.rid=' . $value . ' AND ' . $strConnectTable . '.ptable="' . $strTable . '") OR ' . $strTable . '.regions IS NULL)';
                            break;

                        case 'room':
                        case 'price':
                        case 'area':
                            $arrQuery[] = 'AND ' . $strTable . '.' . $field . '_from' . '<=' . $value . ' AND ' . $strTable . '.' . $field . '_to' . '>=' . $value;
                            break;
                    }
                }
            }
        }

        // return query string
        return $strQuery . ' WHERE ' . implode(' ', $arrQuery) . ' GROUP BY ' . $strTable . '.id';
    }
}
