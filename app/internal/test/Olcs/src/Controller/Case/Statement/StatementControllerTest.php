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
}
