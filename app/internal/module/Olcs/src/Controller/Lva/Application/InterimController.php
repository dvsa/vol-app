<?php

/**
 * Internal Application Interim Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\Lva\Application;

use Olcs\Controller\Interfaces\ApplicationControllerInterface;
use Olcs\Controller\Lva\AbstractInterimController;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;

use Common\Service\Entity\ApplicationEntityService;

/**
 * Internal Application Interim Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 * @author Joshua Curtis <josh.curtis@valtech.co.uk>
 */
class InterimController extends AbstractInterimController implements ApplicationControllerInterface
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected $location = 'internal';

    /**
     * Alter the form to add a reprint button if the interim status is
     * in-force.
     *
     * @param \Olcs\Controller\Lva\Zend\Form\Form $form
     * @param $application
     *
     * @return \Olcs\Controller\Lva\Zend\Form\Form
     */
    public function alterForm($form, $application)
    {
        if (!($application['interimStatus']['id'] == ApplicationEntityService::INTERIM_STATUS_INFORCE)) {
            $form->get('form-actions')->remove('reprint');
        }

        return $form;
    }
}
