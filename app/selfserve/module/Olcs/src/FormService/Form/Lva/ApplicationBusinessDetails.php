<?php

namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\BusinessDetails\ApplicationBusinessDetails as CommonApplicationBusinessDetails;
use Laminas\ServiceManager\ServiceLocatorAwareInterface;
use Laminas\ServiceManager\ServiceLocatorAwareTrait;
use Olcs\FormService\Form\Lva\Traits\ButtonsAlterations;
use Common\Form\Form;

/**
 * Application Business Details Form
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationBusinessDetails extends CommonApplicationBusinessDetails implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait,
        ButtonsAlterations;

    /**
     * Alter form
     *
     * @param Form  $form   form
     * @param array $params params
     */
    public function alterForm($form, $params)
    {
        parent::alterForm($form, $params);

        $this->getFormHelper()->remove($form, 'allow-email');

        // if we have got any in force licences or submitted licence application lock the elements down
        if ($params['hasInforceLicences'] || ($params['hasOrganisationSubmittedLicenceApplication'] ?? false)) {
            $this->getFormServiceLocator()->get('lva-lock-business_details')->alterForm($form);
        }
        $this->alterButtons($form);
    }
}
