<?php

namespace OlcsTest\View\Helper;

use Common\Service\Helper\TranslationHelperService;
use Psr\Container\ContainerInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\View\Helper\SubmissionSectionMultipleTables;
use Olcs\View\Helper\SubmissionSectionMultipleTablesFactory;
use Mockery as m;

class SubmissionSectionMultipleTablesFactoryTest extends MockeryTestCase
{
    public function testInvoke()
    {
        $mockTranslator = m::mock(TranslationHelperService::class);

        $mockServiceLocator = m::mock(ContainerInterface::class);
        $mockServiceLocator->shouldReceive('get')->with(TranslationHelperService::class)
            ->andReturn($mockTranslator);

        $sut = new SubmissionSectionMultipleTablesFactory();
        $service = $sut->__invoke($mockServiceLocator, SubmissionSectionMultipleTables::class);

        $this->assertInstanceOf(SubmissionSectionMultipleTables::class, $service);
    }
}
