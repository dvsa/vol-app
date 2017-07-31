<?php

namespace Olcs\FormService\Form\Lva\People;

use Common\FormService\Form\Lva\People\ApplicationPeople as CommonApplicationPeople;
use Olcs\FormService\Form\Lva\Traits\ButtonsAlterations;
use Common\Form\Form;
use Common\Form\Elements\Validators\TableRequiredValidator;

/**
 * Application People
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationPeople extends CommonApplicationPeople
{
    use ButtonsAlterations;

    /**
     * Alter form
     *
     * @param Form  $form   Form
     * @param array $params Parameters for form
     *
     * @return Form
     */
    public function alterForm(Form $form, array $params = [])
    {
        parent::alterForm($form, $params);

        if (isset($params['canModify']) && $params['canModify'] === false) {
            $form->get('form-actions')->get('save')->setLabel('lva.external.return.link');
            $form->get('form-actions')->get('save')->removeAttribute('class');
            $form->get('form-actions')->get('save')->setAttribute('class', 'action--tertiary large');
        } else {
            $this->alterButtons($form);
        }

        if ($params['isPartnership']) {
            $formHelper = $this->getFormHelper();
            $formHelper->removeValidator($form, 'table->rows', TableRequiredValidator::class);
            $validator = new TableRequiredValidator(['rowsRequired' => 2]);
            $validator->setMessage('people.partnership.validation-message', 'required');
            $formHelper->attachValidator($form , 'table->rows', $validator);
        }
    }
}
