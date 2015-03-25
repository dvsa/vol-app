<?php

/**
 * Bus Details Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace Olcs\Controller\Bus\Details;

use Olcs\Controller\Bus\BusController;

/**
 * Bus Details Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BusDetailsController extends BusController
{
    protected $section = 'details';
    protected $subNavRoute = 'licence_bus_details';

    protected $inlineScripts = ['forms/bus-details-ta'];

    public function alterFormBeforeValidation($form)
    {
        if ($this->isFromEbsr() || !$this->isLatestVariation()) {
            $form->setOption('readonly', true);
        }

        return $form;
    }

    public function redirectToIndex()
    {
        return $this->redirectToRoute(
            null,
            ['action'=>'edit'],
            ['code' => '303'], // Why? No cache is set with a 303 :)
            true
        );
    }
}
