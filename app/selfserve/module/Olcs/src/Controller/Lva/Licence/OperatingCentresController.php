<?php

/**
 * External Licencing Operating Centres Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Licence;

use Zend\Form\Form;
use Common\Controller\Lva;
use Olcs\Controller\Lva\Traits\LicenceControllerTrait;
use Common\Controller\Lva\Traits\LicenceOperatingCentresControllerTrait;

/**
 * External Licencing Operating Centres Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class OperatingCentresController extends Lva\AbstractOperatingCentresController
{
    use LicenceControllerTrait,
        LicenceOperatingCentresControllerTrait {
            LicenceOperatingCentresControllerTrait::alterActionForm as commonAlterActionForm;
        }

    protected $lva = 'licence';
    protected $location = 'external';

    public function indexAction()
    {
        // we can't traitify this due to the parent reference...
        $this->addVariationInfoMessage();
        return parent::indexAction();
    }

    /**
     * Alter action form
     *
     * @param \Zend\Form\Form $form
     */
    public function alterActionForm(Form $form)
    {
        $form = parent::alterActionForm($form);

        // invoke trait aliased method
        $this->commonAlterActionForm($form);

        $addressElement = $form->get('address');

        $helper = $this->getServiceLocator()
            ->get('Helper\Form');
        $helper->disableElements($addressElement);
        $helper->disableValidation($form->getInputFilter()->get('address'));

        $lockedElements = array(
            $addressElement->get('searchPostcode'),
            $addressElement->get('addressLine1'),
            $addressElement->get('town'),
            $addressElement->get('postcode'),
            $addressElement->get('countryCode'),
        );

        foreach ($lockedElements as $element) {
            $helper->lockElement($element, 'operating-centre-address-requires-variation');
        }

        return $form;
    }

    /**
     * Alter the form
     *
     * @TODO should live in the licence trait, but calls parent... so needs refactoring
     *
     * @param \Zend\Form\Form $form
     * @return \Zend\Form\Form
     */
    public function alterForm(Form $form)
    {
        /*
        $form = $this->getLicenceSectionService()->alterForm($form);
         */

        $form = parent::alterForm($form);

        $data = $this->getTotalAuthorisationsForLicence($this->getIdentifier());

        $filter = $form->getInputFilter();

        foreach (['vehicles', 'trailers'] as $which) {
            $key = 'totAuth' . ucfirst($which);

            if ($filter->get('data')->has($key)) {
                $this->attachCantIncreaseValidator(
                    $filter->get('data')->get($key),
                    'total-' . $which,
                    $data[$key]
                );
            }
        }

        return $form;
    }

    /**
     * Remove the advertisements fieldset and the confirmation checkboxes
     *
     * @param \Zend\Form\Form $form
     */
    protected function alterActionFormForGoods(Form $form)
    {
        parent::alterActionFormForGoods($form);

        $form->remove('advertisements')
            ->get('data')
            ->remove('sufficientParking')
            ->remove('permission');
    }
}
