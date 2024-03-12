<?php

namespace OlcsTest\View\Helper;

use Common\Service\Table\Formatter\Address;
use Olcs\View\Helper\Address as AddressHelper;
use Mockery as m;

/**
 * Class AddressTest
 * @package OlcsTest\View\Helper
 */
class AddressTest extends \PHPUnit\Framework\TestCase
{
    protected $addressFormatter;

    protected function setUp(): void
    {
        $this->addressFormatter = m::mock(Address::class);
        $this->sut = new AddressHelper($this->addressFormatter);
    }
    public function testInvoke()
    {
        $address =  [
            'addressLine1' => 'Unit 9',
            'addressLine2' => 'Shapely Industrial Estate',
            'addressLine3' => 'Harehills',
            'addressLine4' => '',
            'postcode' => 'LS9 2FA',
            'town' => 'Leeds'
        ];

        $string = 'Unit 9, Shapely Industrial Estate, Harehills, Leeds, LS9 2FA';

        $this->addressFormatter->shouldReceive('format')
            ->with($address, ['addressFields' => ['addressLine1', 'addressLine2', 'addressLine3', 'addressLine4', 'town', 'postcode', 'countryCode']])
            ->andReturn($string);

        $this->assertEquals($string, $this->sut->__invoke($address));
    }
}
