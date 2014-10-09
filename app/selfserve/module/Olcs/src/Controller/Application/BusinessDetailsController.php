<?php

/**
 * Business Details Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Application;

use Olcs\View\Model\Section;
use Zend\Form\Form;

/**
 * Business Details Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class BusinessDetailsController extends AbstractApplicationController
{
    /**
     * Business details section
     */
    public function indexAction()
    {
        $form = $this->getHelperService('FormHelper')
            ->createForm('Lva\BusinessDetails')
            ->setData([]);

        $table = $this->getServiceLocator()
            ->get('Table')
            ->buildTable(
                'application_your-business_business_details-subsidiaries',
                array(), // @TODO data...
                array(), // params?
                false
            );

        $form->get('table')
            ->get('table')
            ->setTable($table);

        return new Section(
            array(
                'title' => 'Business details',
                'form' => $form
            )
        );
    }
}
