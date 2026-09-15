<?php

namespace Common\FormService\Form\Lva;

use Common\Service\Helper\FormHelperService;

/**
 * Safety Form
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class Safety
{
    public function __construct(protected FormHelperService $formHelper)
    {
    }

    /**
     * Returns form
     *
     * @return \Laminas\Form\Form
     */
    public function getForm()
    {
        return $this->formHelper->createForm('Lva\Safety');
    }
}
