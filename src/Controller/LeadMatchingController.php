<?php

namespace ContaoEstateManager\LeadMatchingTool\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use ContaoEstateManager\LeadMatchingTool\LeadMatchingRead;

/**
 * Handles the LeadMatching api routes.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
class LeadMatchingController extends Controller
{
    /**
     * Runs the command scheduler. (READ)
     *
     * @param $module
     * @param $id
     *
     * @return JsonResponse|string
     */
    public function readAction($module, $id)
    {
        $this->container->get('contao.framework')->initialize();

        $controller = new LeadMatchingRead();

        return $controller->run($module, $id);
    }
}
