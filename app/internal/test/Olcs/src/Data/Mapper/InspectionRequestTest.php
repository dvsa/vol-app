<?php

namespace OlcsTest\Data\Mapper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\InspectionRequest as Sut;
use Laminas\Form\Form;

/**
 * Inspection Request Mapper Test
 */
class InspectionRequestTest extends MockeryTestCase
{
    public function testMapFromErrors()
    {
        $mockForm = new Form();

        $errors = [
            'reportType' => ['error1'],
            'inspectorName' => ['error2'],
            'general' => ['error3']
        ];

        $expected = [
            'general' => ['error3']
        ];

        $this->assertEquals($expected, Sut::mapFromErrors($mockForm, $errors));
    }

    public function testMapFromForm()
    {
        $data = [
            'data' => ['foo' => 'bar']
        ];

        $expected = [
            'foo' => 'bar'
        ];

        $this->assertEquals($expected, Sut::mapFromForm($data));
    }

    public function testMapEnforcementAreaFromLicence()
    {
        $data = [
            'enforcementArea' => ['name' => 'foo']
        ];

        $this->assertEquals('foo', Sut::mapEnforcementAreaFromLicence($data));
    }

    public function testMapEnforcementAreaFromApplication()
    {
        $data = [
            'licence' => ['enforcementArea' => ['name' => 'foo']]
        ];

        $this->assertEquals('foo', Sut::mapEnforcementAreaFromApplication($data));
    }

    public function testMapFromResultTypeApp()
    {
        $data = [
            'foo' => 'bar',
            'requestDate' => null,
            'application' => ['licence' => ['enforcementArea' => ['name' => 'foo']]]
        ];

        $result = Sut::mapFromResult($data);

        // @note probably need to use Util\DateTime class from backend in common / internal as well
        // in future, to test DateTime fields
        $this->assertInstanceOf('\DateTime', $result['data']['requestDate']);
        $this->assertEquals(
            $result['data']['enforcementAreaName'],
            $data['application']['licence']['enforcementArea']['name']
        );
        $this->assertEquals(
            $result['data']['foo'],
            $data['foo']
        );
    }

    public function testMapFromResultTypeLic()
    {
        $data = [
            'foo' => 'bar',
            'requestDate' => '2015-01-01',
            'licence' => ['enforcementArea' => ['name' => 'foo']]
        ];

        $expected = [
            'data' => [
                'foo' => 'bar',
                'enforcementAreaName' => 'foo',
                'requestDate' => '2015-01-01',
                'licence' => ['enforcementArea' => ['name' => 'foo']]
            ],
        ];

        $result = Sut::mapFromResult($data);

        $this->assertEquals($result, $expected);
    }
}
