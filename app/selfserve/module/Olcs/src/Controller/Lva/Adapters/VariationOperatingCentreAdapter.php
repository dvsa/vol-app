<?php

/**
 * External Variation Operating Centre Adapter
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Adapters;

use Common\Controller\Lva\Adapters\VariationOperatingCentreAdapter as CommonVariationOperatingCentreAdapter;
use Zend\Form\Form;

/**
 * Variation Operating Centre Adapter
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationOperatingCentreAdapter extends CommonVariationOperatingCentreAdapter
{
    /**
     * Extend the abstract behaviour to alter the action form
     *
     * @param \Zend\Form\Form $form
     * @return \Zend\Form\Form
     */
    public function alterActionForm(Form $form)
    {
        $form = parent::alterActionForm($form);

        $action = $this->getOperatingCentreAction();

        if ($action !== self::ACTION_ADDED) {
            $this->disableAddressFields($form);
        }

        return $form;
    }

    /**
     * Process address lookup for main form
     *
     * @param Form $form
     * @param Request $request
     * @return type
     */
    public function processAddressLookupForm($form, $request)
    {
        $action = $this->getOperatingCentreAction();

        if ($action !== self::ACTION_ADDED) {
            return false;
        }

        return parent::processAddressLookupForm($form, $request);
    }
}
