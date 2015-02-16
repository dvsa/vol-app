<?php
namespace OlcsTest\Controller\Submission;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

/**
 * Submission Recommendation Controller Test
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class RecommendationControllerTest extends AbstractHttpControllerTestCase
{
    /**
     * Returns a usable instance of the abstract class.
     *
     * @param array $methods
     * @return \Olcs\Controller\Cases\Submission\RecommendationController
     */
    public function getSut(array $methods = null)
    {
        return $this->getMock('Olcs\Controller\Cases\Submission\RecommendationController', $methods);
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
        $userId = 12;
        $inData = [];

        $outData = [];
        $outData['fields']['submission'] = $submissionId;
        $outData['fields']['senderUser'] = $userId;

        $params = $this->getMockParams('fromRoute');
        $params->expects($this->once())->method('fromRoute')
               ->with('submission')->will($this->returnValue($submissionId));

        $sut = $this->getSut(['parentProcessLoad', 'params', 'getLoggedInUser']);
        $sut->expects($this->once())->method('parentProcessLoad')
            ->with($inData)->will($this->returnValue($inData));
        $sut->expects($this->once())->method('params')
            ->will($this->returnValue($params));
        $sut->expects($this->once())->method('getLoggedInUser')
            ->will($this->returnValue($userId));

        $this->assertSame($outData, $sut->processLoad($inData));
    }
}
