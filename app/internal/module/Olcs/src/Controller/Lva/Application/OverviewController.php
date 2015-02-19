<?php

/**
 * Application Overview Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Olcs\Controller\Lva\Application;

use Zend\View\Model\ViewModel;
use Common\Controller\Lva\AbstractController;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;
use Zend\Form\Element\Select as SelectElement;

/**
 * Application Overview Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class OverviewController extends AbstractController
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected $location = 'internal';

    /**
     * Application overview
     */
    public function indexAction()
    {
        $applicationId = $this->getApplicationId();

        $form = $this->getTrackingForm($applicationId);
        $this->alterForm($form);

        $trackingData = $this->getServiceLocator()->get('Entity\ApplicationTracking')
            ->getTrackingStatuses($applicationId);

        $form->setData($this->formatTrackingDataForForm($trackingData));

        return $this->render('overview', $form, []);
    }

    protected function getTrackingForm($applicationId)
    {
        return $this->getServiceLocator()->get('Helper\Form')
            ->createForm('ApplicationOverview');
    }

    protected function formatTrackingDataForForm($data)
    {
        return ['tracking' => $data];
    }

    protected function alterForm($form)
    {
        $fieldset = $form->get('tracking');

        $stringHelper = $this->getServiceLocator()->get('Helper\String');

        // build up the tracking fieldset dynamically, based on relevant sections
        $sections = $this->getAccessibleSections();
        $options  = $this->getServiceLocator()->get('Entity\ApplicationTracking')->getValueOptions();
        foreach ($sections as $section) {
            $selectProperty = lcfirst($stringHelper->underscoreToCamel($section)) . 'Status';
            $select = new SelectElement($selectProperty);
            $select->setValueOptions($options);
            $select->setLabel('section.name.'.$section);
            $fieldset->add($select);
        }

        // modify label (it should be 'Save' not 'Save & return' as per AC)
        $form->get('form-actions')->get('save')->setLabel('Save');

        return $form;
    }
}
