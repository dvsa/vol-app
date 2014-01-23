<?php

namespace unit\Olcs\Controller\VCase;

use \OlcsCommon\Controller\AbstractHttpControllerTestCase;
use \Mockery as m;

/**
 * Tests for the Case Convictions form controller
 *
 * @package		olcs
 * @author		Pelle Wessman <pelle.wessman@valtech.se>
 */
class ConvictionsControllerTest  extends AbstractHttpControllerTestCase
{
    protected $traceError = true;

    public function testForValidDetailsPage()
    {
        $this->mockService('Olcs\Case', 'get', array(
            'caseId' => 1337,
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
        ))->with('1337');

        $this->mockService('Olcs\Case', 'get', array(
            'detailCommentId' => 1,
            'detailComment' => '<p>Praesent commodo cursus magna</p>',
        ))->with('1337/detail-comment');

        $this->mockService('Olcs\Case', 'get', array(
            'rows' => array(
                array(
                    'id' => '4',
                    'convictionNumber' => '',
                    'name' => ' ',
                    'defType' => '',
                    'dateOfOffence' => '0000-00-00',
                    'dateOfConviction' => '0000-00-00',
                    'description' => 'adsfdsf',
                    'courtFpn' => '',
                    'penalty' => '',
                    'msi' => 'N',
                    'decToTc' => 'N',
                    'version' => '1',
                ),
            ),
        ))->with('1337/convictions', m::any());

        $this->dispatch('/case/6/1337/convictions', 'GET');
        $this->assertResponseStatusCode(200);
        $this->assertControllerClass('ConvictionsController');
    }

    public function testForInvalidDetailsPage()
    {
        $this->mockService('Olcs\Case', 'get', false)->with('1338');

        $this->dispatch('/case/123/1338/convictions', 'GET');
        $this->assertResponseStatusCode(404);
        $this->assertControllerClass('ConvictionsController');
    }

    public function testForFormPost()
    {
        $this->mockService('Olcs\Case', 'put', array('commentId' => 2))->with('5/detail-comment', m::any());

        $this->dispatch('/case/convictions', 'POST', array(
            'caseId' => '5',
            'detailTypes' => 1,
            'caseDetailsNote' => 'Case submission note 1',
        ));
        $this->assertResponseStatusCode(302);
        $this->assertControllerClass('ConvictionsController');
    }
}
