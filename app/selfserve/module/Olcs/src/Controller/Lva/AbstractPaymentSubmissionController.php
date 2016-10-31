<?php

/**
 * External Abstract Payment Submission Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\Lva;

use Common\Controller\Lva\AbstractController;
use Common\RefData;
use Zend\View\Model\ViewModel;
use Common\Exception\BadRequestException;
use Common\Exception\ResourceNotFoundException;
use Dvsa\Olcs\Transfer\Command\Transaction\PayOutstandingFees as PayOutstandingFeesCmd;
use Dvsa\Olcs\Transfer\Command\Transaction\CompleteTransaction as CompletePaymentCmd;
use Dvsa\Olcs\Transfer\Command\Application\SubmitApplication as SubmitApplicationCmd;
use Dvsa\Olcs\Transfer\Query\Transaction\Transaction as PaymentByIdQry;
use Dvsa\Olcs\Transfer\Query\Application\OutstandingFees;
use Common\Controller\Traits\GenericReceipt;
use Olcs\Controller\Lva\Traits\StoredCardsTrait;

/**
 * External Abstract Payment Submission Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
abstract class AbstractPaymentSubmissionController extends AbstractController
{
    use GenericReceipt,
        StoredCardsTrait;

    const PAYMENT_METHOD = RefData::FEE_PAYMENT_METHOD_CARD_ONLINE;

    protected $lva;
    protected $location = 'external';
    protected $disableCardPayments = false;

    public function indexAction()
    {
        $applicationId = $this->getApplicationId();

        // bail out if we don't have an application id
        if (empty($applicationId)) {
            throw new BadRequestException('Invalid payment submission request');
        }

        $redirectUrl = $this->url()->fromRoute(
            'lva-'.$this->lva.'/result',
            ['action' => 'payment-result'],
            ['force_canonical' => true],
            true
        );

        $dtoData = [
            'cpmsRedirectUrl' => $redirectUrl,
            'applicationId' => $applicationId,
            'paymentMethod' => self::PAYMENT_METHOD,
            'storedCardReference' => $this->params()->fromRoute('storedCardReference', null)
        ];
        $dto = PayOutstandingFeesCmd::create($dtoData);
        $response = $this->handleCommand($dto);

        if (!$response->isOk()) {
            $this->addErrorMessage('feeNotPaidError');
            return $this->redirectToOverview();
        }

        if (empty($response->getResult()['id']['transaction'])) {
            // there were no fees to pay so we don't render the CPMS page
            $postData = (array) $this->getRequest()->getPost();
            return $this->submitApplication($applicationId, $postData['version']);
        }

        // Look up the new payment in order to get the redirect data
        $paymentId = $response->getResult()['id']['transaction'];
        $response = $this->handleQuery(PaymentByIdQry::create(['id' => $paymentId]));
        $payment = $response->getResult();
        $view = new ViewModel(
            [
                'gateway' => $payment['gatewayUrl'],
                'data' => [
                    'receipt_reference' => $payment['reference']
                ]
            ]
        );

        // render the gateway redirect
        $view->setTemplate('cpms/payment');
        return $this->render($view);
    }

    protected function submitApplication($applicationId, $version)
    {
        $dto = SubmitApplicationCmd::create(
            [
                'id' => $applicationId,
                'version' => $version,
            ]
        );

        $response = $this->handleCommand($dto);

        if ($response->isOk()) {
            return $this->redirectToSummary();
        }

        $this->getServiceLocator()->get('Helper\FlashMessenger')->addUnknownError();
        return $this->redirectToOverview();
    }

    /**
     * Handle response from third-party payment gateway
     */
    public function paymentResultAction()
    {
        $applicationId = $this->getApplicationId();

        $queryStringData = (array)$this->getRequest()->getQuery();
        $reference = isset($queryStringData['receipt_reference']) ? $queryStringData['receipt_reference'] : null;

        $dtoData = [
            'reference' => $reference,
            'cpmsData' => $queryStringData,
            'paymentMethod' => self::PAYMENT_METHOD,
            'submitApplicationId' => $applicationId,
        ];

        $response = $this->handleCommand(CompletePaymentCmd::create($dtoData));

        if (!$response->isOk()) {
            $this->addErrorMessage('payment-failed');
            return $this->redirectToOverview();
        }

        // check payment status and redirect accordingly
        $paymentId = $response->getResult()['id']['transaction'];
        $response = $this->handleQuery(PaymentByIdQry::create(['id' => $paymentId]));
        $payment = $response->getResult();
        switch ($payment['status']['id']) {
            case RefData::TRANSACTION_STATUS_COMPLETE:
                return $this->redirectToSummary($reference);
            case RefData::TRANSACTION_STATUS_CANCELLED:
                break;
            case RefData::TRANSACTION_STATUS_FAILED:
            default:
                $this->addErrorMessage('feeNotPaidError');
                break;
        }

        return $this->redirectToOverview();
    }

    protected function redirectToSummary($ref = null)
    {
        return $this->redirect()->toRoute(
            'lva-'.$this->lva.'/summary',
            [
                'application' => $this->getApplicationId(),
                'reference' => $ref
            ]
        );
    }

    protected function redirectToOverview()
    {
        return $this->redirect()->toRoute(
            'lva-'.$this->lva,
            ['application' => $this->getApplicationId()]
        );
    }

    /**
     * Display stored cards form
     */
    public function payAndSubmitAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->redirectToOverview();
        }

        $applicationId = $this->getApplicationId();
        if (empty($applicationId)) {
            throw new BadRequestException('Invalid payment submission request');
        }

        $fees = $this->getOutstandingFeeDataForApplication($applicationId);
        if (empty($fees) || $this->disableCardPayments) {
            $postData = (array) $this->getRequest()->getPost();
            return $this->submitApplication($applicationId, $postData['version']);
        }

        $post = (array) $this->getRequest()->getPost();
        if (isset($post['form-actions']['pay'])) {
            /*
             * If pay POST param exists that mean we are on 2nd step
             * so we need to redirect to the index action which do all
             * the logic for the payment and app/var submission
             */
            $storedCardReference =
                ($this->getRequest()->getPost('storedCards')['card'] !== '0') ?
                $this->getRequest()->getPost('storedCards')['card'] : false;

            $params = [
                'action' => 'index',
                $this->getIdentifierIndex() => $applicationId,
            ];
            if ($storedCardReference) {
                $params['storedCardReference'] = $storedCardReference;
            }
            return $this->redirect()->toRoute('lva-'.$this->lva.'/payment', $params);
        }

        /* @var $form \Common\Form\Form */
        $form = $this->getServiceLocator()->get('Helper\Form')->createForm('FeePayment');
        $this->setupSelectStoredCards($form);

        return $this->getStoredCardsView($fees, $form);
    }

    /**
     * Get stored cards view
     *
     * @param array $fees
     * @param \Common\Form\Form $form
     * @return View
     */
    protected function getStoredCardsView($fees, $form)
    {
        if (count($fees) > 1) {
            $table = $this->getServiceLocator()->get('Table')
                ->buildTable('pay-fees', $fees, [], false);
            $view = new ViewModel(
                [
                    'table' => $table,
                    'form' => $form,
                    'hasContinuation' => $this->hasContinuationFee($fees),
                    'type' => 'submit'
                ]
            );
            $view->setTemplate('pages/fees/pay-multi');
        } else {
            $fee = $fees[0];
            $view = new ViewModel(
                [
                    'fee' => $fee,
                    'form' => $form,
                    'hasContinuation' => $fee['feeType']['feeType']['id'] == RefData::FEE_TYPE_CONT,
                    'type' => 'submit'
                ]
            );
            $view->setTemplate('pages/fees/pay-one');
        }
        return $view;
    }

    /**
     * Get outstanding fees for application
     *
     * @param int $applicationId
     * @return array
     */
    protected function getOutstandingFeeDataForApplication($applicationId)
    {
        $query = OutstandingFees::create(['id' => $applicationId, 'hideExpired' => true]);
        $response = $this->handleQuery($query);
        if (!$response->isOk()) {
            throw new ResourceNotFoundException('Error getting outstaning fees');
        }
        $result = $response->getResult();
        $this->disableCardPayments = $result['disableCardPayments'];
        return $result['outstandingFees'];
    }
}
