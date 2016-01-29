<?php

/**
 * Statement Test Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */

namespace OlcsTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Olcs\Controller\Cases\Statement\StatementController;

/**
 * Statement Test Controller
 */
class StatementControllerTest extends AbstractHttpControllerTestCase
{
    protected $testClass = 'Olcs\Controller\Cases\Statement\StatementController';

    public function testProcessSave()
    {
        $this->markTestSkipped();
        $inData = [
            'requestorsAddress' => [
                'key1' => 'value1',
                'searchPostcode' => 'PC'
            ],
            'fields' => [
                'personId' => '',
                'personVersion' => '',
                'contactDetailsId' => '',
                'contactDetailsVersion' => '',
                'requestorsForename' => 'Joe',
                'requestorsFamilyName' => 'Bloggs',
                'contactDetailsType' => 'ct_corr',
            ]
        ];

        $outData = [
            'requestorsAddress' => [
                'key1' => 'value1'
            ],
            'fields' => [
                'personId' => '',
                'personVersion' => '',
                'contactDetailsId' => '',
                'contactDetailsVersion' => '',
                'requestorsForename' => 'Joe',
                'requestorsFamilyName' => 'Bloggs',
                'contactDetailsType' => 'ct_corr',
                'requestorsContactDetails' => [
                    'id' => '',
                    'version' => '',
                    'contactType' => 'ct_corr',
                    'person' => [
                        'id' => '',
                        'version' => '',
                        'forename' => 'Joe',
                        'familyName' => 'Bloggs',
                    ],
                    'address' => [
                        'key1' => 'value1'
                    ]
                ]
            ]
        ];

        $controller = $this->getMock(
            'Olcs\Controller\Cases\Statement\StatementController',
            ['addSuccessMessage', 'redirectToIndex', 'processDataMapForSave', 'save']
        );

        $controller->expects($this->once())->method('processDataMapForSave')
                   ->with($outData)->will($this->returnValue($outData));

        $controller->expects($this->once())->method('save')
                   ->with($outData)->will($this->returnValue(null));

        $controller->expects($this->once())->method('addSuccessMessage');

        $this->assertNull(null, $controller->processSave($inData));
    }

    public function testProcessLoad()
    {
        $this->markTestSkipped();
        $case = '1';

        $data = [];
        $data['k'] = 'v';

        $oData = [];
        $oData['case'] = $case;
        $oData['fields']['case'] = $case;
        $oData['base']['case'] = $case;
        $oData['k'] = 'v';

        $controller = $this->getMock(
            'Olcs\Controller\Cases\Statement\StatementController',
            ['getQueryOrRouteParam']
        );

        $controller->expects($this->any())->method('getQueryOrRouteParam')
                   ->with('case')->will($this->returnValue($case));

        $this->assertEquals($oData, $controller->processLoad($data));
    }

    /**
     * Tests the generate action
     *
     */
    public function testGenerateAction()
    {
        $this->markTestSkipped();
        $sut = $this->getMock(
            $this->testClass,
            array(
                'getFromRoute',
                'getQueryOrRouteParam',
                'getCase',
                'redirect'
            )
        );

        $getFromRouteValues = [
            'case' => 12,
            'statement' => 34
        ];
        $sut->expects($this->any())
            ->method('getFromRoute')
            ->will(
                $this->returnCallback(
                    function ($key) use ($getFromRouteValues) {
                        return $getFromRouteValues[$key];
                    }
                )
            );

        $sut->expects($this->once())
            ->method('getQueryOrRouteParam')
            ->with('licence')
            ->will($this->returnValue(null));

        $sut->expects($this->once())
            ->method('getCase')
            ->will(
                $this->returnValue(
                    [
                        'id' => 1234,
                        'licence' => [
                            'id' => 56
                        ]
                    ]
                )
            );

        $redirect = $this->getMock('\stdClass', ['toRoute']);
        $redirect->expects($this->once())
            ->method('toRoute')
            ->with(
                'case_licence_docs_attachments/entity/generate',
                ['case' => 12, 'licence' => 56, 'entityType' => 'statement', 'entityId' => 34]
            );
        $sut->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($redirect));

        $sut->generateAction();
    }
}
