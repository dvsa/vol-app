<?php

namespace Olcs\Controller\VCase;

use \OlcsCommon\Controller\AbstractHttpControllerTestCase;
use \Mockery as m;

/**
 * Description of SubmissionControllerTest
 *
 * @author valtechuk
 */
class SubmissionControllerTest  extends AbstractHttpControllerTestCase
{
    protected $traceError = true;

    public function testSubmissionGeneratorAction()
    {
        $this->mockService('Olcs\Case', 'get', array(
          'caseId' => 1,
          'caseNumber' => 100001,
          'caseOwner' => '6',
          'caseType' => 'licence',
          'caseStatus' => 'open',
          'openTime' => '2013-10-05T18:35:34+0000',
          'closedTime' => NULL,
          'categories' => array(
              'Convictions',
              'Section 9',
          ),
          'ecms' => 'abc123',
          'description' => 'Fusce dapibus.',
          'licence' => array(
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
          ),
          'operator' => array(
              "operatorId" => 6,
              "operatorName" => "John Smith Haulage Ltd.",
              "entityType" => "Registered company",
          ),
          'vehicleCount' => 25,
          'trailerCount' => 13,
          'vehicleInPossession' => 0,
          'mlh' => 'Y',
          'owners' => array(
              'header' => 'Not Defined',
              'list' => array(
                  array(
                      'lastName' => 'Smith',
                      'firstName' => 'John',
                      'dob' => '1960-02-15T00:00:00+0000',
                  ),
              ),
          ),
          'operating-centres' => array(
            array(
                'address' => array(
                    'line1' => 'Unit 5',
                    'line2' => '12 Albert Street',
                    'line3' => 'Westpoint',
                    'line4' => '',
                    'town' => 'Leeds',
                    'country' => 'UK',
                    'postcode' => 'LS9 6NA',
                ),
                'vehicle_auth' => 10,
                'trailer_auth' => 5,
                'conditions' => array(
                    'Ensure finance records are submitted back to OTC every 6 months.',
                ),
            ),
          ),
          'licence-conditions' => array(
              'Ensure finance records are submitted back to OTC every 6 months.',
          ),
        ))->with('1/summary');

        $this->mockService('Olcs\Licence', 'get', array(
            'rows' => array(
                array(
                    'lastName' => 'Anthony',
                    'firstName' => 'Tom',
                    'dob' => '1973-12-09T00:00:00+0000',
                    'qualificationType' => array(
                        'CPCSI',
                        'CPCSN',
                    ),
                    'otherLicensesOrApps' => array(
                        array(
                            'number' => 'OB1234568',
                            'status' => 'Valid',
                        ),
                    ),
                    'internalExternal' => 'External',
                ),
            ),
        ))->with('1/transport-managers');

        $this->mockService('Olcs\Case', 'get', array(
            'detailCommentId' => 1,
            'detailComment' => '<p>Praesent commodo cursus magna</p>',
        ))->with('1/detail-comment');

        $this->mockService('Olcs\Submission', 'create', array('submissionId' => 2))->withAnyArgs();

        $this->dispatch('/case/submission/generate', 'POST', array('caseId' => '1'));
        $this->assertResponseStatusCode(302);
        $this->assertControllerClass('SubmissionController');
    }

    public function testSubmissionGeneratorActionNonPost()
    {
        $this->dispatch('/case/submission/generate','GET', array('caseId' => '1'));
        $this->assertResponseStatusCode(405);
        $this->assertControllerClass('SubmissionController');
    }

    public function testSubmissionGeneratorActionInvalidCase()
    {
        $this->mockService('Olcs\Case', 'get', false)->with('1/summary');

        $this->dispatch('/case/submission/generate','POST', array('caseId' => '1'));
        $this->assertResponseStatusCode(404);
        $this->assertControllerClass('SubmissionController');
    }
}
