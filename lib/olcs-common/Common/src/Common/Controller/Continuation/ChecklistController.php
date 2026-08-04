<?php

namespace Common\Controller\Continuation;

use Common\Data\Mapper\Continuation\LicenceChecklist as LicenceChecklistMapper;
use Common\FormService\FormServiceManager;
use Common\RefData;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Transfer\Query\ContinuationDetail\LicenceChecklist as LicenceChecklistQuery;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\View\Model\ViewModel;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * ChecklistController
 */
class ChecklistController extends AbstractContinuationController
{
    public const FINANCES_ROUTE = 'continuation/finances';

    public const DECLARATION_ROUTE = 'continuation/declaration';

    public const CONDITIONS_UNDERTAKINGS_ROUTE = 'continuation/conditions-undertakings';

    protected $layout = 'pages/continuation-checklist';

    protected $checklistSectionLayout = 'layouts/simple';

    protected $currentStep = self::STEP_CHECKLIST;

    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FormServiceManager $formServiceManager,
        TranslationHelperService $translationHelper
    ) {
        parent::__construct($niTextTranslationUtil, $authService, $formServiceManager, $translationHelper);
    }

    /**
     * Index page
     *
     * @return ViewModel
     */
    #[\Override]
    public function indexAction()
    {
        $data = $this->getData(
            $this->getContinuationDetailId()
        );
        $licenceData = $data['licence'];

        $form = $this->getForm('continuations-checklist', $data);
        $request = $this->getRequest();

        if ($request->isPost()) {
            $form->setData((array) $request->getPost());
            if ($form->isValid()) {
                $this->redirect()->toRoute($this->getNextStepRoute($data), [], [], true);
            }
        }

        return $this->getViewModel(
            $licenceData['licNo'],
            $form,
            LicenceChecklistMapper::mapFromResultToView($data, $this->translationHelper)
        );
    }

    /**
     * Get data
     *
     * @param int $continuationDetailId continuation detail id
     *
     * @return array
     */
    protected function getData($continuationDetailId)
    {
        $dto = LicenceChecklistQuery::create(['id' => $continuationDetailId]);
        $response = $this->handleQuery($dto);
        if (!$response->isOk()) {
            $this->addErrorMessage('unknown-error');
        }

        return $response->getResult();
    }

    /**
     * People section page
     *
     * @return ViewModel
     */
    public function peopleAction()
    {
        $data = $this->getData(
            $this->getContinuationDetailId()
        );
        $licenceData = $data['licence'];
        $organisation = $data['licence']['organisation'];
        $organisationUsers = $organisation['organisationPersons'];
        $mappedData = LicenceChecklistMapper::mapPeopleSectionToView(
            $organisationUsers,
            $organisation['type']['id'],
            $this->translationHelper
        );
        $view = new ViewModel(
            [
                'licNo' => $licenceData['licNo'],
                'data' => $mappedData['people'],
                'totalMessage' => $mappedData['totalPeopleMessage'],
                'totalCount' => count($organisationUsers)
            ]
        );

        return $this->renderSection($view);
    }

    /**
     * Vehicles section page
     *
     * @return ViewModel
     */
    public function vehiclesAction()
    {
        $data = $this->getData(
            $this->getContinuationDetailId()
        );
        $licenceData = $data['licence'];
        $licenceVehicles = $licenceData['licenceVehicles'];
        $mappedData = LicenceChecklistMapper::mapVehiclesSectionToView(
            $licenceData,
            $this->translationHelper
        );
        $view = new ViewModel(
            [
                'licNo' => $licenceData['licNo'],
                'data' => $mappedData['vehicles'],
                'totalMessage' => $mappedData['totalVehiclesMessage'],
                'totalCount' => count($licenceVehicles)
            ]
        );

        return $this->renderSection($view);
    }

    /**
     * Users section page
     *
     * @return ViewModel
     */
    public function usersAction()
    {
        $data = $this->getData(
            $this->getContinuationDetailId()
        );

        $licenceData = $data['licence'];

        $mappedData = LicenceChecklistMapper::mapUsersSectionToView(
            $licenceData,
            $this->translationHelper
        );
        $view = new ViewModel(
            [
                'licNo' => $licenceData['licNo'],
                'data' => $mappedData['users'],
                'totalMessage' => $mappedData['totalUsersMessage'],
                'totalCount' => $mappedData['totalCount']
            ]
        );

        return $this->renderSection($view);
    }

    /**
     * Operating centres section page
     *
     * @return ViewModel
     */
    public function operatingCentresAction()
    {
        $data = $this->getData(
            $this->getContinuationDetailId()
        );
        $licenceData = $data['licence'];
        $licenceVehicles = $licenceData['operatingCentres'];
        $mappedData = LicenceChecklistMapper::mapOperatingCentresSectionToView(
            $data,
            $this->translationHelper
        );
        $view = new ViewModel(
            [
                'licNo' => $licenceData['licNo'],
                'data' => $mappedData['operatingCentres'],
                'totalMessage' => $mappedData['totalOperatingCentresMessage'],
                'totalCount' => count($licenceVehicles)
            ]
        );

        return $this->renderSection($view);
    }

    /**
     * Transport managers section page
     *
     * @return ViewModel
     */
    public function transportManagersAction()
    {
        $data = $this->getData(
            $this->getContinuationDetailId()
        );
        $licenceData = $data['licence'];
        $tmLicences = $licenceData['tmLicences'];
        $mappedData = LicenceChecklistMapper::mapTransportManagerSectionToView(
            $licenceData,
            $this->translationHelper
        );
        $view = new ViewModel(
            [
                'licNo' => $licenceData['licNo'],
                'data' => $mappedData['transportManagers'],
                'totalMessage' => $mappedData['totalTransportManagersMessage'],
                'totalCount' => count($tmLicences)
            ]
        );

        return $this->renderSection($view);
    }

    /**
     * Safety inspectors section page
     *
     * @return ViewModel
     */
    public function safetyInspectorsAction()
    {
        $data = $this->getData(
            $this->getContinuationDetailId()
        );
        $licenceData = $data['licence'];
        $mappedData = LicenceChecklistMapper::mapSafetyInspectorsSectionToView(
            $licenceData,
            $this->translationHelper
        );
        $workshops = $licenceData['workshops'];
        $view = new ViewModel(
            [
                'licNo' => $licenceData['licNo'],
                'data' => $mappedData['safetyInspectors'],
                'totalMessage' => $mappedData['totalSafetyInspectorsMessage'],
                'totalCount' => count($workshops)
            ]
        );

        return $this->renderSection($view);
    }

    /**
     * Render section
     *
     * @param ViewModel $view view model
     *
     * @return ViewModel
     */
    protected function renderSection($view)
    {
        $view->setTemplate('pages/continuation-section');
        $base = new ViewModel();
        $base->setTemplate($this->checklistSectionLayout)
            ->setTerminal(true)
            ->addChild($view, 'content');

        return $base;
    }

    /**
     * Get next step route
     *
     * @param array $data data
     *
     * @return string
     */
    protected function getNextStepRoute($data)
    {
        $licenceData = $data['licence'];
        if (
            $licenceData['licenceType']['id'] === RefData::LICENCE_TYPE_SPECIAL_RESTRICTED
            && $licenceData['goodsOrPsv']['id'] === RefData::LICENCE_CATEGORY_PSV
        ) {
            return self::DECLARATION_ROUTE;
        }

        if ($data['hasConditionsUndertakings'] || $this->isPsvRestricted($licenceData)) {
            return self::CONDITIONS_UNDERTAKINGS_ROUTE;
        }

        return self::FINANCES_ROUTE;
    }
}
