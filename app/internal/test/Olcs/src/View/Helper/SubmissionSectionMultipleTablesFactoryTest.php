<?php

namespace OlcsTest\View\Helper;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\View\Helper\SubmissionSectionMultipleTablesFactory;
use Mockery as m;

/**
 * Class SubmissionSectionMultipleTablesFactoryTest
 * @package OlcsTest\Form\Element
 */
class SubmissionSectionMultipleTablesFactoryTest extends MockeryTestCase
{
    public function testCreateService()
    {
        $mockTranslator = m::mock('\Laminas\I18n\Translator\Translator');

        $mockServiceLocator = m::mock('\Laminas\ServiceManager\ServiceLocatorInterface');
        $mockServiceLocator->shouldReceive('getServiceLocator')->andReturnSelf();
        $mockServiceLocator->shouldReceive('get')->with('Translator')
            ->andReturn($mockTranslator);

        $sut = new SubmissionSectionMultipleTablesFactory();
        $service = $sut->createService($mockServiceLocator);

        $this->assertInstanceOf('\Olcs\View\Helper\SubmissionSectionMultipleTables', $service);
    }
}
