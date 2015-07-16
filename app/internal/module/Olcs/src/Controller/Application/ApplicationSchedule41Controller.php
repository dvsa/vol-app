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
            $form->setData((array)$request->getPost());

            if ($form->isValid()) {
                $licence = $this->handleQuery(
                    LicenceByNumber::create(
                        [
                            'licenceNumber' => $form->getData()['licence-number']['licenceNumber']
                        ]
                    )
                );

                $result = $this->isLicenceValid($licence->getResult());
                if ($result !== true) {
                    return $result;
                }

                return $this->redirect()->toRoute(
                    'lva-application/schedule41/transfer',
                    array(
                        'application' => $this->params('application'),
                        'licNo' => $form->getData()['licence-number']['licenceNumber']
                    )
                );
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
     * Is the licence valid according to the Ac.
     *
     * @param array $licence
     *
     * @return \Zend\Http\Response
     */
    private function isLicenceValid(array $licence)
    {
        // Licence not valid.
        $allowed = array(
            LicenceEntityService::LICENCE_STATUS_VALID,
            LicenceEntityService::LICENCE_STATUS_SUSPENDED,
            LicenceEntityService::LICENCE_STATUS_CURTAILED
        );

        if (!in_array($licence['status']['id'], $allowed)) {
            return $this->redirectWithError('application.schedule41.licence-not-valid');
        }

        // Licence is PSV.
        if ($licence['goodsOrPsv']['id'] === LicenceEntityService::LICENCE_CATEGORY_PSV) {
            return $this->redirectWithError('application.schedule41.licence-is-psv');
        }

        return true;
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
     * Get the operating centres table for transfering.
     *
     * @param $data
     *
     * @return mixed
     */
    public function getOcTable($data)
    {
        return $this->getServiceLocator()
            ->get('Table')
            ->prepareTable(
                'schedule41.operating-centres',
                $data
            );
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
