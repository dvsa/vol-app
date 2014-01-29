<?php
namespace OlcsSelfserve\Controller;

use \OlcsCommon\Controller\AbstractHttpControllerTestCase;
use \Mockery as m;

class LookupControllerTest extends AbstractHttpControllerTestCase
{
    protected $traceError = true;

    public function testPersonFirstnameSearchRedirect()
    {
        $this->dispatch('/search', 'POST', array('firstName' => 'Bob','submit' => 'Search'));
        $this->assertResponseStatusCode(302);
        $this->assertControllerClass('LookupController');
        $this->assertRedirectTo('/search/person-results?firstName=Bob');
    }

    public function testPersonLastnameSearchRedirect()
    {
        $this->dispatch('/search', 'POST', array('lastName' => 'Smith','submit' => 'Search'));
        $this->assertResponseStatusCode(302);
        $this->assertControllerClass('LookupController');
        $this->assertRedirectTo('/search/person-results?lastName=Smith');
    }

    public function testPersonDobSearchRedirect()
    {
        $this->dispatch('/search', 'POST', array('dobDay' => '1',
            'dobMonth' => '1',
            'dobYear' => '1974',
            'submit' => 'Search'));
        $this->assertResponseStatusCode(302);
        $this->assertControllerClass('LookupController');
        $this->assertRedirectTo('/search/person-results?dobDay=1&dobMonth=1&dobYear=1974');
    }

    public function testOperatorSearchRedirect()
    {
        $this->dispatch('/search', 'POST', array('licenceNumber' => '12345678','submit' => 'Search'));
        $this->assertResponseStatusCode(302);
        $this->assertControllerClass('LookupController');
        $this->assertRedirectTo('/search/operator-results/page/1/10?licenceNumber=12345678');
    }

    public function testPersonResultsPage()
    {
        $this->mockService('Olcs\Lookup', 'get', array(
            "rows" => array(
                array(
                    "personId" => 123,
                    "lastName" => "Anthony",
                    "firstName" => "David",
                    "dob" => "1973-03-03T00:00:00+0000"
                ),
            ),
        ))->with(m::any());

        $this->dispatch('/search/person-results', 'GET', array('firstName' => 'David'));
        $this->assertResponseStatusCode(200);
        $this->assertControllerClass('LookupController');
    }

    public function testSearchEmpty() {
       $this->dispatch('/search', 'POST', array('data'=>'this is the data','submit' => 'Search'));
        $this->assertResponseStatusCode(302);
        $this->assertControllerClass('LookupController');
    }

    public function testOperatorResultsPage()
    {
        $this->mockService('Olcs\Lookup', 'get', array(
            "count" => "5",
            "rows" => array(
                array(
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
                ),
            ),
        ))->with(array(
            'type' => 'licence',
            'limit' => 10,
            'search' => array(
                'operatorTradingName' => 'o'
            )
        ));

        $this->dispatch('/search/operator-results?operatorTradingName=o');
        $this->assertResponseStatusCode(200);
        $this->assertControllerClass('LookupController');
    }
}
