<?php

namespace Common\Controller\Lva;

use Common\RefData;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Command\Application\Schedule41;
use Dvsa\Olcs\Transfer\Command\Application\Schedule41Approve;
use Dvsa\Olcs\Transfer\Command\Application\Schedule41Refuse;
use Dvsa\Olcs\Transfer\Command\Application\Schedule41Reset;
use Dvsa\Olcs\Transfer\Query\Licence\LicenceByNumber;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\Form\Form;
use Laminas\View\Model\ViewModel;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Review Controller
 *
 * @author Joshua Curtis <josh.curtis@valtech.co.uk>
 */
class Schedule41Controller extends AbstractController
{
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        protected FormHelperService $formHelper,
        protected TableFactory $tableFactory,
        protected FlashMessengerHelperService $flashMessengerHelper
    ) {
        parent::__construct($niTextTranslationUtil, $authService);
    }

    /**
     * Search for a licence by licence number.
     *
     * @return string|\Laminas\Http\Response|ViewModel
     */
    public function licenceSearchAction()
    {
        $request = $this->getRequest();
        $form = $this->formHelper
            ->createFormWithRequest(
                'Schedule41LicenceSearch',
                $request
            );

        if ($request->isPost()) {
            if ($this->isButtonPressed('cancel')) {
                return $this->redirect()->toRoute(
                    'lva-application/overview',
                    [
                        'application' => $this->params('application')
                    ]
                );
            }

            $form->setData((array)$request->getPost());

            if ($form->isValid()) {
                $valid = $this->isLicenceValid($request->getPost()['licence-number']['licenceNumber']);

                if ($valid === true) {
                    return $this->redirect()->toRoute(
                        'lva-application/schedule41/transfer',
                        [
                            'application' => $this->params('application'),
                            'licNo' => $form->getData()['licence-number']['licenceNumber']
                        ]
                    );
                }

                $this->mapLicenceSearchErrors($form, $valid);
            }
        }

        return $this->render('schedule41', $form);
    }

    /**
     * Transfer operating centres from a licence to and application.
     *
     * @return string|ViewModel
     */
    public function transferAction()
    {
        $request = $this->getRequest();
        $licence = $this->handleQuery(
            LicenceByNumber::create(
                [
                    'licenceNumber' => $this->params()->fromRoute('licNo', null)
                ]
            )
        )->getResult();

        if ($request->isPost()) {
            $postData = (array)$request->getPost();

            if (isset($postData['cancel'])) {
                return $this->redirect()->toRoute(
                    'lva-application/overview',
                    [
                        'application' => $this->params('application')
                    ]
                );
            }

            if (!isset($postData['table']['id'])) {
                $this->flashMessenger()
                    ->addErrorMessage('application.schedule41.no-rows-selected');

                return $this->redirect()->toRoute(
                    'lva-application/schedule41/transfer',
                    [
                        'application' => $this->params('application'),
                        'licNo' => $this->params('licNo')
                    ]
                );
            }

            $command = Schedule41::create(
                [
                    'id' => $this->getApplication()['id'],
                    'licence' => $licence['id'],
                    'operatingCentres' => $postData['table']['id'],
                    'surrenderLicence' => $postData['surrenderLicence']
                ]
            );

            $response = $this->handleCommand($command);

            if ($response->isOk()) {
                $this->flashMessenger()
                    ->addSuccessMessage('lva.section.title.schedule41.success');

                return $this->redirect()->toRouteAjax(
                    'lva-application/operating_centres',
                    [
                        'application' => $this->params('application')
                    ]
                );
            }
        }

        $form = $this->formHelper->createFormWithRequest('Schedule41Transfer', $request);
        $form->get('table')->get('table')->setTable(
            $this->getOcTable(
                $this->formatDataForTable($licence)
            )
        );

        return $this->render('schedule41', $form);
    }

    /**
     * Approve the registered schedule 4/1 request for the application.
     *
     */
    public function approveSchedule41Action()
    {
        $request = $this->getRequest();

        $application = $this->handleQuery(
            \Dvsa\Olcs\Transfer\Query\Application\Schedule41Approve::create(
                ['id' => $this->params('application')]
            )
        )->getResult();

        if (!empty($application['errors'])) {
            return $this->cannotPublish($application['errors']);
        }

        if ($application['isVariation']) {
            $form = $this->formHelper
                ->createFormWithRequest('VariationApproveSchedule41', $request);
        } else {
            $form = $this->formHelper
                ->createFormWithRequest('GenericConfirmation', $request);

            $form->get('messages')->get('message')->setValue('schedule41.approve.application.message');
        }

        $form->get('form-actions')->get('submit')->setLabel('Approve');

        if ($request->isPost()) {
            $form->setData((array)$request->getPost());
            if ($form->isValid()) {
                $data = $form->getData();

                $command = Schedule41Approve::create(
                    [
                        'id' => $application['id'],
                        'trueS4' => ($data['isTrueS4'] ?? null)
                    ]
                );

                $response = $this->handleCommand($command);

                if ($response->isOk()) {
                    $this->flashMessenger()
                        ->addSuccessMessage('lva.section.title.schedule41.approve.success');

                    return $this->redirect()->toRouteAjax(
                        'lva-application/overview',
                        [
                            'application' => $this->params('application')
                        ]
                    );
                }
            }
        }

        return $this->render('schedule41', $form);
    }

    /**
     * Get a form with the cannot publish validation messages
     *
     * @param array $errors Errors
     */
    private function cannotPublish($errors)
    {
        $formHelper = $this->formHelper;
        $form = $formHelper->createFormWithRequest('Message', $this->getRequest());
        $form->setMessage($errors);
        $form->removeOkButton();

        return $this->render(
            'publish_application_error',
            $form
        );
    }

    /**
     * Reset the registered schedule 4/1 request for the application.
     *
     * @return \Laminas\Http\Response|ViewModel
     */
    public function resetSchedule41Action()
    {
        $request = $this->getRequest();

        $form = $this->formHelper
            ->createFormWithRequest(
                'GenericConfirmation',
                $request
            );

        $form->get('messages')->get('message')->setValue('schedule41.reset.application.message');
        $form->get('form-actions')->get('submit')->setLabel('Reset');

        if ($request->isPost()) {
            $form->setData((array)$request->getPost());
            if ($form->isValid()) {
                $command = Schedule41Reset::create(
                    [
                        'id' => $this->params('application'),
                    ]
                );

                $response = $this->handleCommand($command);

                if ($response->isOk()) {
                    $this->flashMessenger()
                        ->addSuccessMessage('lva.section.title.schedule41.reset.success');

                    return $this->redirect()->toRouteAjax(
                        'lva-application/overview',
                        [
                            'application' => $this->params('application')
                        ]
                    );
                }
            }
        }

        return $this->render('schedule41', $form);
    }

    /**
     * Refuse the registered schedule 4/1 request for the application.
     *
     * @return \Laminas\Http\Response|ViewModel
     */
    public function refuseSchedule41Action()
    {
        $request = $this->getRequest();

        $form = $this->formHelper
            ->createFormWithRequest(
                'GenericConfirmation',
                $request
            );

        $form->get('messages')->get('message')->setValue('schedule41.refuse.application.message');
        $form->get('form-actions')->get('submit')->setLabel('Refuse');

        if ($request->isPost()) {
            $form->setData((array)$request->getPost());
            if ($form->isValid()) {
                $command = Schedule41Refuse::create(
                    [
                        'id' => $this->params('application'),
                    ]
                );

                $response = $this->handleCommand($command);

                if ($response->isOk()) {
                    $this->flashMessenger()
                        ->addSuccessMessage('lva.section.title.schedule41.refuse.success');

                    return $this->redirect()->toRouteAjax(
                        'lva-application/overview',
                        [
                            'application' => $this->params('application')
                        ]
                    );
                }
            }
        }

        return $this->render('schedule41', $form);
    }

    /**
     * Is the licence valid according to the Ac.
     *
     * @param string $licenceNumber Licence number
     *
     * @return array|bool
     */
    private function isLicenceValid($licenceNumber)
    {
        try {
            $response = $this->handleQuery(
                LicenceByNumber::create(['licenceNumber' => $licenceNumber])
            );
        } catch (\Common\Service\Cqrs\Exception\NotFoundException $e) {
            return ['number-not-valid' => ['application.schedule41.licence-number-not-valid']];
        }

        if (!$response->isOk()) {
            $errors['number-not-valid'][] = 'application.schedule41.licence-number-not-valid';

            return $errors;
        }

        $licence = $response->getResult();

        // Licence not valid.
        $allowed = [
            RefData::LICENCE_STATUS_VALID,
            RefData::LICENCE_STATUS_SUSPENDED,
            RefData::LICENCE_STATUS_CURTAILED
        ];

        $errors = [];
        if (!in_array($licence['status']['id'], $allowed)) {
            $errors['not-valid'][] = 'application.schedule41.licence-not-valid';
        }

        // Licence is PSV.
        if ($licence['goodsOrPsv']['id'] === RefData::LICENCE_CATEGORY_PSV) {
            $errors['is-psv'][] = 'application.schedule41.licence-is-psv';
        }

        if ($errors === []) {
            return true;
        }

        return $errors;
    }

    /**
     * Return a redirect response with an error message.
     *
     * @param string $message Message
     *
     * @return \Laminas\Http\Response
     */
    protected function redirectWithError($message)
    {
        $this->flashMessenger()
            ->addErrorMessage($message);

        return $this->redirect()->toRoute(
            'lva-application/schedule41',
            [
                'application' => $this->params('application')
            ]
        );
    }

    /**
     * Get the operating centres table for transferring.
     *
     * @param array $data Data
     *
     * @return mixed
     */
    public function getOcTable($data)
    {
        return $this->tableFactory
            ->prepareTable(
                'schedule41.operating-centres',
                $data
            );
    }

    /**
     * Format the operating centre data for the table.
     *
     * @param array $data Data
     *
     * @return array
     */
    public function formatDataForTable($data)
    {
        return array_map(
            static fn($operatingCentre) => [
                'id' => $operatingCentre['id'],
                'address' => $operatingCentre['operatingCentre']['address'],
                'noOfVehiclesRequired' => $operatingCentre['noOfVehiclesRequired'],
                'noOfTrailersRequired' => $operatingCentre['noOfTrailersRequired'],
                'operatingCentre' => $operatingCentre['operatingCentre'],
                'conditions' => $operatingCentre['operatingCentre']['conditionUndertakings'],
                'undertakings' => $operatingCentre['operatingCentre']['conditionUndertakings']
            ],
            $data['operatingCentres']
        );
    }

    /**
     * Map licence search errors
     *
     * @param Form  $form   Form
     * @param array $errors Errors
     */
    public function mapLicenceSearchErrors(Form $form, array $errors): void
    {
        $formMessages = [];

        if (isset($errors['number-not-valid'])) {
            foreach ($errors['number-not-valid'] as $message) {
                $formMessages['licence-number']['licenceNumber'][] = $message;
            }

            unset($errors['number-not-valid']);
        }

        if (isset($errors['not-valid'])) {
            foreach ($errors['not-valid'] as $message) {
                $formMessages['licence-number']['licenceNumber'][] = $message;
            }

            unset($errors['not-valid']);
        }

        if (isset($errors['is-psv'])) {
            foreach ($errors['is-psv'] as $message) {
                $formMessages['licence-number']['licenceNumber'][] = $message;
            }

            unset($errors['not-valid']);
        }

        if ($errors !== []) {
            $fm = $this->flashMessengerHelper;

            foreach ($errors as $error) {
                $fm->addCurrentErrorMessage($error);
            }
        }

        $form->setMessages($formMessages);
    }

    /**
     * No-op.
     *
     * @param int $lvaId Lva id
     *
     * @return void
     */
    #[\Override]
    protected function checkForRedirect($lvaId)
    {
        unset($lvaId);
    }
}
