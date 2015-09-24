<?php

/**
 * Licence People
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\FormService\Form\Lva\People;

use Common\Form\Elements\InputFilters\Lva\BackToLicenceActionLink;
use Common\FormService\Form\Lva\People\LicencePeople as CommonLicencePeople;

/**
 * Licence People
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicencePeople extends CommonLicencePeople
{
    public function alterForm($form)
    {
        $form = parent::alterForm($form);

        $this->addBackToOverviewLink($form, 'licence');

        return $form;
    }
}
