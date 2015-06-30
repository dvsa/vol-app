<?php
namespace OlcsTest\Controller\Submission;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

/**
 * Submission Decision Controller Test
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class DecisionControllerTest extends AbstractHttpControllerTestCase
{
    /**
     * Returns a usable instance of the abstract class.
     *
     * @param array $methods
     * @return \Olcs\Controller\Cases\Submission\DecisionController
     */
    public function getSut(array $methods = null)
    {
        return $this->getMock('Olcs\Controller\Cases\Submission\DecisionController', $methods);
    }

    /**
     * Gives us a mocked rest client.
     *
     * @param string $method Name of the method.
     */
    public function getMockParams($method)
    {
        return $this->getMock('stdClass', [$method], array(), '', false, false);
    }

    public function testProcessLoad()
    {
        $submissionId = 123;
        $inData = [];

        $outData = [];
        $outData['fields']['submission'] = $submissionId;

        $params = $this->getMockParams('fromRoute');
        $params->expects($this->once())->method('fromRoute')
               ->with('submission')->will($this->returnValue($submissionId));

        $sut = $this->getSut(['parentProcessLoad', 'params']);
        $sut->expects($this->once())->method('parentProcessLoad')
            ->with($inData)->will($this->returnValue($inData));
        $sut->expects($this->once())->method('params')
            ->will($this->returnValue($params));

        $this->assertSame($outData, $sut->processLoad($inData));
    }

    /**
     * @dataProvider processSaveDataProvider
     */
    public function testProcessSave($isOk)
    {
        $id = 1;
        $submissionId = 123;
        $caseId = 12;
        $data = ['fields' => ['id' => $id]];

        $params = $this->getMockParams('fromRoute');
        $params->expects($this->any())->method('fromRoute')->will(
            $this->returnValueMap(
                [
                    ['id', $id],
                    ['submission', $submissionId],
                    ['case', $caseId],
                ]
            )
        );

        $mockedResponse = $this->getMock('stdClass', ['isOk'], array(), '', false, false);
        $mockedResponse->expects($this->once())->method('isOk')
            ->will($this->returnValue($isOk));

        $mockedDecisionBusinessService = $this->getMock('stdClass', ['process'], array(), '', false, false);
        $mockedDecisionBusinessService->expects($this->once())->method('process')
            ->with(
                [
                    'id' => $id,
                    'data' => $data['fields'],
                    'submissionId' => $submissionId,
                    'caseId' => $caseId,
                ]
            )
            ->will($this->returnValue($mockedResponse));

        $mockedBusinessServiceManager = $this->getMock('stdClass', ['get'], array(), '', false, false);
        $mockedBusinessServiceManager->expects($this->once())->method('get')
            ->with('Cases\Submission\Decision')->will($this->returnValue($mockedDecisionBusinessService));

        $mockedServiceLocator = $this->getMock('stdClass', ['get'], array(), '', false, false);
        $mockedServiceLocator->expects($this->once())->method('get')
            ->with('BusinessServiceManager')->will($this->returnValue($mockedBusinessServiceManager));

        $sut = $this->getSut(
            ['getServiceLocator', 'params', 'addSuccessMessage', 'addErrorMessage', 'redirectToRoute']
        );
        $sut->expects($this->once())->method('getServiceLocator')
            ->will($this->returnValue($mockedServiceLocator));
        $sut->expects($this->any())->method('params')
            ->will($this->returnValue($params));
        $sut->expects($this->exactly($isOk ? 1 : 0))->method('addSuccessMessage');
        $sut->expects($this->exactly($isOk ? 0 : 1))->method('addErrorMessage');
        $sut->expects($this->once())->method('redirectToRoute');

        $sut->processSave($data);
    }

    public function processSaveDataProvider()
    {
        return [
            // success
            [true],
            // error
            [false],
        ];
    }
}
