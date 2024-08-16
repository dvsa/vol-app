<?php

namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\FinancialHistory;
use Common\Form\Form;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Olcs\FormService\Form\Lva\Traits\ButtonsAlterations;

/**
 * Application financial history
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ApplicationFinancialHistory extends FinancialHistory
{
    use ButtonsAlterations;

    protected TranslationHelperService $translator;
    protected FormHelperService $formHelper;

    public function __construct(
        FormHelperService $formHelper,
        TranslationHelperService $translator
    ) {
        parent::__construct($formHelper, $translator);
    }

    /**
     * Make form alterations
     *
     * @param Form  $form form
     * @param array $data Form data
     *
     * @return Form
     */
    protected function alterForm(Form $form, array $data = [])
    {
        parent::alterForm($form, $data);
        $this->alterButtons($form);

        return $form;
    }
}
