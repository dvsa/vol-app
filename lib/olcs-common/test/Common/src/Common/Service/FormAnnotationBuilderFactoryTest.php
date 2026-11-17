<?php

declare(strict_types=1);

namespace CommonTest\Service;

use Laminas\Form\Annotation\AttributeBuilder;
use Psr\Container\ContainerInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Common\Service\FormAnnotationBuilderFactory;

final class FormAnnotationBuilderFactoryTest extends MockeryTestCase
{
    public function testInvoke(): void
    {
        $mockFormFactory = m::mock(\Laminas\Form\FormElementManager::class);
        $mockValidatorManager = m::mock(\Laminas\Validator\ValidatorPluginManager::class);
        $mockFilterManager = m::mock(\Laminas\Filter\FilterPluginManager::class);

        $mockServiceLocator = m::mock(ContainerInterface::class);
        $mockServiceLocator->shouldReceive('get')->with('FormElementManager')->andReturn($mockFormFactory);
        $mockServiceLocator->shouldReceive('get')->with('ValidatorManager')->andReturn($mockValidatorManager);
        $mockServiceLocator->shouldReceive('get')->with('FilterManager')->andReturn($mockFilterManager);

        $sut = new FormAnnotationBuilderFactory();
        $this->assertInstanceOf(
            AttributeBuilder::class,
            $sut->__invoke($mockServiceLocator, AttributeBuilder::class)
        );
    }
}
