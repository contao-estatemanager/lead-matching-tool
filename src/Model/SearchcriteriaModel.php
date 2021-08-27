<?php
namespace ContaoEstateManager\LeadMatchingTool\Model;

use Contao\Database;
use Contao\Model;
use Contao\Model\Collection;

/**
 * @property integer $id
 * @property integer $tstamp
 * @property string  $title
 * @property string  $marketing
 * @property boolean $published
 */

class SearchcriteriaModel extends Model
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
     * @param null $data
     *
     * @return Collection|null
     */
    public static function findPublishedByFilteredAttributes($config, $arrOptions, $data=null): ?Collection
    {
        $strTable = static::$strTable;
        $strQuery = static::buildFilterQuery($config, $data);

        $objResult = Database::getInstance()->prepare('SELECT ' . $strTable . '.* FROM ' . $strTable . $strQuery);

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
     * @param null $data
     *
     * @return int
     */
    public static function countPublishedByFilteredAttributes($config, $data=null): int
    {
        $strTable = static::$strTable;
        $strQuery = static::buildFilterQuery($config, $data);

        $objCount = Database::getInstance()->execute('SELECT COUNT(' . $strTable . '.id) FROM ' . $strTable . $strQuery);

        return $objCount->numRows;
    }

    /**
     * Build query string
     *
     * @param $config
     * @param null $data
     *
     * @return string
     */
    private static function buildFilterQuery($config, $data=null): string
    {
        $strTable = static::$strTable;
        $arrQuery = array($strTable . '.published=1');
        $strQuery = '';

        if($data === null)
        {
            $data = $_SESSION['LEAD_MATCHING']['estate'];
        }

        // set marketing type if is not possible to choose manually
        if($config->marketingType)
        {
            $arrQuery[] = 'AND ' . $strTable . '.marketing="' . $config->marketingType . '"';
        }

        if(is_array($data))
        {
            foreach ($data as $field => $value)
            {
                if($value)
                {
                    switch($field)
                    {
                        case 'marketingType':
                            $arrQuery[] = 'AND marketing="' . $value . '"';
                            break;

                        case 'objectTypes':
                            $arrQuery[] = 'AND objectType="' . $value . '"';
                            break;

                        case 'regions':
                            if(!!$config->preciseRegionSearch)
                            {
                                $strConnectTable = 'tl_region_connection';
                                $strQuery .= ' LEFT JOIN ' . $strConnectTable .' ON ' . $strConnectTable . '.pid=' . $strTable . '.id';
                                $arrQuery[] = 'AND ((' . $strConnectTable . '.rid=' . $value . ' AND ' . $strConnectTable . '.ptable="' . $strTable . '") OR ' . $strTable . '.regions IS NULL)';
                            }
                            elseif($data['region_latitude'] && $data['region_longitude'])
                            {
                                $arrQuery[] = 'AND ' . $strTable . '.latitude!=0 AND ' . $strTable . '.longitude!=0 AND (6371*acos(cos(radians(' . $data['region_latitude'] . '))*cos(radians(' . $strTable . '.latitude))*cos(radians(' . $strTable . '.longitude)-radians(' . $data['region_longitude'] . '))+sin(radians(' . $data['region_latitude'] . '))*sin(radians(' . $strTable . '.latitude)))) <= ' . $data['range'] ?? 100;
                            }
                            break;

                        case 'room':
                        case 'price':
                        case 'area':
                            $tableField = $strTable . '.' . $field;
                            $arrQuery[] = 'AND (' . $tableField . '_from' . '<=' . $value . ' OR ' . $tableField . '_from="") AND (' . $tableField . '_to' . '>=' . $value . ' OR ' . $tableField . '_to="")';
                            break;
                    }
                }
            }
        }

        // return query string
        return $strQuery . ' WHERE ' . implode(' ', $arrQuery) . ' GROUP BY ' . $strTable . '.id';
    }
}
