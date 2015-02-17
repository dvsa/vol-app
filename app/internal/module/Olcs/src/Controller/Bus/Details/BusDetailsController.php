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
        if ($this->isFromEbsr()) {
            $fields = $form->get('fields');

            foreach ($this->disableFormFields as $field) {
                $fields->get($field)->setAttribute('disabled', 'disabled');
            }

            $form->remove('form-actions');
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
