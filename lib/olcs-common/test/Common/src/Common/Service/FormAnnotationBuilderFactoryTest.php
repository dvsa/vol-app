<?php

namespace CommonTest\Service;

use Psr\Container\ContainerInterface;
use Laminas\Form\Annotation\AnnotationBuilder;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Common\Service\FormAnnotationBuilderFactory;

class FormAnnotationBuilderFactoryTest extends MockeryTestCase
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
            AnnotationBuilder::class,
            $sut->__invoke($mockServiceLocator, AnnotationBuilder::class)
        );
    }
}
