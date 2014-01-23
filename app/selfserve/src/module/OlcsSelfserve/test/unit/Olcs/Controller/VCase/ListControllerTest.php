<?php

namespace Olcs\Controller\VCase;

use \OlcsCommon\Controller\AbstractHttpControllerTestCase;
use \Mockery as m;

/**
 * Tests for the Case List controller
 *
 * @package olcs
 * @author  Pelle Wessman <pelle.wessman@valtech.se>
 */
class ListControllerTest extends AbstractHttpControllerTestCase
{
    protected $traceError = true;

    public function testForValidListPage()
    {
        $this->mockService('Olcs\Licence', 'get', array(
          'licenceId' => 1,
          'licenceNumber' => 'OB1234567',
          'licenceStatus' => 'Valid',
          'licenceType' => 'Standard National',
          'fabsReference' => '',
          'tradeType' => '',
          'goodsOrPsv' => '',
          'startDate' => '2010-12-01T00:00:00+0000',
          'reviewDate' => '2010-12-01T00:00:00+0000',
          'endDate' => '2010-12-01T00:00:00+0000',
          'address' => array(
              'line1' => 'Unit 10',
              'line2' => '10 High Street',
              'line3' => 'Alwoodley',
              'line4' => '',
              'town' => 'Leeds',
              'country' => 'United Kingdom',
              'postcode' => 'LS7 9SD',
          ),
          'caseCount' => 4,
          'operator' => array(
              "operatorId" => 6,
              "operatorName" => "John Smith Haulage Ltd.",
              "entityType" => "Registered company",
          ),
          'tradingNames' => array(
              'Test',
              'Foobar',
          ),
        ))->with('1');

        $this->mockService('Olcs\Licence', 'get', array(
            "count" => 1,
            "rows" => array(
                array(
                  'caseId' => 1,
                  'caseNumber' => 100001,
                  'caseOwner' => '6',
                  'caseType' => 'licence',
                  'caseStatus' => 'open',
                  'openTime' => '2013-10-05T18:35:34+0000',
                  'closedTime' => NULL,
                  'categories' => array(
                    0 => 'Convictions',
                    1 => 'Section 9',
                  ),
                  'ecms' => 'abc123',
                  'description' => 'Fusce dapibus.',
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
                ),
            ),
        ))->with('1/cases', m::any());

        $this->dispatch('/case/list/1', 'GET');
        $this->assertResponseStatusCode(200);
        $this->assertControllerClass('ListController');
    }

    public function testForInvalidListPage()
    {
        $this->mockService('Olcs\Licence', 'get', false)->with('5555555');

        //$this->dispatch('/case/list', 'GET', array('licenceId' => '5555555'));
        $this->dispatch('/case/list/5555555', 'GET');
        $this->assertResponseStatusCode(404);
        $this->assertControllerClass('ListController');
    }

    public function testForEmptyListPage()
    {
        $this->mockService('Olcs\Licence', 'get', array(
            'licenceId' => 1,
            'licenceNumber' => 'OB1234567',
            'licenceStatus' => 'Valid',
            'licenceType' => 'Standard National',
            'fabsReference' => '',
            'tradeType' => '',
            'goodsOrPsv' => '',
            'startDate' => '2010-12-01T00:00:00+0000',
            'reviewDate' => '2010-12-01T00:00:00+0000',
            'endDate' => '2010-12-01T00:00:00+0000',
            'address' => array(
                'line1' => 'Unit 10',
                'line2' => '10 High Street',
                'line3' => 'Alwoodley',
                'line4' => '',
                'town' => 'Leeds',
                'country' => 'United Kingdom',
                'postcode' => 'LS7 9SD',
            ),
            'caseCount' => 4,
            'operator' => array(
                "operatorId" => 6,
                "operatorName" => "John Smith Haulage Ltd.",
                "entityType" => "Registered company",
            ),
            'tradingNames' => array(
                'Test',
                'Foobar',
            ),
        ))->with('1');

        $this->mockService('Olcs\Licence', 'get', array(
            "count" => 0,
            "rows" => array(),
        ))->with('1/cases', m::any());

        $this->dispatch('/case/list/1', 'GET');
        $this->assertResponseStatusCode(200);
        $this->assertControllerClass('ListController');
    }
}
