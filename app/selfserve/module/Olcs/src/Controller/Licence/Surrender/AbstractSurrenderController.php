<?php

namespace Olcs\Controller\Licence\Surrender;

use Dvsa\Olcs\Transfer\Command\Surrender\Update;
use Common\Util;
use Dvsa\Olcs\Transfer\Query\Surrender\ByLicence as SurrenderQuery;
use Permits\Controller\Config\FeatureToggle\FeatureToggleConfig;
use Common\Controller\Interfaces\ToggleAwareInterface;
use Olcs\Controller\AbstractSelfserveController;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;
use Dvsa\Olcs\Transfer\Query\Licence\LicenceWithCorrespondenceCd as LicenceQuery;

class AbstractSurrenderController extends AbstractSelfserveController implements ToggleAwareInterface
{
    use Util\FlashMessengerTrait;

    protected $toggleConfig = [
        'default' => FeatureToggleConfig::SELFSERVE_SURRENDER_ENABLED
    ];

    protected $pageTemplate = 'pages/licence-surrender';

    /** @var  \Common\Service\Helper\FormHelperService */
    protected $hlpForm;

    /** @var  \Common\Service\Helper\FlashMessengerHelperService */
    protected $hlpFlashMsgr;

    protected $licenceId;


    protected $licence;


    public function onDispatch(MvcEvent $e)
    {
        $this->licenceId = (int)$this->params('licence');
        $this->licence = $this->getLicence();
        $this->hlpForm = $this->getServiceLocator()->get('Helper\Form');
        $this->hlpFlashMsgr = $this->getServiceLocator()->get('Helper\FlashMessenger');
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

    protected function getBackLink(string $route): string
    {
        return $this->url()->fromRoute($route, [], [], true);
    }


    private function getLicence()
    {
        $response = $this->handleQuery(
            LicenceQuery::create(['id' => (int)$this->params('licence')])
        );

        return $response->getResult();
    }

    protected function getSurrender()
    {
        $response = $this->handleQuery(
            SurrenderQuery::create(['id' => (int)$this->params('licence')])
        );

        return $response->getResult();
    }

    protected function updateSurrender(string $status, array $extraData = []): bool
    {
        $surrender = $this->getSurrender();

        $dtoData = array_merge([
            'id' => $this->licenceId,
            'partial' => false,
            'version' => $surrender['version'],
            'status' => $status,
        ], $extraData);

        $response = $this->handleCommand(Update::create($dtoData));
        return $response->isOk();
    }
}
