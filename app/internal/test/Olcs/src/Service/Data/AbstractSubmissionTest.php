<?php

namespace OlcsTest\Service\Data;

use Mockery as m;

/**
 * Class SubmissionTest
 * @package OlcsTest\Service\Data
 */
abstract class AbstractSubmissionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @provider providerSubmissionSectionData
     *
     * @param $sectionId
     */
    public function createSubmissionSection($input, $expected)
    {
        $mockRestClient = $this->getMock('\Common\Util\RestClient', [], [], '', false);
        $mockRestClient->expects($this->any())
            ->method('get')
            ->with(
                '',
                array('id' => $input['caseId'],
                    'bundle' => json_encode($input['sectionConfig']['bundle'])
                )
            )
            ->willReturn($expected['loadedCaseSectionData']);

        $mockApiResolver = $this->getMock('stdClass', ['getClient']);
        $mockApiResolver
            ->expects($this->once())
            ->method('getClient')
            ->with($this->equalTo($input['sectionConfig']['service']))
            ->willReturn($mockRestClient);
        $this->sut->setApiResolver($mockApiResolver);

        $wordFilter = new \Zend\Filter\Word\DashToCamelCase();

        $mockFilterManager = $this->getMock('stdClass', ['get']);
        $filterClass = 'Olcs\Filter\SubmissionSection\\' . ucfirst($wordFilter->filter($input['sectionId']));

        $sectionFilter = new $filterClass;

        $sm = $this->getMock(
            'Zend\ServiceManager\ServiceLocatorInterface',
            [
                'getServiceLocator',
                'setServiceLocator',
                'get',
                'has'
            ]
        );
        $dateTimeProcessor = $this->getMock('stdClass', ['calculateDate']);

        $dateTimeProcessor->expects($this->any())
            ->method('calculateDate')
            ->willReturn('25/12/2000');
        $sm->expects($this->any())
            ->method('getServiceLocator')
            ->willReturnSelf();
        $sm->expects($this->any())
            ->method('get')
            ->with('Common\Util\DateTimeProcessor')
            ->willReturn($dateTimeProcessor);

        $sectionFilter->setServiceLocator($sm);

        $mockFilterManager
            ->expects($this->once())
            ->method('get')
            ->with(
                $this->equalTo(
                    'Olcs/Filter/SubmissionSection/' . ucfirst($wordFilter->filter($input['sectionId']))
                )
            )
            ->willReturn($sectionFilter);

        $this->sut->setFilterManager($mockFilterManager);

        $result = $this->sut->createSubmissionSection($input['caseId'], $input['sectionId'], $input['sectionConfig']);

        $this->assertEquals($result, $expected['expected']);
    }
}
