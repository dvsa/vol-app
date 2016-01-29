<?php
namespace OlcsTest\Data\Mapper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\AdjustTransaction as Sut;
use Zend\Form\FieldsetInterface;
use Zend\Form\FormInterface;

/**
 * AdjustTransaction Mapper Test
 */
class AdjustTransactionTest extends MockeryTestCase
{
    public function testMapFromResult()
    {
        $inData = [
            'id' => 69,
            'version' => 1,
            'paymentMethod' => [
                'id' => 'fpm_cash',
                'description' => 'Cash',
            ],
            'amountAfterAdjustment' => '12.34',
            'payerName' => 'Dan',
            'payingInSlipNumber' => '1234',
            'chequePoNumber' => '2345',
            'chequePoDate' => '2015-12-10',
        ];

        $expected = [
            'details' => [
                'paymentType' => 'fpm_cash',
                'paymentMethod' => 'Cash',
                'received' => '12.34',
                'payer' => 'Dan',
                'slipNo' => '1234',
                'chequeNo' => '2345',
                'poNo' => '2345',
                'chequeDate' => '2015-12-10',
                'id' => 69,
                'version' => 1,
            ],
        ];

        $this->assertEquals($expected, Sut::mapFromResult($inData));
    }

    public function testMapFromForm()
    {
        $inData = [
            'details' => [
                'paymentType' => 'fpm_cash',
                'paymentMethod' => 'Cash',
                'received' => '12.34',
                'payer' => 'Dan',
                'slipNo' => '1234',
                'chequeNo' => '2345',
                'poNo' => '2345',
                'chequeDate' => '2015-12-10',
                'id' => 69,
                'version' => 1,
            ],
        ];
        $expected = [
           'received' => '12.34',
            'payer' => 'Dan',
            'slipNo' => '1234',
            'chequeNo' => '2345',
            'poNo' => '2345',
            'chequeDate' => '2015-12-10',
            'id' => 69,
            'version' => 1,
        ];

        $this->assertEquals($expected, Sut::mapFromForm($inData));
    }

    public function testMapFromErrors()
    {
        $mockForm = m::mock(FormInterface::class);
        $mockField = m::mock();

        $mockForm
            ->shouldReceive('get')
            ->with('details')
            ->once()
            ->andReturn([$mockField]);

        $mockField
            ->shouldReceive('getName')
            ->andReturn('field')
            ->shouldReceive('setMessages')
            ->once()
            ->with([0 => 'invalid']);

        $errors = [
            'messages' => [
                'field' => [
                    0 => 'invalid',
                ],
            ],
        ];

        $result = Sut::mapFromErrors($mockForm, $errors);

        $this->assertEquals([], $result);
    }
}
