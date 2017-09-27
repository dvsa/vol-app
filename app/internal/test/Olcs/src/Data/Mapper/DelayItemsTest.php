<?php
namespace OlcsTest\Data\Mapper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\DelayItems as Sut;
use Zend\Form\Form;

/**
 * DelayItems Mapper Test
 */
class DelayItemsTest extends MockeryTestCase
{
    public function testMapFromErrors()
    {
        $mockForm = new Form();

        $errors = [
            'error' => ['error1'],
        ];

        $this->assertEquals($errors, Sut::mapFromErrors($mockForm, $errors));
    }

    public function testMapFromForm()
    {
        $data = [
            'fields' => ['nextReviewDate' => '2018-01-01']
        ];

        $expected = [
            'nextReviewDate' => '2018-01-01'
        ];

        $this->assertEquals($expected, Sut::mapFromForm($data));
    }

    public function testMapFromResult()
    {
        $data = [];
        $data['fields'] = [
            'ids' => '100,200,300',
            'nextReviewDate' => '2018-01-01',
        ];

        $expected = [
            'ids' => [
                100,
                200,
                300
            ],
            'nextReviewDate' => '2018-01-01',
        ];

        $this->assertEquals($expected, Sut::mapFromResult($data));
    }
}
