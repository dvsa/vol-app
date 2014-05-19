<?php

/**
 * Search controller form post tests
 *
 * @author adminmwc
 */

namespace OlcsTest\Controller\Submission;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class SubmissionSectionTraitTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__.'/../../../../../'  . 'config/application.config.php'
        );
        
        parent::setUp();
        
        $this->submissionConfig = array(
            'sections' => array(
                'case-summary-info' => array(
                    'view' => 'submission/partials/case-summary',
                    'dataPath' => 'VosaCase',
                    'bundle' => array(
                        'children' => array(
                            'categories' => array(
                                'properties' => array(
                                    'id',
                                    'name'
                                )
                            ),
                            'convictions' => array(
                                'properties' => 'ALL'
                            ),
                            'licence' => array(
                                'properties' => 'ALL',
                                'children' => array(
                                    'trafficArea' => array(
                                        'properties' => 'ALL'
                                    ),
                                    'organisation' => array(
                                        'properties' => 'ALL'
                                    )
                                )
                            )
                        )
                    )
                )
            )
        );
    }
    
    public function testCreateSubmission()
    {
        $submissionSectionTrait = $this->getMockForTrait(
            '\Olcs\Controller\Submission\SubmissionSectionTrait',
            array(),
            '',
            true,
            true,
            true,
            array(
                'makeRestCall',
                'createSubmissionSection',
            )
        );
        
        $routeParams = array('licence' => 7);
        
        $submissionSectionTrait->expects($this->once())
            ->method('makeRestCall')
            ->with('Licence', 'GET', array('id' => $routeParams['licence']))
            ->will($this->returnValue(array('id' => 7)));
        
        $submissionSectionTrait->submissionConfig = array(
            'sections' => array(
                'case-summary-info' => array(
                    'view' => 'submission/partials/case-summary',
                    'dataPath' => 'VosaCase',
                    'bundle' => array(
                        'children' => array(
                            'categories' => array(
                                'properties' => array(
                                    'id',
                                    'name'
                                )
                            ),
                            'convictions' => array(
                                'properties' => 'ALL'
                            ),
                            'licence' => array(
                                'properties' => 'ALL',
                                'children' => array(
                                    'trafficArea' => array(
                                        'properties' => 'ALL'
                                    ),
                                    'organisation' => array(
                                        'properties' => 'ALL'
                                    )
                                )
                            )
                        )
                    )
                )
            )
        );
        
        $submissionSectionTrait->expects($this->once())
            ->method('createSubmissionSection')
            ->with('case-summary-info', $submissionSectionTrait->submissionConfig['sections']['case-summary-info'])
            ->will($this->returnValue(array('data' => array())));
        
        $result = $submissionSectionTrait->createSubmission($routeParams);
    }
    
    public function testCreateSubmissionSection()
    {
        $submissionSectionTrait = $this->getMockForTrait(
            '\Olcs\Controller\Submission\SubmissionSectionTrait',
            array(),
            '',
            true,
            true,
            true,
            array(
                'makeRestCall',
                'getParams',
                'getFilteredSectionData'
            )
        );
        
        $submissionSectionTrait->expects($this->once())
            ->method('getParams')
            ->with(array('case'))
            ->will($this->returnValue(array('case' => 54)));
        
         $submissionSectionTrait->expects($this->once())
            ->method('makeRestCall')
            ->with('VosaCase', 'GET', array('id' => 54))
            ->will($this->returnValue(array('id' => 7)));
         
         $submissionSectionTrait->expects($this->once())
            ->method('getFilteredSectionData')
            ->with('CaseSummaryInfo', array('id' => 7))
            ->will($this->returnValue(array('caseNumber' => 'x1234567')));
        
        $result = $submissionSectionTrait->createSubmissionSection(
            'case-summary-info',
            $this->submissionConfig['sections']['case-summary-info']
        );
    }
    
    public function testCaseSummaryInfo()
    {
        $submissionSectionTrait = $this->getMockForTrait(
            '\Olcs\Controller\Submission\SubmissionSectionTrait',
            array(),
            '',
            true,
            true,
            true,
            array()
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
        $this->assertContains('Court 12', $result[0]);
        $this->assertArrayHasKey('dateOfConviction', $result[0]);
    }
    
    public function testPersons()
    {
        $submissionSectionTrait = $this->getMockForTrait(
            '\Olcs\Controller\Submission\SubmissionSectionTrait'
        );
        $data = array(
            'licence' => array(
                'organisation' => array(
                    'organisationOwners' => array(
                        array(
                            'person' => array(
                                'surname' => 'Smith',
                                'firstName' => 'Fred',
                                'dateOfBirth' => '1975-03-15T00:00:00+0000'
                            )
                        )
                    )
                )
            ),
        );
        
        $result = $submissionSectionTrait->persons($data);
        $this->assertContains('Fred', $result[0]);
        $this->assertArrayHasKey('firstName', $result[0]);
    }
    
    public function testTransportManagers()
    {
        $submissionSectionTrait = $this->getMockForTrait(
            '\Olcs\Controller\Submission\SubmissionSectionTrait'
        );
        $data = array(
            'licence' => array(
                'transportManagerLicences' => array(
                    array(
                        'transportManager' =>  array(
                            'tmType' => 'Internal',
                            'qualifications' => array(
                                array(
                                    'qualificationType' => 'CPCSI'
                                )
                            ),
                            'contactDetails' => array(
                                'person' => array(
                                    'surname' => 'Smith',
                                    'firstName' => 'Fred',
                                    'dateOfBirth' => '1975-03-15T00:00:00+0000'
                                )
                            )
                        )
                    )
                )
            )
        );
        
        $result = $submissionSectionTrait->transportManagers($data);
        $this->assertContains('Fred', $result[0]);
        $this->assertArrayHasKey('firstName', $result[0]);
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
