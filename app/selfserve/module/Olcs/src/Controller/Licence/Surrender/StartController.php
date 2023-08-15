<?php

namespace Olcs\Controller\Licence\Surrender;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Common\Service\Cqrs\Exception\AccessDeniedException;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Transfer\Command\Surrender\Create;
use Laminas\Http\Response;
use Laminas\I18n\Translator\Translator;
use Laminas\Mvc\MvcEvent;
use Olcs\Controller\AbstractSelfserveController;
use Olcs\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\FeatureToggle\FeatureToggleConfig;
use Permits\Data\Mapper\MapperManager;

class StartController extends AbstractSelfserveController implements ToggleAwareInterface
{
    protected $toggleConfig = [
        'default' => FeatureToggleConfig::SELFSERVE_SURRENDER_ENABLED
    ];

    protected $templateConfig = [
        'default' => 'licence/surrender-index'
    ];

    protected $dataSourceConfig = [
        'index' => DataSourceConfig::LICENCE
    ];

    protected $formConfig = [
        'index' => [
            'startForm' => [
                'formClass' => \Olcs\Form\Model\Form\Surrender\Start::class
            ]
        ]
    ];

    protected FlashMessengerHelperService $flashMessengerHelper;

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

    public function onDispatch(MvcEvent $e)
    {
        return parent::onDispatch($e);
    }

    /**
     * IndexAction
     *
     * @return array|\Laminas\View\Model\ViewModel
     */
    public function indexAction()
    {
        $licence = $this->data['licence'];
        return $this->getView($licence, $this->translationHelper);
    }

    /**
     * startAction
     *
     * @return \Laminas\View\Model\ViewModel | Response
     */
    public function startAction()
    {
        $licNo = $this->params('licence');

        try {
            $response = $this->handleCommand(Create::create(['id' => $licNo]));
            if ($response->isOk()) {
                $result = $response->getResult();
                if (!empty($result)) {
                    return $this->redirect()->toRoute(
                        'licence/surrender/review-contact-details/GET',
                        [
                            'licence' => $licNo,
                        ]
                    );
                }
            }
        } catch (AccessDeniedException $e) {
            $message = $this->translationHelper->translate('licence.surrender.already.applied');
            $this->flashMessengerHelper->addInfoMessage($message);
        } catch (\Exception $e) {
            $this->flashMessengerHelper->addUnknownError();
        }

        $this->redirect()->refresh();
    }

    private function getGvData()
    {
        return [
            'pageTitle' => 'licence.surrender.start.title.gv',
            'cancelBus' => ['']
        ];
    }

    private function getPsvData($translateService)
    {
        return [
            'pageTitle' => 'licence.surrender.start.title.psv',
            'cancelBus' => [$translateService->translate('licence.surrender.start.cancel.bus')]
        ];
    }

    /**
     * getView
     *
     * @param Licence    $licence
     * @param Translator $translateService
     *
     * @return \Laminas\View\Model\ViewModel
     */
    private function getView($licence, $translateService): \Laminas\View\Model\ViewModel
    {
        $view = $this->genericView();

        switch ($licence['goodsOrPsv']['id']) {
            case 'lcat_gv':
                $view->setVariables($this->getGvData());
                break;
            case 'lcat_psv':
                $view->setVariables($this->getPsvData($translateService));
                break;
            default:
                break;
        }

        $view->setVariable('licNo', $licence['licNo']);
        $view->setVariable('body', 'markup-licence-surrender-start');
        $view->setVariable('backUrl', $this->url()->fromRoute('lva-licence', ['licence' => $licence['id']]));
        $view->setVariable('startForm', $this->form);

        return $view;
    }
}
