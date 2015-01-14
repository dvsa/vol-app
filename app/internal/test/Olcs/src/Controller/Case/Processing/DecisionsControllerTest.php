<?php

namespace OlcsTest\Controller;

use Olcs\Controller\Cases\Processing\DecisionsController;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Olcs\TestHelpers\ControllerPluginManagerHelper;
use Olcs\TestHelpers\ControllerDetailsActionHelper;

/**
 * Class DecisionsControllerTest
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class DecisionsControllerTest extends MockeryTestCase
{
    /**
     * @var ControllerPluginManagerHelper
     */
    protected $pluginManagerHelper;

    /**
     * @var ControllerDetailsActionHelper
     */
    protected $detailsHelper;

    public function setUp()
    {
        $this->sut = new DecisionsController();
        $this->pluginManagerHelper = new ControllerPluginManagerHelper();
        $this->detailsHelper = new ControllerDetailsActionHelper();

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

        $this->sut->setPluginManager($this->detailsHelper->getPluginManager(['case' => $id]));

        $mockServiceManager = $this->detailsHelper->getServiceManager($expectedResult, $mockRestData, $placeholderName);
        $this->sut->setServiceLocator($mockServiceManager);

        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $this->sut->detailsAction());
    }

    public function testDetailsActionNotFound()
    {
        $id = null;
        $mockRestData = false;
        $expectedResult = null;
        $placeholderName = 'case';

        $this->sut->setPluginManager($this->detailsHelper->getPluginManager(['case' => $id]));

        $mockServiceManager = $this->detailsHelper->getServiceManager($expectedResult, $mockRestData, $placeholderName);
        $this->sut->setServiceLocator($mockServiceManager);

        $this->sut->setEvent($this->detailsHelper->getNotFoundEvent());

        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $this->sut->detailsAction());
    }
}
