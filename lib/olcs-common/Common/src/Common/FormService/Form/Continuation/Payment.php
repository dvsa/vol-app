<?php

namespace Common\FormService\Form\Continuation;

use Common\Form\Form;
use Common\Form\Model\Form\Continuation\Payment as PaymentForm;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\GuidanceHelperService;

/**
 * Continuation fee payment form
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class Payment
{
    public function __construct(protected FormHelperService $formHelper, private GuidanceHelperService $guidanceHelper)
    {
    }

    /**
     * Get form
     *
     * @param array $data continuation detail data
     *
     * @return Form
     */
    public function getForm($data)
    {
        $form = $this->formHelper->createForm(PaymentForm::class);

        $this->alterForm($form, $data);

        return $form;
    }

    /**
     * Alter form
     *
     * @param Form  $form form
     * @param array $data data
     *
     * @return void
     */
    protected function alterForm($form, $data)
    {
        if (isset($data['disableCardPayments']) && $data['disableCardPayments'] === true) {
            $formActions = $form->get('form-actions');
            $formActions->remove('pay');
            $cancelButton = $form->get('form-actions')->get('cancel');
            $cancelButton->setLabel('back-to-fees');
            $cancelButton->setAttribute('class', 'govuk-button govuk-button--secondary');
            $this->guidanceHelper->append('selfserve-card-payments-disabled');
        }
    }
}
