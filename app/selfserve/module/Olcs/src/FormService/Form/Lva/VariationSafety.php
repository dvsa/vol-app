<?php

namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\Safety as CommonSafety;
use Common\Service\Helper\FormHelperService;

/**
 * Variation safety
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class VariationSafety extends CommonSafety
{
    protected FormHelperService $formHelper;

    public function __construct(FormHelperService $formHelper)
    {
        parent::__construct($formHelper);
    }

    /**
     * Returns form
     *
     * @return \Laminas\Form\FormInterface
     */
    #[\Override]
    public function getForm()
    {
        $form = parent::getForm();

        $this->formHelper->remove($form, 'form-actions->cancel');

        return $form;
    }
}
