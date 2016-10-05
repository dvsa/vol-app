<?php

namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\AbstractFormService;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Abstract class to create submission form at LVA Overview page
 *
 * @author Dmitry Golubev <dmitrij.golubev@valtech.co.uk>
 */
class AbstractOverviewSubmission extends AbstractFormService implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /** @var array */
    protected $sections = [];

    /**
     * Get Form
     *
     * @param array $data   Api/Form data
     * @param array $params Form parameters
     *
     * @return \Zend\Form\Form
     */
    public function getForm($data, $params)
    {
        $this->sections = $params['sections'];

        $form = $this->getFormHelper()->createForm('Lva\PaymentSubmission');
        $this->alterForm($form, $data, $params);

        return $form;
    }

    /**
     * Make changes in Submit and Payment form
     *
     * @param \Zend\Form\FormInterface $form   Form
     * @param array                    $data   Api data
     * @param array                    $params Parameters
     *
     * @return void
     */
    protected function alterForm(\Zend\Form\FormInterface $form, array $data, array $params)
    {
        /** @var \Common\Service\Helper\FormHelperService $formHelper */
        $formHelper = $this->getFormHelper();

        $elmBtnSubmit = $form->get('submitPay');

        //
        $fee = (float)$data['outstandingFeeTotal'];
        if ($fee > 0) {
            // show fee amount
            $form->get('amount')->setValue(
                $this->getServiceLocator()->get('Helper\Translation')
                    ->translateReplace(
                        'application.payment-submission.amount.value',
                        [number_format($fee, 2)]
                    )
            );
        } else {
            $formHelper->remove($form, 'amount');

            // if no fee, change submit button text
            $elmBtnSubmit->setLabel('submit-application.button');
        }

        // if card payments disabled, change button label
        $isDisabledCardPayments = (bool)$data['disableCardPayments'];
        if ($isDisabledCardPayments) {
            $elmBtnSubmit->setLabel('submit-application.button');
        }

        // note, we don't set an action on the form if we're disabling
        if ($params['isReadyToSubmit']) {
            $form->setAttribute('action', $params['actionUrl']);
        } else {
            $formHelper->disableElement($form, 'submitPay');
        }
    }

    /**
     * Has any section specified status
     *
     * @param string $status Status
     *
     * @return bool
     */
    protected function hasSectionsWithStatus($status)
    {
        foreach ($this->sections as $section) {
            if ($section['status'] === $status) {
                return true;
            }
        }

        return false;
    }
}
