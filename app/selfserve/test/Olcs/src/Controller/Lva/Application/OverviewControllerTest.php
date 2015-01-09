<?php

/**
 * Overview Controller Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace OlcsTest\Controller\Lva\Application;

use Olcs\Controller\Lva\Application\OverviewController as Sut;

/**
 * Overview Controller Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class OverviewControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test the helper function that gets the latest fee from an array
     * of outstanding fees.
     *
     * If two fees have the same invoice date, we should get the one with
     * the higher id (primary key)
     *
     * @group application-overview-controller
     */
    public function testGetLatestFee()
    {
        $fees = [
            [
                'amount' => '251.75',
                'invoicedDate' => '2013-11-22T00:00:00+0000',
                'id' => 77,
            ],
            [
                'amount' => '254.40',
                'invoicedDate' => '2013-11-25T00:00:00+0000',
                'id' => 78,
            ],
            [
                'amount' => '250.50',
                'invoicedDate' => '2013-11-25T00:00:00+0000',
                'id' => 76,
            ],
            [
                'amount' => '150.00',
                'invoicedDate' => '2013-11-21T00:00:00+0000',
                'id' => 79,
            ],
        ];

        $sut = new Sut();

        $this->assertEquals(
            [
                'amount' => '254.40',
                'invoicedDate' => '2013-11-25T00:00:00+0000',
                'id' => 78,
            ],
            $sut->getLatestFee($fees)
        );
    }
}
