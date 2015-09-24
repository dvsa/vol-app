<?php

/**
 * Variation People
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\FormService\Form\Lva\People;

use Common\Form\Elements\InputFilters\Lva\BackToVariationActionLink;
use Common\FormService\Form\Lva\People\VariationPeople as CommonVariationPeople;

/**
 * Variation People
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationPeople extends CommonVariationPeople
{
    public function alterForm($form)
    {
        $form = parent::alterForm($form);

        $this->addBackToOverviewLink($form, 'variation');

        return $form;
    }
}
