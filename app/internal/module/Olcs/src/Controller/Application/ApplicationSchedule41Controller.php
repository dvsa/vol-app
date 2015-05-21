<?php

/**
 * ApplicationSchedule41Controller.php
 */
namespace Olcs\Controller\Application;

use Common\Controller\Lva\Traits\CrudTableTrait;

use Common\Controller\Plugin\Redirect;
use Common\Service\Entity\LicenceEntityService;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;
use Olcs\Controller\AbstractController;
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
class ApplicationSchedule41Controller extends AbstractController
{
    use ApplicationControllerTrait;

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
                if ($result instanceof \Redirect) {
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

        $view = new ViewModel(
            array(
                'form' => $form
            )
        );

        $view->setTemplate('partials/form');

        return $this->renderView($view);
    }

    /**
     * Transfer operating centres from a licence to and application.
     *
     * @return string|ViewModel
     */
    public function transferAction()
    {
        $request = $this->getRequest();
        $data = $this->getServiceLocator()->get('Entity\LicenceOperatingCentre')
            ->getOperatingCentresByLicenceNumberForSchedule41(
                $this->params()->fromRoute('licNo', null)
            );

        $form = $this->getServiceLocator()->get('Helper\Form')->createFormWithRequest('Schedule41Transfer', $request);
        $form->get('table')->get('table')->setTable(
            $this->getOcTable($data)
        );

        $view = new ViewModel(
            array(
                'form' => $form
            )
        );

        $view->setTemplate('partials/form');

        return $this->renderView($view);
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

        return $this->redirectToRouteAjax(
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
