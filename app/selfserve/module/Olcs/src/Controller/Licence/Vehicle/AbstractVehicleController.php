<?php

namespace Olcs\Controller\Licence\Vehicle;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Common\FeatureToggle;
use Common\RefData;
use Common\Service\Cqrs\Exception\NotFoundException;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Util;
use Dvsa\Olcs\Transfer\Query\DvlaSearch\Vehicle;
use Exception;
use Olcs\Controller\AbstractSelfserveController;
use Olcs\Controller\Config\DataSource\DataSourceConfig;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;

abstract class AbstractVehicleController extends AbstractSelfserveController implements ToggleAwareInterface
{
    use Util\FlashMessengerTrait;

    protected $toggleConfig = [
        'default' => [FeatureToggle::DVLA_INTEGRATION]
    ];

    protected $templateConfig =[
        'default' =>  'pages/licence-vehicle'
    ];

    protected $dataSourceConfig = [
        'default' => DataSourceConfig::LICENCE
    ];

    protected $pageTemplate = 'pages/licence-vehicle';

    /** @var  FormHelperService */
    protected $hlpForm;

    /** @var  FlashMessengerHelperService */
    protected $hlpFlashMsgr;

    /**
     * @var int $licenceId
     */
    protected $licenceId;

    /**
     * @param MvcEvent $e
     * @return array|mixed|\Zend\Http\PhpEnvironment\Response|\Zend\Http\Response
     */
    public function onDispatch(MvcEvent $e)
    {
        $this->licenceId = (int)$this->params('licence');
        $this->hlpForm = $this->getServiceLocator()->get('Helper\Form');
        $this->hlpFlashMsgr = $this->getServiceLocator()->get('Helper\FlashMessenger');
        return parent::onDispatch($e);
    }

    /**
     * @param array $params
     * @return ViewModel
     */
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

    /**
     * Get a url based on a named route
     *
     * @param string $route
     * @return string
     */
    protected function getLink(string $route): string
    {
        return $this->url()->fromRoute($route, [], [], true);
    }

    /**
     * @return array
     */
    abstract protected function getViewVariables(): array;


    /**
     * @return bool
     */
    protected function isGoods(): bool
    {
        return $this->data['licence']['goodsOrPsv']['id'] === RefData::LICENCE_CATEGORY_GOODS_VEHICLE;
    }

    /**
     * @param string $vrm
     * @return array
     * @throws Exception
     * @throws NotFoundException
     */
    protected function fetchVehicleData(string $vrm): array
    {
        $response = $this->handleQuery(Vehicle::create([
            'vrm' => $vrm
        ]));

        if (!$response->isOk()) {
            throw new Exception("Bad response: " . $response->getStatusCode());
        }

        if ($response->getResult()['count'] === 0) {
            throw new NotFoundException("Vehicle not found with vrm: $vrm");
        }

        return $response->getResult()['results'][0];
    }
}
