<?php

/**
 * IRHP Application Controller
 *
 * @author Andy Newton <andy@vitri.ltd>
 */

namespace Olcs\Controller\IrhpPermits;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Common\Data\Mapper\Permits\NoOfPermits;
use Common\FeatureToggle;
use Common\RefData;
use Common\Service\Cqrs\Exception\NotFoundException;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\CancelApplication;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\SubmitApplication;
use Dvsa\Olcs\Transfer\Query\IrhpPermitApplication\GetList as ListDTO;
use Dvsa\Olcs\Transfer\Query\IrhpPermitStock\AvailableCountries;
use Dvsa\Olcs\Transfer\Query\IrhpPermitWindow\OpenByCountry;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\ById as ItemDto;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\MaxStockPermits;
use Dvsa\Olcs\Transfer\Query\Licence\Licence as LicenceDto;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\CreateFull as CreateDTO;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\UpdateFull as UpdateDTO;
use Olcs\Controller\Interfaces\IrhpApplicationControllerInterface;
use Olcs\Form\Model\Form\IrhpBilateral;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Data\Mapper\IrhpApplication as IrhpApplicationMapper;
use Olcs\Mvc\Controller\ParameterProvider\ConfirmItem;
use Zend\View\Model\ViewModel;

class IrhpApplicationController extends AbstractInternalController implements
    IrhpApplicationControllerInterface,
    LeftViewProvider,
    ToggleAwareInterface
{

    protected $toggleConfig = [
        'default' => [
            FeatureToggle::BACKEND_ECMT
        ],
    ];

    protected $routeIdentifier = 'irhp-application';

    // Maps the route parameter irhpPermitId to the "id" parameter in the the ById (ItemDTO) query.
    protected $itemParams = ['id' => 'irhpAppId'];

    protected $deleteParams = ['id' => 'irhpAppId'];

    // Maps the licence route parameter into the ListDTO as licence => value
    protected $listVars = ['licence'];
    protected $listDto = ListDto::class;
    protected $itemDto = ItemDto::class;
    protected $formClass = IrhpBilateral::class;
    protected $addFormClass = IrhpBilateral::class;
    protected $mapperClass = IrhpApplicationMapper::class;
    protected $createCommand = CreateDto::class;
    protected $updateCommand = UpdateDto::class;

    protected $addContentTitle = 'Add Irhp Application';

    // After Adding and Editing we want users taken back to index dashboard
    protected $redirectConfig = [
        'add' => [
            'route' => 'licence/permits',
            'action' => 'index',
        ],
        'edit' => [
            'route' => 'licence/permits',
            'action' => 'index',
        ]
    ];

    // Scripts to include when rendering actions.
    protected $inlineScripts = [
        'indexAction' => ['table-actions'],
    ];

    /**
     * Get left view
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function getLeftView()
    {
        $view = new ViewModel(
            [
                'navigationId' => 'irhp_permits',
                'navigationTitle' => 'Application details'
            ]
        );
        $view->setTemplate('admin/sections/admin/partials/generic-left');

        return $view;
    }

    /**
     * Handles click of the Submit button on right-sidebar
     *
     * @return \Zend\Http\Response
     *
     */
    public function submitAction()
    {
        return $this->confirmCommand(
            new ConfirmItem($this->deleteParams),
            SubmitApplication::class,
            'Are you sure?',
            'Submit Application. Are you sure?',
            'IRHP Application Submitted'
        );
    }

    /**
     * Handles click of the Cancel button on right sidebar
     *
     * @return \Zend\Http\Response
     *
     */
    public function cancelAction()
    {
        return $this->confirmCommand(
            new ConfirmItem($this->deleteParams),
            CancelApplication::class,
            'Are you sure?',
            'Cancel Application. Are you sure?',
            'IRHP Application Cancelled'
        );
    }

    /**
     * Setup required values for Add form
     *
     * @param $form
     * @param $formData
     * @return mixed
     */
    protected function alterFormForAdd($form, $formData)
    {
        $licence = $this->getLicence();
        $formData['topFields']['numVehicles'] = $licence['totAuthVehicles'];
        $formData['topFields']['numVehiclesLabel'] = $licence['totAuthVehicles'];
        $formData['topFields']['dateReceived'] = date("Y-m-d");
        $formData['topFields']['irhpPermitType'] = $this->params()->fromRoute('permitTypeId', null);
        $formData['topFields']['licence'] = $this->params()->fromRoute('licence', null);

        $maxStockPermits = $this->handleQuery(
            MaxStockPermits::create(['licence' => $this->params()->fromRoute('licence', null)])
        );
        if (!$maxStockPermits->isOk()) {
            throw new NotFoundException('Could not retrieve max permits data');
        }
        $formData['maxStockPermits']['result'] = $maxStockPermits->getResult()['results'];

        // Prepare data structure with open bilateral windows for NoOfPermits form builder
        $formData['application'] = IrhpApplicationMapper::mapApplicationData(
            $this->getBilateralWindows()['results'],
            RefData::IRHP_BILATERAL_PERMIT_TYPE_ID
        );
        $formData['application']['licence']['totAuthVehicles'] = $licence['totAuthVehicles'];

        // Build the dynamic NoOfPermits per country per year form from Common
        NoOfPermits::mapForFormOptions(
            $formData,
            $form,
            $this->getServiceLocator()->get('Helper\Translation'),
            'application',
            'maxStockPermits'
        );

        $form->setData($formData);

        return $form;
    }

    /**
     * Setup required values for Edit form
     *
     * @param $form
     * @param $formData
     * @return mixed
     *
     */
    protected function alterFormForEdit($form, $formData)
    {
        $licence = $this->getLicence();

        $formData['topFields']['numVehicles'] = $licence['totAuthVehicles'];
        $formData['topFields']['numVehiclesLabel'] = $licence['totAuthVehicles'];
        $formData['topFields']['licence'] = $this->params()->fromRoute('licence', null);

        // Prepare data structure with open bilateral windows for NoOfPermits form builder
        $formData['application'] = IrhpApplicationMapper::mapApplicationData(
            $this->getBilateralWindows()['results'],
            RefData::IRHP_BILATERAL_PERMIT_TYPE_ID,
            $formData
        );

        $maxStockPermits = $this->handleQuery(
            MaxStockPermits::create(['licence' => $this->params()->fromRoute('licence', null)])
        );
        if (!$maxStockPermits->isOk()) {
            throw new NotFoundException('Could not retrieve max permits data');
        }
        $formData['maxStockPermits']['result'] = $maxStockPermits->getResult()['results'];

        // Build the dynamic NoOfPermits per country per year form from Common
        $formData['application']['licence']['totAuthVehicles'] = $licence['totAuthVehicles'];
        NoOfPermits::mapForFormOptions(
            $formData,
            $form,
            $this->getServiceLocator()->get('Helper\Translation'),
            'application',
            'maxStockPermits'
        );

        $form->setData($formData);

        return $form;
    }

    /**
     * @return array|mixed
     * @throws NotFoundException
     */
    protected function getBilateralWindows()
    {
        //Get list of countries that BiLaterals are applicable to
        $countries = $this->handleQuery(AvailableCountries::create([]));
        if (!$countries->isOk()) {
            throw new NotFoundException('Could not retrieve available countries');
        }

        // We just want the IDs for the next Query
        $countryIds = array_column($countries->getResult()['countries'], 'id');

        if (empty($countryIds)) {
            throw new NotFoundException('No countries are available for this type of application');
        }

        //Query open windows for the country IDs retrieved above
        $windows = $this->handleQuery(OpenByCountry::create(['countries' => $countryIds]));
        if (!$windows->isOk()) {
            throw new NotFoundException('Could not retrieve open windows');
        }

        return $windows->getResult();
    }

    /**
     * @return array|mixed
     * @throws NotFoundException
     */
    protected function getLicence()
    {
        $response = $this->handleQuery(LicenceDto::create(['id' => $this->params()->fromRoute('licence', null)]));
        if (!$response->isOk()) {
            throw new NotFoundException('Could not find Licence');
        }

        return $response->getResult();
    }
}
