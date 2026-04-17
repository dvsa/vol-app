<?php

namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\Undertakings as CommonUndertakings;
use Common\Service\Helper\FormHelperService;

/**
 * Undertakings Form
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class Undertakings extends CommonUndertakings
{
    protected FormHelperService $formHelper;

    public function __construct(FormHelperService $formHelper)
    {
        parent::__construct($formHelper);
    }

    /**
     * Make form alterations
     *
     * @param \Laminas\Form\Form $form
     * @return \Laminas\Form\Form
     */
    #[\Override]
    protected function alterForm($form)
    {
        parent::alterForm($form);

        $form->get('form-actions')->get('save')->setLabel('internal.save.button');

        $this->formHelper->remove($form, 'interim');

        return $form;
    }
}
