<?php

namespace Olcs\Controller\Licence\Surrender;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Common\Service\Cqrs\Exception\AccessDeniedException;
use Dvsa\Olcs\Transfer\Command\Surrender\Create;
use Olcs\Controller\Config\DataSource\DataSourceConfig;
use Olcs\Form\Model\Form\Surrender\Start;
use Permits\Controller\Config\FeatureToggle\FeatureToggleConfig;
use Zend\Http\Response;
use Zend\Mvc\MvcEvent;

class StartController extends AbstractSurrenderController implements ToggleAwareInterface
{
    protected $toggleConfig = [
        'default' => FeatureToggleConfig::SELFSERVE_SURRENDER_ENABLED
    ];

    protected $templateConfig = [
        'index' => 'licence/surrender-index'
    ];

    protected $dataSourceConfig = [
        'index' => DataSourceConfig::LICENCE
    ];

    protected $formConfig = [
        'index' => [
            'startForm' => [
                'formClass' => Start::class
            ]
        ]
    ];

    private $translateService;

    public function onDispatch(MvcEvent $e)
    {
        $this->translateService = $this->getServiceLocator()->get('Helper\Translation');
        return parent::onDispatch($e);
    }

    /**
     * IndexAction
     *
     * @return array|\Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        return $this->createView();
    }

    /**
     * startAction
     *
     * @return \Zend\View\Model\ViewModel | Response
     */
    public function startAction()
    {
        try {
            $response = $this->handleCommand(Create::create(['id' => $this->licenceId]));
            if ($response->isOk()) {
                $result = $response->getResult();
                if (!empty($result)) {
                    return $this->redirect()->toRoute('licence/surrender/review-contact-details/GET', [], [], true);
                }
            }
        } catch (AccessDeniedException $e) {
            $message = $this->translateService->translate('licence.surrender.already.applied');
            $this->hlpFlashMsgr->addInfoMessage($message);
        } catch (\Exception $e) {
            $this->hlpFlashMsgr->addUnknownError();
        }

        return $this->redirect()->refresh();
    }

    private function getGvData()
    {
        return [
            'pageTitle' => 'licence.surrender.start.title.gv',
            'cancelBus' => ['']
        ];
    }

    private function getPsvData()
    {
        return [
            'pageTitle' => 'licence.surrender.start.title.psv',
            'cancelBus' => [$this->translateService->translate('licence.surrender.start.cancel.bus')]
        ];
    }

    protected function getViewVariables(): array
    {
        switch ($this->data['licence']['goodsOrPsv']['id']) {
            case 'lcat_gv':
                $variables = $this->getGvData();
                break;
            case 'lcat_psv':
                $variables = $this->getPsvData();
                break;
            default:
                $variables = [];
                break;
        }

        $variables = array_merge($variables, [
            'licNo' => $this->data['surrender']['licence']['licNo'],
            'body' => 'markup-licence-surrender-start',
            'backUrl' => $this->getBackLink('lva-licence'),
            'startForm' => $this->form
        ]);

        return $variables;
    }
}
