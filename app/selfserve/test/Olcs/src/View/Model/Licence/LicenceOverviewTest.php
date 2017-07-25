<?php

/**
 * Licence Overview Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace OlcsTest\View\Model\Licence;

use Olcs\View\Model\Licence\LicenceOverview;

/**
 * Licence Overview Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class LicenceOverviewTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test constructor with set variables
     * 
     * @group licenceOverview
     */
    public function testSetVariables()
    {
        $data = [
            'licNo' => 1,
            'inForceDate' => '2014-01-01',
            'expiryDate' => '2015-01-01',
            'status' => ['id' => 'status']
        ];
        $overview = new LicenceOverview($data);
        $this->assertEquals($overview->licenceId, 1);
        $this->assertEquals($overview->startDate, '2014-01-01');
        $this->assertEquals($overview->renewalDate, '2015-01-01');
        $this->assertEquals($overview->status, 'status');
    }

    public function testSetVariablesIsExpired()
    {
        $data = [
            'licNo' => 1,
            'inForceDate' => '2014-01-01',
            'expiryDate' => '2015-01-01',
            'status' => ['id' => 'status'],
            'isExpired' => true,
            'isExpiring' => true,
        ];
        $overview = new LicenceOverview($data);
        $this->assertEquals($overview->licenceId, 1);
        $this->assertEquals($overview->startDate, '2014-01-01');
        $this->assertEquals($overview->renewalDate, '2015-01-01');
        $this->assertEquals($overview->status, 'licence.status.expired');
    }

    public function testSetVariablesIsExpiring()
    {
        $data = [
            'licNo' => 1,
            'inForceDate' => '2014-01-01',
            'expiryDate' => '2015-01-01',
            'status' => ['id' => 'status'],
            'isExpired' => false,
            'isExpiring' => true,
        ];
        $overview = new LicenceOverview($data);
        $this->assertEquals($overview->licenceId, 1);
        $this->assertEquals($overview->startDate, '2014-01-01');
        $this->assertEquals($overview->renewalDate, '2015-01-01');
        $this->assertEquals($overview->status, 'licence.status.expiring');
    }
}
