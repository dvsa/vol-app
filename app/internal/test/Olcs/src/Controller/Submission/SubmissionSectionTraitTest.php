<?php

/**
 * Search controller form post tests
 *
 * @author adminmwc <michael.cooper@valtech.co.uk>
 */
namespace OlcsTest\Controller\Submission;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

/**
 * Search controller form post tests
 *
 * @author adminmwc <michael.cooper@valtech.co.uk>
 */
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
                                'properties' => 'ALL',
                                'children' => array(
                                    'category' => array(
                                        'properties' => array(
                                            'id',
                                            'description'
                                        )
                                    )
                                )
                            ),
                            'licence' => array(
                                'properties' => 'ALL',
                                'children' => array(
                                    'trafficArea' => array(
                                        'properties' => 'ALL'
                                    ),
                                    'organisation' => array(
                                        'properties' => 'ALL',
                                        'children' => array(
                                            'organisationOwners' => array(
                                                'properties' => 'ALL',
                                                'children' => array(
                                                    'person' => array(
                                                        'properties' => 'ALL'
                                                    )
                                                )
                                            )
                                        )
                                    ),
                                    'transportManagerLicences' => array(
                                        'properties' => 'ALL',
                                        'children' => array(
                                            'transportManager' => array(
                                                'properties' => 'ALL',
                                                'children' => array(
                                                    'qualifications' => array(
                                                        'properties' => 'ALL'
                                                    ),
                                                    'contactDetails' => array(
                                                        'properties' => 'ALL',
                                                        'children' => array(
                                                            'person' => array(
                                                                'properties' => 'ALL'
                                                            )
                                                        )
                                                    )
                                                )
                                            )
                                        )
                                    )
                                )
                            )
                        )
                    )
                ),
                'persons' => array(
                    'view' => 'submission/partials/persons'
                ),
                'transport-managers' => array(
                    'view' => 'submission/partials/transport-managers',
                    'exclude' => array(
                        'column' => 'licenceType',
                        'values' => array(
                            'standard national',
                            'standard international'
                        )
                    )
                ),
                'outstanding-applications' => null,
                'objections' => null,
                'representations' => null,
                'complaints' => null,
                'environmental' => null,
                'previous-history' => null,
                'operating-centre' => null,
                'conditions' => null,
                'undertakings' => null,
                'annual-test-history' => null,
                'prohibition-history' => null,
                'conviction-history' => array(
                    'view' => 'submission/partials/conviction-history',
                ),
                'bus-services-registered' => array(
                    'exclude' => array(
                        'column' => 'goodsOrPsv',
                        'values' => array(
                            'psv',
                        )
                    )
                ),
                'bus-compliance-issues' => array(
                    'exclude' => array(
                        'column' => 'goodsOrPsv',
                        'values' => array(
                            'psv',
                        )
                    )
                ),
                'current-submission' => null
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

    /**
     * @dataProvider convictionHistoryProvider
     *
     * @param int $categoryId
     * @param string $operatorName
     */
    public function testConvictionHistory($categoryId, $operatorName)
    {
        $submissionSectionTrait = $this->getMockForTrait(
            '\Olcs\Controller\Submission\SubmissionSectionTrait',
            array(),
            '',
            true,
            true,
            true,
            array(
                'getServiceLocator'
            )
        );

        $configServiceLocator = $this->getMock('\stdClass', array('get'));

        $configServiceLocator->expects($this->once())
            ->method('get')
            ->with('Config')
            ->will($this->returnValue($this->getStaticDefTypes()));

        $data = array('convictions' => []);
        $thisConviction['dateOfOffence'] = '2014-01-01';
        $thisConviction['category']['id'] = $categoryId;
        $thisConviction['category']['description'] = 'category description';
        $thisConviction['categoryText'] = 'category text';
        $thisConviction['defType'] = 'defendant_type.operator';
        $thisConviction['operatorName'] = $operatorName;
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

        $submissionSectionTrait->expects($this->once())
            ->method('getServiceLocator')
            ->will($this->returnValue($configServiceLocator));

        $result = $submissionSectionTrait->convictionHistory($data);
        $this->assertContains('Court 12', $result[0]);
        $this->assertArrayHasKey('dateOfConviction', $result[0]);
    }

    /**
     * Data provider for testConvictionHistory
     *
     * @return array
     */
    public function convictionHistoryProvider()
    {
        return array(
            array(1,'Operator Name'),
            array(168,'')
        );
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

    private function getStaticDefTypes()
    {
        return array(
            'static-list-data' => array(
                'defendant_types' =>
                [
                    'defendant_type.operator' => 'Operator',
                    'defendant_type.owner' => 'Owner',
                    'defendant_type.partner' => 'Partner',
                    'defendant_type.director' => 'Director',
                    'defendant_type.driver' => 'Driver',
                    'defendant_type.transport_manager' => 'Transport Manager',
                    'defendant_type.other' => 'Other'
                ]
            )
        );
    }
}
