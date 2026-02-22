<?php

namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\Safety;
use Common\Service\Helper\FormHelperService;
use Olcs\FormService\Form\Lva\Traits\ButtonsAlterations;

/**
 * Application safety
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ApplicationSafety extends Safety
{
    use ButtonsAlterations;

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
        $form = $this->formHelper->createForm('Lva\Safety');

        $this->alterButtons($form);

        return $form;
    }
}
