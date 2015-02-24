<?php

namespace OlcsTest\View\Helper;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\View\Helper\SubmissionSectionMultipleTablesFactory;
use Mockery as m;
use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager;
use \Olcs\TestHelpers\ControllerPluginManagerHelper;

/**
 * Class SubmissionSectionMultipleTablesFactoryTest
 * @package OlcsTest\Form\Element
 */
class SubmissionSectionMultipleTablesFactoryTest extends MockeryTestCase
{
    public function testCreateService()
    {
        $mockTranslator = m::mock('\Zend\I18n\Translator\Translator');

        $mockServiceLocator = m::mock('\Zend\ServiceManager\ServiceLocatorInterface');
        $mockServiceLocator->shouldReceive('getServiceLocator')->andReturnSelf();
        $mockServiceLocator->shouldReceive('get')->with('Translator')
            ->andReturn($mockTranslator);

        $sut = new SubmissionSectionMultipleTablesFactory();
        $service = $sut->createService($mockServiceLocator);

        $this->assertInstanceOf('\Olcs\View\Helper\SubmissionSectionMultipleTables', $service);
    }
}
