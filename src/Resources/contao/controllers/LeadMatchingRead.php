<?php

namespace ContaoEstateManager\LeadMatchingTool;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * LeadMatching read api controller.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
class LeadMatchingRead extends \Frontend
{
    /**
     * Method Constants
     */
    const METHOD_GET  = 'GET';
    const METHOD_POST = 'POST';

    /**
     * Run the controller
     *
     * @param String $module  Module-Name
     * @param int    $id      Id
     *
     * @return JsonResponse|string
     */
    public function run($module, $id)
    {
        $data = array(
            'msg' => 'no data'
        );

        switch ($module)
        {
            case 'count':

                if(!$id || !($config = LeadMatchingModel::findById($id)))
                {
                    $data = array(
                        'error' => 1,
                        'message' => 'No Configuration found (' . $id . ')'
                    );

                    break;
                }

                $validParam = array('marketingType', 'regions', 'objectTypes', 'room', 'area', 'price');
                $currParam = $this->getParameters(self::METHOD_GET, $validParam);

                $count = SearchcriteriaModel::countPublishedByFilteredAttributes($config, $currParam);

                $data = array(
                    'error' => 0,
                    'data' => ['count' => $count]
                );

                break;
        }

        return new JsonResponse($data);
    }

    /**
     * Return parameters by method
     *
     * @param $method
     * @param array $arrValidParam Array of valid parameters
     * @param array $arrDefaultParam Optional array of default parameters
     *
     * @return array
     */
    public function getParameters($method, $arrValidParam, $arrDefaultParam=array())
    {
        $arrMethod = array();
        $param = $arrDefaultParam;

        switch($method){
            case self::METHOD_GET:  $arrMethod = $_GET; break;
            case self::METHOD_POST: $arrMethod = $_POST; break;
        }

        foreach ($arrMethod as $key => $value)
        {
            if (in_array($key, $arrValidParam))
            {
                $param[$key] = $value;
            }
        }

        return $param;
    }
}
