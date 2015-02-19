<?php
namespace OlcsTest\Controller\Conviction;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Olcs\TestHelpers\ControllerPluginManagerHelper;
use Olcs\TestHelpers\ControllerDetailsActionHelper;
use Olcs\TestHelpers\ControllerRouteMatchHelper;

/**
 * Offence controller tests
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
class OffenceControllerTest extends MockeryTestCase
{
    /**
     * @var ControllerPluginManagerHelper
     */
    protected $pluginManagerHelper;

    /**
     * @var ControllerDetailsActionHelper
     */
    protected $detailsHelper;

    /**
     * @var ControllerRouteMatchHelper
     */
    protected $routeMatchHelper;

    public function setUp()
    {
        $this->sut = new \Olcs\Controller\Cases\Conviction\OffenceController();

        $this->pluginManagerHelper = new ControllerPluginManagerHelper();
        $this->detailsHelper = new ControllerDetailsActionHelper();
        $this->routeMatchHelper = new ControllerRouteMatchHelper();

        parent::setUp();
    }

    /**
     * Tests the details action
     */
    public function testDetailsAction()
    {
        $id = 1;
        $mockRestData = ['id' => $id];
        $expectedResult = ['id' => $id];
        $placeholderName = 'case';
        $offenceId = 99;

        $this->sut->setPluginManager($this->detailsHelper->getPluginManager(['case' => $id, 'offence' => $offenceId]));

        $mockServiceManager = $this->detailsHelper->getServiceManager($expectedResult, $mockRestData, $placeholderName);
        $this->sut->setServiceLocator($mockServiceManager);

        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $this->sut->detailsAction());
    }
}
