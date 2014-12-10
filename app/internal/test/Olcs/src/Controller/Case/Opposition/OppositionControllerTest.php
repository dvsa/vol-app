<?php

/**
 * Opposition Test Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace OlcsTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Mockery as m;

/**
 * Opposition Test Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class OppositionControllerTest extends AbstractHttpControllerTestCase
{
    protected $testClass = 'Olcs\Controller\Cases\Opposition\OppositionController';

    public function indexActionDataProvider()
    {
        return [
            ['2014-04-01T09:43:21+0100', '2014-04-01', '2014-04-22T00:00:00+0100'], //dates are fine
            ['2014-04-02T09:43:21+0100', '2014-04-01', null], //received is before the ad placed date
            ['2014-04-02T09:43:21+0100', null, null] //we don't have an ad placed date
        ];
    }

    /**
     * @dataProvider indexActionDataProvider
     *
     * @param $receivedDate
     * @param $adPlacedDate
     * @param $oorDate
     */
    public function testIndexAction($receivedDate, $adPlacedDate, $oorDate)
    {
        $id = 1;

        $listData = [
            'Results' => [
                0 => [
                    'application' => [
                        'receivedDate' => $receivedDate,
                        'operatingCentres' => [
                            0 => [
                                'adPlacedDate' => $adPlacedDate
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $expectedViewVars = [
            'oooDate' => null,
            'oorDate' => $oorDate
        ];

        $sut = $this->getMock(
            'Olcs\Controller\Cases\Opposition\OppositionController',
            ['getView', 'getIdentifierName', 'checkForCrudAction', 'buildTableIntoView', 'renderView']
        );

        $sut->setListData($listData);

        $view = $this->getMock('\Zend\View\View', ['setTemplate', 'setVariables']);
        $view->expects($this->once())
            ->method('setTemplate')
            ->with('view-new/pages/case/opposition')
            ->will($this->returnSelf());

        $view->expects($this->once())
            ->method('setVariables')
            ->with($expectedViewVars)
            ->will($this->returnSelf());

        $sut->expects($this->once())->method('getView')
            ->will($this->returnValue($view));
        $sut->expects($this->once())->method('getIdentifierName')
            ->will($this->returnValue($id));
        $sut->expects($this->once())->method('checkForCrudAction')
            ->with(null, [], $id)->will($this->returnValue(null));
        $sut->expects($this->once())->method('buildTableIntoView');

        $sut->expects($this->once())->method('renderView')
            ->with($view)->will($this->returnValue($view));

        $this->assertSame($view, $sut->indexAction());
    }
}
