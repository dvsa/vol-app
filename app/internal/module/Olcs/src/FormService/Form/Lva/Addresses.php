<?php

namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\Addresses as CommonAddresses;
use Common\FormService\Form\Lva\SmsNumberRequiredTrait;
use Common\Service\Cqrs\Query\QuerySender;
use Common\Service\Helper\FormHelperService;

/**
 * Addresses Form
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class Addresses extends CommonAddresses
{
    use SmsNumberRequiredTrait;

    protected FormHelperService $formHelper;

    public function __construct(FormHelperService $formHelper, private readonly QuerySender $querySender)
    {
        parent::__construct($formHelper);
    }

    /**
     * Alter Form
     *
     * @param \Laminas\Form\Form $form   Form
     * @param array           $params Params
     *
     * @return void
     */
    #[\Override]
    protected function alterForm(\Laminas\Form\Form $form, array $params)
    {
        $form->get('form-actions')->get('save')->setLabel('internal.save.button');

        parent::alterForm($form, $params);

        // optional for caseworkers once capture is on, and the hint is written for operators
        if ($this->keepSmsNumberOnlyWithCapture($form, $this->querySender)) {
            $contactFieldset = $form->get('contact');
            if ($contactFieldset->has('phone_sms')) {
                $contactFieldset->get('phone_sms')->setOption('hint', null);
            }
        }

        $contact = $form->getInputFilter()->get('contact');

        //  change email settings
        /** @var \Laminas\InputFilter\Input $emailElm */
        $emailElm = $contact->get('email');
        $emailElm->setRequired(false);

        $phonePrimary = $contact->get('phone_primary');
        $phonePrimary->setRequired(false);
    }
}
