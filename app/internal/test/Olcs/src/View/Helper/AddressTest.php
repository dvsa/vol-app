<?php

namespace OlcsTest\View\Helper;

use Olcs\View\Helper\Address as AddressHelper;

/**
 * Class AddressTest
 * @package OlcsTest\View\Helper
 */
class AddressTest extends \PHPUnit\Framework\TestCase
{
    public function testInvoke()
    {
        $address = array (
            'addressLine1' => 'Unit 9',
            'addressLine2' => 'Shapely Industrial Estate',
            'addressLine3' => 'Harehills',
            'addressLine4' => '',
            'postcode' => 'LS9 2FA',
            'town' => 'Leeds'
        );

        $string = 'Unit 9, Shapely Industrial Estate, Harehills, Leeds, LS9 2FA';

        $addressHelper = new AddressHelper();

        $this->assertEquals($string, $addressHelper->__invoke($address));
    }
}
