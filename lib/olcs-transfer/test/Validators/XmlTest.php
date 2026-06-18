<?php

namespace Dvsa\OlcsTest\Transfer\Validators;

use Dvsa\Olcs\Transfer\Validators\Xml as XmlValidator;
use Laminas\Xml\Security as XmlSecurityValidator;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Mockery as m;
use Laminas\Xml\Exception\RuntimeException;

/**
 * Class XmlTest
 * @package Dvsa\OlcsTest\Transfer\Validators
 */
class XmlTest extends TestCase
{
    /**
     * Test isValid when the security validator throws an exception
     */
    public function testIsValidRuntimeException()
    {
        $xmlString = 'xml string';

        $xmlSecurityValidator = m::mock(XmlSecurityValidator::class);
        $xmlSecurityValidator->shouldReceive('scan')
            ->with($xmlString, m::type(\DOMDocument::class))
            ->andThrow(RuntimeException::class);

        $sut = new XmlValidator();
        $sut->setSecurityValidator($xmlSecurityValidator);

        $this->assertEquals(false, $sut->isValid($xmlString));
    }

    /**
     * Tests isValid when the validator returns true
     */
    public function testIsValidWhenValid()
    {
        $xmlString = 'xml string';

        $xmlSecurityValidator = m::mock(XmlSecurityValidator::class);
        $xmlSecurityValidator->shouldReceive('scan')->with($xmlString, m::type(\DOMDocument::class))->andReturn(true);

        $sut = new XmlValidator();
        $sut->setSecurityValidator($xmlSecurityValidator);

        $this->assertEquals($xmlString, $sut->isValid($xmlString));
    }

    /**
     * Tests isValid when the string isn't valid
     */
    public function testIsValidWhenNotValid()
    {
        $xmlString = 'xml string';

        $xmlSecurityValidator = m::mock(XmlSecurityValidator::class);
        $xmlSecurityValidator->shouldReceive('scan')->with($xmlString, m::type(\DOMDocument::class))->andReturn(false);

        $sut = new XmlValidator();
        $sut->setSecurityValidator($xmlSecurityValidator);

        $this->assertEquals(false, $sut->isValid($xmlString));
    }
}
