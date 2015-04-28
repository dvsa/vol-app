<?php

/**
 * AbstractGenericVehiclesPsvController.php
 */
namespace Olcs\Controller\Lva;

use Common\Controller\Lva\AbstractVehiclesPsvController;

/**
 * Class AbstractGenericVehiclesPsvController
 *
 * Inherited by all the Psv vehicle controllers
 *
 * @package Olcs\Controller\Lva
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
abstract class AbstractGenericVehiclesPsvController extends AbstractVehiclesPsvController
{
    /**
     * Alter the form to remove the edit buttons.
     *
     * @param \Zend\Form\Form $form
     *
     * @param $data
     *
     * @return \Zend\Form\Form
     */
    public function alterForm($form, $data)
    {
        $form = parent::alterForm($form, $data);

        if ($form->has('medium')) {
            $form->get('medium')->get('table')->getTable()->removeAction('edit');
        }

        if ($form->has('large')) {
            $form->get('large')->get('table')->getTable()->removeAction('edit');
        }

        return $form;
    }
}
