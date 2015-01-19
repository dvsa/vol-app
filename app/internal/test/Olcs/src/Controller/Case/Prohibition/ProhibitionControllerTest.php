<?php

namespace OlcsTest\Controller;

use Olcs\Controller\Cases\Prohibition\ProhibitionController;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Olcs\TestHelpers\ControllerPluginManagerHelper;
use Olcs\TestHelpers\ControllerDetailsActionHelper;

/**
 * Class ProhibitionControllerTest
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class ProhibitionControllerTest extends MockeryTestCase
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
        $this->sut = new ProhibitionController();
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
        $placeholderName = 'prohibition';

        $this->sut->setPluginManager($this->detailsHelper->getPluginManager(['prohibition' => $id]));

        $mockServiceManager = $this->detailsHelper->getServiceManager($expectedResult, $mockRestData, $placeholderName);
        $this->sut->setServiceLocator($mockServiceManager);

        $data = $this->sut->detailsAction();

        $this->assertEquals($data, $expectedResult);
    }

    public function testDetailsActionNotFound()
    {
        $id = null;
        $mockRestData = false;
        $expectedResult = null;
        $placeholderName = 'prohibition';

        $this->sut->setPluginManager($this->detailsHelper->getPluginManager(['prohibition' => $id]));

        $mockServiceManager = $this->detailsHelper->getServiceManager($expectedResult, $mockRestData, $placeholderName);
        $this->sut->setServiceLocator($mockServiceManager);

        $this->sut->setEvent($this->detailsHelper->getNotFoundEvent());

        $data = $this->sut->detailsAction();

        $this->assertEquals($data, $expectedResult);
    }
}
