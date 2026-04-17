<?php

namespace Olcs\Controller\Licence\Surrender;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Common\Util;
use Dvsa\Olcs\Transfer\Command\Surrender\Update as UpdateSurrender;
use Laminas\Mvc\MvcEvent;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractSelfserveController;
use Olcs\Controller\Config\DataSource\DataSourceConfig;
use Olcs\Service\Surrender\SurrenderStateService;
use Permits\Controller\Config\FeatureToggle\FeatureToggleConfig;
use Permits\Data\Mapper\MapperManager;

abstract class AbstractSurrenderController extends AbstractSelfserveController implements ToggleAwareInterface
{
    use Util\FlashMessengerTrait;

    protected $toggleConfig = [
        'default' => FeatureToggleConfig::SELFSERVE_SURRENDER_ENABLED
    ];

    protected $templateConfig = [
        'default' =>  'pages/licence-surrender'
    ];

    protected $dataSourceConfig = [
        'default' => DataSourceConfig::SURRENDER
    ];

    protected $pageTemplate = 'pages/licence-surrender';

    /** @var FlashMessengerHelperService */
    protected $flashMessengerHelper;

    /**
     * @var int $licenceId
     */
    protected $licenceId;

    /**
     * @param TranslationHelperService $translationHelper
     * @param FormHelperService $formHelper
     * @param TableFactory $tableBuilder
     * @param MapperManager $mapperManager
     * @param FlashMessengerHelperService $flashMessengerHelper
     */
    public function __construct(
        TranslationHelperService $translationHelper,
        FormHelperService $formHelper,
        TableFactory $tableBuilder,
        MapperManager $mapperManager,
        FlashMessengerHelperService $flashMessengerHelper
    ) {
        $this->flashMessengerHelper = $flashMessengerHelper;
        parent::__construct($translationHelper, $formHelper, $tableBuilder, $mapperManager);
    }

    #[\Override]
    public function onDispatch(MvcEvent $e)
    {
        $this->licenceId = (int)$this->params('licence');
        return parent::onDispatch($e);
    }

    protected function renderView(array $params): ViewModel
    {
        $content = new ViewModel($params);
        $content->setTemplate($this->pageTemplate);

        $view = new ViewModel();
        $view->setTemplate('layout/layout')
            ->setTerminal(true)
            ->addChild($content, 'content');

        return $view;
    }

    protected function getNumberOfDiscs(): int
    {
        return  $this->getSurrenderStateService()->getDiscsOnLicence();
    }

    protected function getLink(string $route): string
    {
        return $this->url()->fromRoute($route, [], [], true);
    }

    protected function updateSurrender(string $status, array $extraData = []): bool
    {
        $dtoData = array_merge([
            'id' => $this->licenceId,
            'version' => $this->data['surrender']['version'],
            'status' => $status,
        ], $extraData);

        $response = $this->handleCommand(UpdateSurrender::create($dtoData));
        return $response->isOk();
    }

    /**
     * Surrender State service provides information on status but also
     * other useful util functions for the state of a surrender e.g. number of disks
     * @return SurrenderStateService
     */
    protected function getSurrenderStateService(): SurrenderStateService
    {
        return (new SurrenderStateService())->setSurrenderData($this->data['surrender']);
    }
    /**
     *
     * @return array
     *
     */
    abstract protected function getViewVariables(): array;


    /**
     * @param array $surrender
     *
     * @return ViewModel
     */
    protected function createView(): ViewModel
    {
        $view = $this->genericView();
        $variables = $this->getViewVariables();
        if (!empty($variables)) {
            $view->setVariables(
                $variables
            );
        }
        return $view;
    }

    protected function isInternationalLicence(): bool
    {
        return $this->data['surrender']['isInternationalLicence'];
    }
}
