<?php

namespace OlcsTest\Data\Mapper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\OperatorTransfer as Sut;
use Laminas\Form\Form;

/**
 * OperatorTransfer Mapper Test
 */
class OperatorTransferTest extends MockeryTestCase
{
    /**
     * @dataProvider fromErrorsProvider
     */
    public function testFromErrors($messages, $expected)
    {

        $mockForm = m::mock(\Laminas\Form\FormInterface::class)
            ->shouldReceive('setMessages')
            ->with($expected)
            ->once()
            ->getMock();

        $this->assertNull(Sut::mapFromErrors($mockForm, $messages));
    }

    public function fromErrorsProvider()
    {
        return [
            [
                [Sut::ERR_NO_LICENCES => 'error1'],
                ['licenceIds' => ['error1']]
            ],
            [
                [Sut::ERR_INVALID_ID => 'error2'],
                ['toOperatorId' => ['error2']]
            ],
            [
                ['unknown' => 'error3'],
                ['toOperatorId' => ['form.operator-merge.to-operator-id.validation']]
            ],
        ];
    }
}
