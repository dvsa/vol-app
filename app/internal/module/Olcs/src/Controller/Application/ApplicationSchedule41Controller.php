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
                $licence = $this->getServiceLocator()
                    ->get('Entity\Licence')
                    ->getList(
                        array(
                            'licNo' => (array)$form->getData()['licence-number']['licenceNumber']
                        )
                    );

                $result = $this->isLicenceValid($licence);
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
        $data = $this->getServiceLocator()->get('Entity\Licence')
            ->getByLicenceNumberWithOperatingCentres(
                $this->params()->fromRoute('licNo', null)
            );

        if ($request->isPost()) {
            $postData = (array)$request->getPost();

            $licence = $data['Results'][0];

            $response = $this->getServiceLocator()
                ->get('BusinessServiceManager')
                ->get('Lva\Schedule41')
                ->process(
                    array(
                        'winningApplication' => $this->getApplication(),
                        'losingLicence' => $licence,
                        'data' => $postData
                    )
                );

            if ($response->getType() === Response::TYPE_SUCCESS) {
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
            $this->getOcTable($data)
        );

        return $this->render('schedule41', $form);
    }

    /**
     * Is the licence valid according to the Ac.
     *
     * @param array $params
     *
     * @return \Zend\Http\Response
     */
    private function isLicenceValid(array $params)
    {
        $licence = (count($params['Results']) > 0 ? $params['Results'][0] : false);
        if (!$licence) {
            return $this->redirectWithError('application.schedule41.licence-not-found');
        }

        // Licence not valid.
        $allowed = array(
            LicenceEntityService::LICENCE_STATUS_VALID,
            LicenceEntityService::LICENCE_STATUS_SUSPENDED,
            LicenceEntityService::LICENCE_STATUS_CURTAILED
        );

        if (!in_array($licence['status'], $allowed)) {
            return $this->redirectWithError('application.schedule41.licence-not-valid');
        }

        // Licence is PSV.
        if ($licence['goods_or_psv'] === LicenceEntityService::LICENCE_CATEGORY_PSV) {
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
                $this->formatDataForTable($data)
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
                    'id' => $operatingCentre['operatingCentre']['id'],
                    'address' => $operatingCentre['operatingCentre']['address'],
                    'noOfVehiclesRequired' => $operatingCentre['noOfVehiclesRequired'],
                    'noOfTrailersRequired' => $operatingCentre['noOfTrailersRequired'],
                    'operatingCentre' => $operatingCentre['operatingCentre'],
                    'conditions' => $operatingCentre['operatingCentre']['conditionUndertakings'],
                    'undertakings' => $operatingCentre['operatingCentre']['conditionUndertakings']
                );
            },
            $data['Results']
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
