<?php

namespace Common\FormService\Form\Lva;

use Common\Service\Helper\FormHelperService;

/**
 * Licence History Form
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class LicenceHistory
{
    public function __construct(protected FormHelperService $formHelper)
    {
    }

    public function getForm()
    {
        $form = $this->formHelper->createForm('Lva\LicenceHistory');

        $this->alterForm($form);

        return $form;
    }

    /**
     * Make form alterations
     *
     * @param \Laminas\Form\Form $form
     * @return \Laminas\Form\Form
     */
    protected function alterForm($form)
    {
        return $form;
    }
}
