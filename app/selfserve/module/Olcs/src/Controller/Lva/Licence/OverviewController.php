<?php

namespace Olcs\Controller\Lva\Licence;

use Common\Controller\Interfaces\MethodToggleAwareInterface;
use Common\Controller\Lva\AbstractController;
use Common\RefData;
use Common\Service\Cqrs\Exception\NotFoundException;
use Dvsa\Olcs\Transfer\Command\Licence\PrintLicence;
use Dvsa\Olcs\Transfer\Query\Licence\Licence as LicenceQry;
use Olcs\Controller\Lva\Traits\LicenceControllerTrait;
use Olcs\Controller\Lva\Traits\MethodToggleTrait;
use Olcs\Service\Surrender\SurrenderStateService;
use Olcs\View\Model\Licence\LicenceOverview;
use Olcs\View\Model\ViewModel;
use Permits\Controller\Config\FeatureToggle\FeatureToggleConfig;
use Dvsa\Olcs\Transfer\Query\Surrender\ByLicence;

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
    protected $location = 'external';
    protected $infoBoxLinks = [];

    protected $methodToggles = [
        'showSurrenderLink' => FeatureToggleConfig::SELFSERVE_SURRENDER_ENABLED,

    ];

    /**
     * Licence overview
     *
     * @return LicenceOverview
     */
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
     * @return \Zend\Http\Response
     */
    public function printAction()
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


    protected function showSurrenderLink(array $data, LicenceOverview $viewModel)
    {
        if ($data['isLicenceSurrenderAllowed']) {
            $dto = ByLicence::create(['id' => $data['id']]);

            try {
                $result = $this->handleQuery($dto);
                $surrenderData = $result->getResult();
            } catch (NotFoundException $exception) {
                $surrenderData = [];
            }
            $viewModel->setSurrenderLink($surrenderData);
        }
    }
}
