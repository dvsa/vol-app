<?php

/**
 * Search controller form post tests
 *
 * @author adminmwc
 */

namespace OlcsTest\Controller\Submission;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class SubmissionTraitTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__.'/../../../../../'  . 'config/application.config.php'
        );
        
        parent::setUp();
    }
    
    public function testCaseSummaryInfo()
    {
        $submissionSectionTrait = $this->getMockForTrait(
            '\Olcs\Controller\Submission\SubmissionSectionTrait'
        );
        $data = array(
            'caseNumber' => 54,
            'ecms' => 123123,
            'description' => 'Case 1',
            'licence' => array(
                'licenceNumber' => 7,
                'startDate' => '2014-01-01',
                'authorisedVehicles' => 12,
                'authorisedTrailers' => 4,
                'organisation' => array(
                    'name' => 'Fred SMith',
                    'organisationType' => 'Bus company',
                    'sicCode' => 123345,
                    'isMlh' => 'Y'
                ),
                'licenceType' => 'Standard National'
            )
        );
        $result = $submissionSectionTrait->caseSummaryInfo($data);
        $this->assertContains('Case 1', $result);
        $this->assertArrayHasKey('ecms', $result);
    }
    
    public function testConvictionHistory()
    {
        $submissionSectionTrait = $this->getMockForTrait(
            '\Olcs\Controller\Submission\SubmissionSectionTrait'
        );
        $data = array('convictions' => []);
        $thisConviction['dateOfOffence'] = '2014-01-01';
        $thisConviction['dateOfConviction'] = '2014-01-01';
        $thisConviction['personFirstname'] = 'Fred';
        $thisConviction['personLastname'] = 'Smith';
        $thisConviction['description'] = 'Done for speeding';
        $thisConviction['courtFpm'] = 'Court 12';
        $thisConviction['penalty'] = 'A monkey';
        $thisConviction['si'] = 'Y';
        $thisConviction['decToTc'] = 'Y';
        $thisConviction['dealtWith'] = 'N';
        $data['convictions'][] = $thisConviction;
        
        $result = $submissionSectionTrait->convictionHistory($data);
//print_r($result);
        $this->assertContains('Court 12', $result[0]);
        $this->assertArrayHasKey('dateOfConviction', $result[0]);
    }
    
    public function testGetFilteredSectionData()
    {
        $submissionSectionTrait = $this->getMockForTrait(
            '\Olcs\Controller\Submission\SubmissionSectionTrait'
        );
        
        $data = array(
            'caseNumber' => 54,
            'ecms' => 123123,
            'description' => 'Case 1',
            'licence' => array(
                'licenceNumber' => 7,
                'startDate' => '2014-01-01',
                'authorisedVehicles' => 12,
                'authorisedTrailers' => 4,
                'organisation' => array(
                    'name' => 'Fred SMith',
                    'organisationType' => 'Bus company',
                    'sicCode' => 123345,
                    'isMlh' => 'Y'
                ),
                'licenceType' => 'Standard National'
            )
        );
        
        $result = $submissionSectionTrait->getFilteredSectionData('caseSummaryInfo', $data);
        $this->assertArrayHasKey('ecms', $result);
    }
}
