<?php

namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\LicenceHistory;
use Common\Service\Helper\FormHelperService;
use Laminas\Form\Form;
use Olcs\FormService\Form\Lva\Traits\ButtonsAlterations;

/**
 * Application licence history
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ApplicationLicenceHistory extends LicenceHistory
{
    use ButtonsAlterations;

    protected FormHelperService $formHelper;

    public function __construct(FormHelperService $formHelper)
    {
        parent::__construct($formHelper);
    }

    /**
     * Make form alterations
     *
     * @param Form $form form
     *
     * @return Form
     */
    #[\Override]
    protected function alterForm($form)
    {
        parent::alterForm($form);
        $this->alterButtons($form);

        return $form;
    }
}
