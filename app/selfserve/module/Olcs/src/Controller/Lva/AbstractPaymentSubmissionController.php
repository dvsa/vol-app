<?php

namespace Olcs\Controller\Lva;

use Common\Controller\Lva\AbstractController;
use Common\Controller\Traits\GenericReceipt;
use Common\Exception\BadRequestException;
use Common\Exception\ResourceNotFoundException;
use Common\RefData;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Command\Application\SubmitApplication as SubmitApplicationCmd;
use Dvsa\Olcs\Transfer\Command\Transaction\CompleteTransaction as CompletePaymentCmd;
use Dvsa\Olcs\Transfer\Command\Transaction\PayOutstandingFees as PayOutstandingFeesCmd;
use Dvsa\Olcs\Transfer\Command\Variation\Grant as GrantVariationCmd;
use Dvsa\Olcs\Transfer\Query\Application\Application as ApplicationQry;
use Dvsa\Olcs\Transfer\Query\Application\OutstandingFees;
use Dvsa\Olcs\Transfer\Query\Transaction\Transaction as PaymentByIdQry;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\View\Model\ViewModel;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * External Abstract Payment Submission Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
abstract class AbstractPaymentSubmissionController extends AbstractController
{
    use GenericReceipt;

    protected const PAYMENT_METHOD = RefData::FEE_PAYMENT_METHOD_CARD_ONLINE;

    protected $lva;
    protected string $location = 'external';
    protected $disableCardPayments = false;

    protected TranslationHelperService $translationHelper;
    protected TableFactory $tableFactory;

    /**
     * @param NiTextTranslation $niTextTranslationUtil
     * @param AuthorizationService $authService
     * @param TranslationHelperService $translationHelper
     * @param FlashMessengerHelperService $flashMessengerHelper
     * @param TableFactory $tableFactory
     * @param FormHelperService $formHelper
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        TranslationHelperService $translationHelper,
        protected FlashMessengerHelperService $flashMessengerHelper,
        TableFactory $tableFactory,
        protected FormHelperService $formHelper
    ) {
        $this->translationHelper = $translationHelper;
        $this->tableFactory = $tableFactory;

        parent::__construct($niTextTranslationUtil, $authService);
    }

    /**
     * Index action
     *
     * @return \Laminas\View\Model\ViewModel|\Laminas\Http\Response
     * @psalm-suppress UndefinedDocblockClass
     */
    public function indexAction()
    {
        $applicationId = $this->getApplicationId();

        // bail out if we don't have an application id
        if (empty($applicationId)) {
            throw new BadRequestException('Invalid payment submission request');
        }

        $redirectUrl = $this->url()->fromRoute(
            'lva-' . $this->lva . '/result',
            ['action' => 'payment-result'],
            ['force_canonical' => true],
            true
        );

        $dtoData = [
            'cpmsRedirectUrl' => $redirectUrl,
            'applicationId' => $applicationId,
            'paymentMethod' => self::PAYMENT_METHOD
        ];
        $dto = PayOutstandingFeesCmd::create($dtoData);
        $response = $this->handleCommand($dto);

        $messages = $response->getResult()['messages'];

        $translateHelper = $this->translationHelper;
        $errorMessage = '';
        foreach ($messages as $message) {
            if (is_array($message) && array_key_exists(RefData::ERR_WAIT, $message)) {
                $errorMessage = $translateHelper->translate('payment.error.15sec');
                break;
            } elseif (is_array($message) && array_key_exists(RefData::ERR_NO_FEES, $message)) {
                $errorMessage = $translateHelper->translate('payment.error.feepaid');
                break;
            }
        }
        if ($errorMessage !== '') {
            $this->addErrorMessage($errorMessage);
            return $this->redirectToOverview();
        }

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

    /**
     * Submit application
     *
     * @param int $applicationId Application id
     * @param int $version       Version
     *
     * @return \Laminas\Http\Response
     */
    protected function submitApplication($applicationId, $version)
    {
        // Submit the application (changes status to "Under Consideration")
        $dto = SubmitApplicationCmd::create(
            [
                'id' => $applicationId,
                'version' => $version,
            ]
        );

        $response = $this->handleCommand($dto);

        if (!$response->isOk()) {
            $this->flashMessengerHelper->addUnknownError();
            return $this->redirectToOverview();
        }

        // Check if this variation qualifies for auto-grant
        if ($this->lva === 'variation') {
            $autoGrantEligibility = $this->checkAutoGrantEligibility($applicationId);

            if ($autoGrantEligibility['eligible']) {

                $grantResponse = $this->autoGrantVariation($applicationId);

                if ($grantResponse->isOk()) {
                    return $this->redirectToOverview();
                }

                error_log(
                    'Auto-grant failed for variation ' . $applicationId .
                    ': ' . print_r($grantResponse->getResult(), true)
                );

            }
        }

        // Normal flow: redirect to summary
        return $this->redirectToSummary();
    }

    /**
     * Check if variation is eligible for auto-grant
     *
     * @param int $applicationId
     * @return array ['eligible' => bool, 'reason' => string]
     */
    protected function checkAutoGrantEligibility($applicationId)
    {
        try {
            // Fetch application data to check if it's a variation
            $appQuery = ApplicationQry::create(['id' => $applicationId]);
            $appResponse = $this->handleQuery($appQuery);

            if (!$appResponse->isOk()) {
                return ['eligible' => false, 'reason' => 'Failed to fetch application data'];
            }

            $application = $appResponse->getResult();

            // Must be a variation
            if (!isset($application['isVariation']) || !$application['isVariation']) {
                return ['eligible' => false, 'reason' => 'Not a variation'];
            }

            // Check which sections have been updated using variationCompletion
            $variationCompletion = $application['variationCompletion'] ?? [];

            $hasOperatingCentreChanges = false;
            $hasOtherSectionChanges = false;

            foreach ($variationCompletion as $sectionKey => $status) {
                // Skip undertakings section (always marked as updated when submitting)
                if ($sectionKey === 'undertakings') {
                    continue;
                }

                if ($status == RefData::VARIATION_STATUS_UPDATED) {
                    if ($sectionKey === 'operating_centres') {
                        $hasOperatingCentreChanges = true;
                    } else {
                        $hasOtherSectionChanges = true;
                    }
                }
            }

            // Only operating centres should have changes
            if (!$hasOperatingCentreChanges) {
                return ['eligible' => false, 'reason' => 'No operating centre changes'];
            }

            if ($hasOtherSectionChanges) {
                return ['eligible' => false, 'reason' => 'Other sections have changes'];
            }

            // Fetch operating centres data
            $ocQuery = \Dvsa\Olcs\Transfer\Query\Application\OperatingCentres::create([
                'id' => $applicationId,
                'sort' => 'id',
                'order' => 'ASC'
            ]);
            $ocResponse = $this->handleQuery($ocQuery);

            if (!$ocResponse->isOk()) {
                return ['eligible' => false, 'reason' => 'Failed to fetch operating centres data'];
            }

            $ocData = $ocResponse->getResult();
            $applicationOperatingCentres = $ocData['operatingCentres'] ?? [];

            // Analyze operating centre changes
            // For variations, OCs are marked with action flags:
            // 'E' = Existing (unchanged), 'D' = Deletion, 'A' = Addition, 'U' = Update
            $hasAdditions = false;
            $hasModifications = false;
            $removalCount = 0;
            $remainingCount = 0;

            foreach ($applicationOperatingCentres as $appOC) {
                $action = $appOC['action'] ?? null;

                if ($action === 'A') {
                    $hasAdditions = true;
                } elseif ($action === 'U') {
                    $hasModifications = true;
                } elseif ($action === 'D') {
                    $removalCount++;
                } elseif ($action === 'E' || $action === null) {
                    // Existing OC that remains unchanged
                    $remainingCount++;
                }
            }

            // Calculate initial count (before this variation)
            $initialOCCount = $remainingCount + $removalCount;

            // Validate all criteria
            if ($initialOCCount <= 1) {
                return ['eligible' => false, 'reason' => 'Must have more than 1 operating centre initially'];
            }

            if ($hasAdditions) {
                return ['eligible' => false, 'reason' => 'Contains operating centre additions'];
            }

            if ($hasModifications) {
                return ['eligible' => false, 'reason' => 'Contains operating centre modifications'];
            }

            if ($removalCount === 0) {
                return ['eligible' => false, 'reason' => 'No operating centres being removed'];
            }

            if ($remainingCount < 1) {
                return ['eligible' => false, 'reason' => 'No operating centres would remain'];
            }

            // All checks passed - eligible for auto-grant
            return ['eligible' => true, 'reason' => 'Eligible for auto-grant'];

        } catch (\Exception $e) {
            error_log('Error checking auto-grant eligibility: ' . $e->getMessage());
            return ['eligible' => false, 'reason' => 'Exception: ' . $e->getMessage()];
        }
    }

    /**
     * Auto-grant the variation
     *
     * The Grant command handler will:
     * - Automatically complete the tracking history
     * - Grant/process the application
     * - Add change history entries with VOL system user
     *
     * @param int $applicationId
     * @return \Common\Service\Cqrs\Response
     */
    protected function autoGrantVariation($applicationId)
    {
        try {
            // Fetch the application to get the current version
            $query = ApplicationQry::create(['id' => $applicationId]);
            $response = $this->handleQuery($query);

            if (!$response->isOk()) {
                return $response;
            }

            $application = $response->getResult();

            // Create and execute the grant command
            $dto = GrantVariationCmd::create([
                'id' => $applicationId,
                'version' => $application['version'],
                'grantAuthority' => RefData::GRANT_AUTHORITY_DELEGATED,
                'isAutoGrant' => true,
            ]);

            $grantResponse = $this->handleCommand($dto);

            return $grantResponse;

        } catch (\Exception $e) {
            error_log('Error auto-granting variation: ' . $e->getMessage());

            // Create a failed response using the command service's response factory
            // This ensures we return the correct type
            $result = new \Dvsa\Olcs\Api\Domain\Command\Result();
            $result->addMessage('Auto-grant failed: ' . $e->getMessage());

            // Return a proper failed response
            // The handleCommand method returns a Response, so we need to match that
            // In case of error, we'll just return a generic error and let normal flow continue
            $this->flashMessengerHelper->addErrorMessage('Auto-grant encountered an error');

            // Return a mock response that indicates failure
            // Since we can't easily create a proper Response here, just rethrow
            throw $e;
        }
    }

    /**
     * Handle response from third-party payment gateway
     *
     * @return \Laminas\Http\Response
     */
    public function paymentResultAction()
    {
        $applicationId = $this->getApplicationId();

        $queryStringData = (array)$this->getRequest()->getQuery();
        $reference = $queryStringData['receipt_reference'] ?? null;

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

    /**
     * Redirect to summary
     *
     * @param string $ref Reference
     *
     * @return \Laminas\Http\Response
     */
    protected function redirectToSummary($ref = null)
    {
        return $this->redirect()->toRoute(
            'lva-' . $this->lva . '/summary',
            [
                'application' => $this->getApplicationId(),
                'reference' => $ref
            ]
        );
    }

    /**
     * Redirect to overview
     *
     * @return \Laminas\Http\Response
     */
    protected function redirectToOverview()
    {
        return $this->redirect()->toRoute(
            'lva-' . $this->lva,
            ['application' => $this->getApplicationId()]
        );
    }

    /**
     * Display stored cards form
     *
     * @return \Laminas\View\Model\ViewModel|\Laminas\Http\Response
     */
    public function payAndSubmitAction()
    {
        $applicationId = $this->getApplicationId();
        if (empty($applicationId)) {
            throw new BadRequestException('Invalid payment submission request');
        }
        $post = (array) $this->getRequest()->getPost();

        if ($this->isButtonPressed('customCancel')) {
            $back = $this->params()->fromRoute('redirect-back', 'overview');
            if ($back === 'undertakings') {
                return $this->redirect()->toRouteAjax(
                    'lva-' . $this->lva . '/undertakings',
                    [$this->getIdentifierIndex() => $applicationId]
                );
            }
            return $this->gotoOverview();
        }

        $fees = $this->getOutstandingFeeDataForApplication($applicationId);
        if (empty($fees) || $this->disableCardPayments) {
            $postData = (array) $this->getRequest()->getPost();
            return $this->submitApplication(
                $applicationId,
                !empty($postData['version']) ? $postData['version'] : null
            );
        }

        if (isset($post['form-actions']['pay'])) {
            /*
             * If pay POST param exists that mean we are on 2nd step
             * so we need to redirect to the index action which do all
             * the logic for the payment and app/var submission
             */
            $params = [
                'action' => 'index',
                $this->getIdentifierIndex() => $applicationId,
            ];
            return $this->redirect()->toRoute('lva-' . $this->lva . '/payment', $params);
        }

        /* @var $form \Common\Form\Form */
        $form = $this->formHelper->createForm('FeePayment');

        return $this->getStoredCardsView($fees, $form);
    }

    /**
     * Get stored cards view
     *
     * @param array             $fees Fees
     * @param \Common\Form\Form $form Form
     *
     * @return ViewModel
     */
    protected function getStoredCardsView($fees, $form)
    {
        if (count($fees) > 1) {
            $table = $this->tableFactory
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
     * @param int $applicationId Application id
     *
     * @return array
     * @throw ResourceNotFoundException
     */
    protected function getOutstandingFeeDataForApplication($applicationId)
    {
        $query = OutstandingFees::create(['id' => $applicationId, 'hideExpired' => true]);
        $response = $this->handleQuery($query);
        if (!$response->isOk()) {
            throw new ResourceNotFoundException('Error getting outstanding fees');
        }
        $result = $response->getResult();
        $this->disableCardPayments = $result['disableCardPayments'];
        return $result['outstandingFees'];
    }
}
