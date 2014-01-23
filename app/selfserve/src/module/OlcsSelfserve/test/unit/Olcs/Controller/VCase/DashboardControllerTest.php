<?php

namespace unit\Olcs\Controller\VCase;

use \OlcsCommon\Controller\AbstractHttpControllerTestCase;
use \Mockery as m;

/**
 * Tests for the Case Dashboard controller
 *
 * @package olcs
 * @author Pelle Wessman <pelle.wessman@valtech.se>
 */
class DashboardControllerTest  extends AbstractHttpControllerTestCase
{
    protected $traceError = true;

    public function testForValidDashboardPage()
    {
        $this->mockService('Olcs\Case', 'get', array(
            'caseId' => 237,
            'caseNumber' => 100002,
            'caseOwner' => '6',
            'caseType' => 'licence',
            'caseStatus' => 'open',
            'openTime' => '2013-10-05T21:57:14+0000',
            'closedTime' => NULL,
            'categories' => array(
                'Repute / professional comptenece of TM',
            ),
            'ecms' => 'bcd234',
            'description' => 'fdsgdsf',
            'licence' => array(
                'licenceId' => 6,
                'licenceNumber' => 'OB1234567',
                'licenceStatus' => 'Valid',
                'licenceType' => 'Standard National',
                'fabsReference' => '',
                'tradeType' => '',
                'goodsOrPsv' => '',
                'startDate' => '2010-12-01T00:00:00+0000',
                'reviewDate' => '2010-12-01T00:00:00+0000',
                'endDate' => '2010-12-01T00:00:00+0000',
            ),
            'operator' => array(
                "operatorId" => 6,
                "operatorName" => "John Smith Haulage Ltd.",
                "entityType" => "Registered company",
            ),
        ))->with('237');

        $this->mockService('Olcs\Case', 'get', array(
            'rows' => array(
                array(
                    'submissionId' => '4',
                    'submissionNumber' => '400004',
                    'version' => '8',
                    'type' => 'goods',
                    'status' => 'open',
                    'createdAt' => '2013-10-06T02:47:46+0000',
                    'closedAt' => NULL,
                    'text' => array(
                        'details' => '<div>foobar</div>',
                        'person-owner' => '<div>foobar</div>',
                        'transport-manager' => '<div>foobar</div>',
                        'operating-centre' => '<div>foobar</div>',
                        'conditions' => '<div>foobar</div>',
                        'conviction' => '<div>foobar</div>',
                        'recommendations' => '<div>foobar</div>',
                    ),
                    'with' => 'Kevin Rooney ',
                    'urgent' => true,
                    'case' => array(
                        'caseId' => '237',
                    ),
                    'licence' => array(
                        'licenceId' => '6',
                    ),
                ),
            ),
        ))->with('237/submissions', m::any());

        $this->dispatch('/case/6/237/dashboard', 'GET');
        $this->assertResponseStatusCode(200);
        $this->assertControllerClass('DashboardController');
    }

    public function testForInvalidDashboardPage()
    {
        $this->mockService('Olcs\Case', 'get', false)->with('999999');

        $this->dispatch('/case/22/999999/dashboard', 'GET');
        $this->assertResponseStatusCode(404);
        $this->assertControllerClass('DashboardController');
    }
}
