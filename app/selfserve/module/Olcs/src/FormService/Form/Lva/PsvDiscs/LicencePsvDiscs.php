<?php

/**
 * Licence Psv Discs
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\FormService\Form\Lva\PsvDiscs;

use Common\FormService\Form\Lva\PsvDiscs as CommonPsvDiscs;

/**
 * Licence Psv Discs
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicencePsvDiscs extends CommonPsvDiscs
{
    protected function alterForm($form)
    {
        $form = parent::alterForm($form);

        $this->addBackToOverviewLink($form, 'licence');
    }
}
