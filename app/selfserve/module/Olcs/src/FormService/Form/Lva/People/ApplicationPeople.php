<?php

namespace Olcs\FormService\Form\Lva\People;

use Common\Form\Elements\InputFilters\Lva\BackToApplicationActionLink;
use Common\FormService\Form\Lva\People\ApplicationPeople as CommonApplicationPeople;
use Olcs\FormService\Form\Lva\Traits\ButtonsAlterations;

/**
 * Application People
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationPeople extends CommonApplicationPeople
{
    use ButtonsAlterations;

    public function alterForm($form)
    {
        $form = parent::alterForm($form);

        $this->alterButtons($form);

        return $form;
    }
}
