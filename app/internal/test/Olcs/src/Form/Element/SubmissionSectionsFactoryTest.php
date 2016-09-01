<?php

namespace OlcsTest\Form\Element;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Form\Element\SubmissionSectionsFactory;
use Mockery as m;
use \Olcs\TestHelpers\ControllerPluginManagerHelper;

/**
 * Class SubmissionSectionsFactoryTest
 * @package OlcsTest\Form\Element
 */
class SubmissionSectionsFactoryTest extends MockeryTestCase
{
    public function testCreateService()
    {
        $caseId = 84;
        $transportManagerId = 3;
        $pluginManagerHelper = new ControllerPluginManagerHelper();

        $mockPluginManager = $pluginManagerHelper->getMockPluginManager(['params' => 'Params']);
        $mockParamsPlugin = $mockPluginManager->get('params', '');
        $mockParamsPlugin->shouldReceive('fromRoute')->with('case')
            ->andReturn($caseId);

        $caseData = [
            'id' => 24,
            'transportManager' => [
                'id' => $transportManagerId
            ]
        ];

        $mockCaseService = m::mock('Olcs\Service\Data\Cases');
        $mockCaseService->shouldReceive('fetchData')->with($caseId)
            ->andReturn($caseData);

        $mockFormElementManager = m::mock('\Zend\Form\FormElementManager');

        $mockServiceLocator = m::mock('\Zend\Service\ServiceManager');
        $mockServiceLocator->shouldReceive('get')->with('ControllerPluginManager')
            ->andReturnSelf();
        $mockServiceLocator->shouldReceive('get')->with('DataServiceManager')
            ->andReturnSelf();
        $mockServiceLocator->shouldReceive('get')->with('Olcs\Service\Data\Cases')
            ->andReturn($mockCaseService);
        $mockServiceLocator->shouldReceive('get')->with('params')
            ->andReturn($mockParamsPlugin);

        $mockHiddenElement = m::mock('Zend\Form\Element\Hidden');
        $mockHiddenElement->shouldReceive('setValue')->with($transportManagerId);
        $mockDynamicSelectElement = m::mock('\Common\Form\Element\DynamicSelect');
        $mockDynamicSelectElement->shouldReceive('setOptions')->with(m::type('array'));
        $mockSubmitElement = m::mock('Zend\Form\Element\Submit');
        $mockSubmitElement->shouldReceive('setOptions')->with(m::type('array'));
        $mockDynamicMultiCheckboxElement = m::mock('\Common\Form\Element\DynamicMultiCheckbox');
        $mockDynamicMultiCheckboxElement->shouldReceive('setOptions')->with(m::type('array'));

        $mockFormElementManager->shouldReceive('getServiceLocator')->andReturn($mockServiceLocator);
        $mockFormElementManager->shouldReceive('get')->with('Hidden')->andReturn($mockHiddenElement);
        $mockFormElementManager->shouldReceive('get')->with('DynamicSelect')->andReturn($mockDynamicSelectElement);
        $mockFormElementManager->shouldReceive('get')->with('Submit')->andReturn($mockSubmitElement);
        $mockFormElementManager->shouldReceive('get')
            ->with('DynamicMultiCheckbox')
            ->andReturn($mockDynamicMultiCheckboxElement);

        $sut = new SubmissionSectionsFactory();
        $sut->createService($mockFormElementManager);
    }
}
