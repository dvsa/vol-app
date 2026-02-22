<?php

namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\FinancialHistory as CommonFinancialHistory;
use Common\Form\Form;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;

/**
 * FinancialHistory Form
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class FinancialHistory extends CommonFinancialHistory
{
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
     * @param Form  $form Form
     * @param array $data Parameters for form
     *
     * @return \Laminas\Form\Form
     */
    #[\Override]
    protected function alterForm(Form $form, array $data = [])
    {
        parent::alterForm($form, $data);

        $form->get('form-actions')->get('save')->setLabel('internal.save.button');

        return $form;
    }
}
