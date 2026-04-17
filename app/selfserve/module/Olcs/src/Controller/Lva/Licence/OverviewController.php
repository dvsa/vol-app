<?php

namespace Olcs\Controller\Lva\Licence;

use Common\Controller\Interfaces\MethodToggleAwareInterface;
use Common\Controller\Lva\AbstractController;
use Common\Controller\Lva\Adapters\LicenceLvaAdapter;
use Common\Controller\Lva\Traits\MethodToggleTrait;
use Common\RefData;
use Common\Service\Cqrs\Exception\NotFoundException;
use Dvsa\Olcs\Transfer\Command\Licence\PrintLicence;
use Dvsa\Olcs\Transfer\Query\Licence\Licence as LicenceQry;
use Dvsa\Olcs\Transfer\Query\Surrender\ByLicence;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Olcs\Controller\Lva\Traits\LicenceControllerTrait;
use Olcs\Service\Helper\LicenceOverviewHelperService;
use Olcs\View\Model\Licence\LicenceOverview;
use Permits\Controller\Config\FeatureToggle\FeatureToggleConfig;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Licence Overview Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class OverviewController extends AbstractController implements MethodToggleAwareInterface
{
    use LicenceControllerTrait;
    use MethodToggleTrait;

    protected $lva = 'licence';
    protected string $location = 'external';
    protected $infoBoxLinks = [];

    protected $methodToggles = [
        'showSurrenderLink' => FeatureToggleConfig::SELFSERVE_SURRENDER_ENABLED,

    ];

    /**
     * @param NiTextTranslation $niTextTranslationUtil
     * @param AuthorizationService $authService
     * @param LicenceLvaAdapter $lvaAdapter
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        protected LicenceLvaAdapter $lvaAdapter
    ) {
        parent::__construct($niTextTranslationUtil, $authService);
    }

    /**
     * Licence overview
     *
     * @return LicenceOverview
     */
    #[\Override]
    public function indexAction()
    {
        $data = $this->getOverviewData($this->getLicenceId());

        if (empty($data)) {
            return $this->notFoundAction();
        }

        $data['idIndex'] = $this->getIdentifierIndex();
        $variables = ['shouldShowCreateVariation' => true];

        if ($data['licenceType']['id'] === RefData::LICENCE_TYPE_SPECIAL_RESTRICTED) {
            $variables['shouldShowCreateVariation'] = false;
        }

        $viewModel = new LicenceOverview($data, $this->getAccessibleSections(), $variables);

        $this->togglableMethod(
            $this,
            'showSurrenderLink',
            $data,
            $viewModel
        );

        $viewModel->setInfoBoxLinks();
        return $viewModel;
    }

    /**
     * Process action - Print
     *
     * @return \Laminas\Http\Response|null
     */
    public function printAction(): ?\Laminas\Http\Response
    {
        $cmd = PrintLicence::create(
            [
                'id' => $this->getLicenceId(),
                'dispatch' => false,
            ]
        );

        $response = $this->handleCommand($cmd);
        if (!$response->isOk()) {
            $this->addErrorMessage('licence.print.failed');

            return null;
        }

        $documentId = $response->getResult()['id']['document'];

        return $this->redirect()->toRoute(
            'getfile',
            [
                'identifier' => $documentId,
            ]
        );
    }

    /**
     * Get overview data
     *
     * @param int $licenceId Licence id
     *
     * @return array|mixed
     */
    protected function getOverviewData($licenceId)
    {
        $dto = LicenceQry::create(['id' => $licenceId]);
        $response = $this->handleQuery($dto);
        if ($response->isForbidden()) {
            return null;
        }

        return $response->getResult();
    }

    /**
     * Disable trailers for NI
     *
     * @param bool $keysOnly only return keys?
     *
     * @return array
     */
    #[\Override]
    protected function getAccessibleSections($keysOnly = true)
    {
        $accessibleSections = parent::getAccessibleSections($keysOnly);
        if ($this->fetchDataForLva()['niFlag'] === 'Y') {
            if ($keysOnly) {
                $accessibleSections = array_values(array_diff($accessibleSections, ['trailers']));
            } else {
                unset($accessibleSections['trailers']);
            }
        }
        return $accessibleSections;
    }


    protected function showSurrenderLink(array $data, LicenceOverview $viewModel): void
    {
        if ($data['isLicenceSurrenderAllowed']) {
            $dto = ByLicence::create(['id' => $data['id']]);
            $surrenderData = [];
            try {
                $result = $this->handleQuery($dto);
                if ($result->isOk()) {
                    $surrenderData = $result->getResult();
                }
            } catch (NotFoundException) {
                $surrenderData = [];
            }
            $viewModel->setSurrenderLink($surrenderData);
        }
    }
}
