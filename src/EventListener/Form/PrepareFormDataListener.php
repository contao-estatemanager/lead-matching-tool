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

namespace ContaoEstateManager\LeadMatchingTool\EventListener\Form;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\Form;
use Contao\System;
use ContaoEstateManager\LeadMatchingTool\Controller\FrontendModule\LeadMatchingController;

/**
 * @Hook("prepareFormData")
 */
class PrepareFormDataListener
{
    /**
     * Pass filter data to contact form.
     */
    public function __invoke(array &$submittedData, array &$labels, array $fields, Form $form): void
    {
        $objSessionBag = System::getContainer()->get('session')->getBag('contao_frontend');
        $forms = $objSessionBag->get(LeadMatchingController::SESSION_BAG_KEY);

        if (\array_key_exists($form->id, $forms))
        {
            foreach ($forms[$form->id] as $name => $data)
            {
                $labels[$name] = $data['label'];
                $submittedData[$name] = $data['value'];
            }
        }
    }
}
