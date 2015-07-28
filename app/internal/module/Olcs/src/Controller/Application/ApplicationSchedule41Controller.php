<?php

/**
 * ApplicationSchedule41Controller.php
 */
namespace Olcs\Controller\Application;

use Olcs\Controller\Interfaces\ApplicationControllerInterface;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;

use Common\Service\Entity\LicenceEntityService;
use Common\BusinessService\BusinessServiceAwareInterface;
use Common\BusinessService\BusinessServiceAwareTrait;
use Common\BusinessService\Response;
use Common\Controller\Lva\AbstractController;
use Common\Controller\Plugin\Redirect;

use Dvsa\Olcs\Transfer\Query\Licence\LicenceByNumber;
use Dvsa\Olcs\Transfer\Command\Application\Schedule41;

use Zend\Form\Form;
use Zend\View\Model\ViewModel;

/**
 * Class ApplicationSchedule41Controller
 *
 * Application41 schedule controller.
 *
 * @package Olcs\Controller\Application
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
class ApplicationSchedule41Controller extends AbstractController implements ApplicationControllerInterface, BusinessServiceAwareInterface
{
    use ApplicationControllerTrait,
        BusinessServiceAwareTrait;

    protected $lva = 'application';
    protected $location = 'internal';

    protected $section = 'operating_centres';

    /**
     * Search for a licence by licence number.
     *
     * @return string|\Zend\Http\Response|ViewModel
     */
    public function licenceSearchAction()
    {
        $request = $this->getRequest();
        $form = $this->getServiceLocator()
            ->get('Helper\Form')
            ->createFormWithRequest(
                'Schedule41LicenceSearch',
                $request
            );

        if ($request->isPost()) {
            if ($this->isButtonPressed('cancel')) {
                return $this->redirect()->toRoute(
                    'lva-application/overview',
                    array(
                        'application' => $this->params('application')
                    )
                );
            }

            $form->setData((array)$request->getPost());

            if ($form->isValid()) {
                $valid = $this->isLicenceValid($request->getPost()['licence-number']['licenceNumber']);

                if ($valid === true) {
                    $this->redirect()->toRoute(
                        'lva-application/schedule41/transfer',
                        array(
                            'application' => $this->params('application'),
                            'licNo' => $form->getData()['licence-number']['licenceNumber']
                        )
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
            if ($this->isButtonPressed('cancel')) {
                return $this->redirect()->toRoute(
                    'lva-application/overview',
                    array(
                        'application' => $this->params('application')
                    )
                );
            }

            $postData = (array)$request->getPost();

            if (!isset($postData['table']['id'])) {
                $this->flashMessenger()
                    ->addErrorMessage('application.schedule41.no-rows-selected');

                return $this->redirect()->toRoute(
                    'lva-application/schedule41/transfer',
                    array(
                        'application' => $this->params('application'),
                        'licNo' => $this->params('licNo')
                    )
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

                return $this->redirect()->toRoute(
                    'lva-application/operating_centres',
                    array(
                        'application' => $this->params('application')
                    )
                );
            }
        }

        $form = $this->getServiceLocator()->get('Helper\Form')->createFormWithRequest('Schedule41Transfer', $request);
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
     *
     */
    public function approveSchedule41Action()
    {

    }

    /**
     * Is the licence valid according to the Ac.
     *
     * @param $licenceNumber
     *
     * @return array|bool
     */
    private function isLicenceValid($licenceNumber)
    {
        $response = $this->handleQuery(
            LicenceByNumber::create(
                [
                    'licenceNumber' => $licenceNumber
                ]
            )
        );

        if (!$response->isOk()) {
            $errors['number-not-valid'][] = 'application.schedule41.licence-number-not-valid';

            return $errors;
        }

        $licence = $response->getResult();

        // Licence not valid.
        $allowed = array(
            LicenceEntityService::LICENCE_STATUS_VALID,
            LicenceEntityService::LICENCE_STATUS_SUSPENDED,
            LicenceEntityService::LICENCE_STATUS_CURTAILED
        );

        $errors = [];
        if (!in_array($licence['status']['id'], $allowed)) {
            $errors['not-valid'][] = 'application.schedule41.licence-not-valid';
        }

        // Licence is PSV.
        if ($licence['goodsOrPsv']['id'] === LicenceEntityService::LICENCE_CATEGORY_PSV) {
            $errors['is-psv'][] = 'application.schedule41.licence-is-psv';
        }

        if (empty($errors)) {
            return true;
        }

        return $errors;
    }

    /**
     * Return a redirect response with an error message.
     *
     * @param $message
     *
     * @return \Zend\Http\Response
     */
    protected function redirectWithError($message)
    {
        $this->flashMessenger()
            ->addErrorMessage($message);

        return $this->redirect()->toRoute(
            'lva-application/schedule41',
            array(
                'application' => $this->params('application')
            )
        );
    }

    /**
     * Get the operating centres table for transferring.
     *
     * @param $data
     *
     * @return mixed
     */
    public function getOcTable($data)
    {
        $table = $this->getServiceLocator()
            ->get('Table')
            ->prepareTable(
                'schedule41.operating-centres',
                $data
            );

        return $table;
    }

    /**
     * Format the operating centre data for the table.
     *
     * @param $data
     *
     * @return array
     */
    public function formatDataForTable($data)
    {
        $operatingCentres = array_map(
            function ($operatingCentre) {
                return array(
                    'id' => $operatingCentre['id'],
                    'address' => $operatingCentre['operatingCentre']['address'],
                    'noOfVehiclesRequired' => $operatingCentre['noOfVehiclesRequired'],
                    'noOfTrailersRequired' => $operatingCentre['noOfTrailersRequired'],
                    'operatingCentre' => $operatingCentre['operatingCentre'],
                    'conditions' => $operatingCentre['operatingCentre']['conditionUndertakings'],
                    'undertakings' => $operatingCentre['operatingCentre']['conditionUndertakings']
                );
            },
            $data['operatingCentres']
        );

        return $operatingCentres;
    }

    public function mapLicenceSearchErrors(Form $form, array $errors)
    {
        $formMessages = [];

        if (isset($errors['number-not-valid'])) {

            foreach ($errors['number-not-valid'] as $key => $message) {
                $formMessages['licence-number']['licenceNumber'][] = $message;
            }

            unset($errors['number-not-valid']);
        }

        if (isset($errors['not-valid'])) {

            foreach ($errors['not-valid'] as $key => $message) {
                $formMessages['licence-number']['licenceNumber'][] = $message;
            }

            unset($errors['not-valid']);
        }

        if (isset($errors['is-psv'])) {

            foreach ($errors['dueDate'][0] as $key => $message) {
                $formMessages['licence-number']['licenceNumber'][] = $message;
            }

            unset($errors['not-valid']);
        }

        if (!empty($errors)) {
            $fm = $this->getServiceLocator()->get('Helper\FlashMessenger');

            foreach ($errors as $error) {
                $fm->addCurrentErrorMessage($error);
            }
        }

        $form->setMessages($formMessages);
    }

    /**
     * No-op.
     *
     * @param $lvaId
     *
     * @return bool
     */
    public function checkForRedirect($lvaId)
    {
        unset($lvaId);
    }
}
