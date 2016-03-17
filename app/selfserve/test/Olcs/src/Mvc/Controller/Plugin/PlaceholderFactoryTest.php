<?php

/**
 * PlaceholderFactory Test
 */
namespace OlcsTest\Mvc\Controller\Plugin;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Mvc\Controller\Plugin\Placeholder;
use Olcs\Mvc\Controller\Plugin\PlaceholderFactory;
use Zend\View\Helper\Placeholder as ViewPlaceholder;

/**
 * PlaceholderFactory Test
 */
class PlaceholderFactoryTest extends MockeryTestCase
{
    public function testCreateService()
    {
        $viewPlaceholder = new ViewPlaceholder();

        $mockSl = m::mock(\Zend\ServiceManager\ServiceLocatorInterface::class);
        $mockSl->shouldReceive('getServiceLocator->get')->with('ViewHelperManager')->once()->andReturnSelf()
            ->shouldReceive('get')->with('placeholder')->once()->andReturn($viewPlaceholder);

        $sut = new PlaceholderFactory();
        $obj = $sut->createService($mockSl);

        $this->assertInstanceOf(Placeholder::class, $obj);
    }
}
