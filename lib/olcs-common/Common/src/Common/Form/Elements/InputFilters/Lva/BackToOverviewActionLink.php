<?php

/**
 * Back To Overview Action Link
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Form\Elements\InputFilters\Lva;

use Common\Form\Elements\InputFilters\ActionLink;

/**
 * Back To Overview Action Link
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class BackToOverviewActionLink extends ActionLink
{
    protected $lva;

    public function __construct()
    {
        $this->setName('back-to-' . $this->lva);
        $this->setOptions(
            [
                'route' => 'lva-' . $this->lva,
                'label' => 'back-to-' . $this->lva . '-overview'
            ]
        );
        $this->setAttribute('class', 'govuk-button');
    }
}
