<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Validators;

use Dvsa\Olcs\Transfer\Validators\Xml as XmlValidator;
use Dvsa\Olcs\Transfer\Validators\XmlFactory as XmlFactory;
use Psr\Container\ContainerInterface;
use Laminas\Xml\Security as XmlSecurityValidator;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Mockery as m;

class XmlFactoryTest extends TestCase
{
    public function testInvoke()
    {
        $mockXmlSecurity = m::mock(XmlSecurityValidator::class);

        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')->with(XmlSecurityValidator::class)->andReturn($mockXmlSecurity);

        $sut = new XmlFactory();
        $service = $sut->__invoke($mockSl, XmlValidator::class);

        $this->assertInstanceOf(XmlValidator::class, $service);
        $this->assertInstanceOf(XmlSecurityValidator::class, $service->getSecurityValidator());
    }
}
