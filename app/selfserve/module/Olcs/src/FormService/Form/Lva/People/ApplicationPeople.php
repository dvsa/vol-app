<?php

/**
 * Application People
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\FormService\Form\Lva\People;

use Common\Form\Elements\InputFilters\Lva\BackToApplicationActionLink;
use Common\FormService\Form\Lva\People\ApplicationPeople as CommonApplicationPeople;

/**
 * Application People
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationPeople extends CommonApplicationPeople
{
    public function alterForm($form)
    {
        $form = parent::alterForm($form);

        $this->addBackToOverviewLink($form, 'application', false);

        return $form;
    }
}
