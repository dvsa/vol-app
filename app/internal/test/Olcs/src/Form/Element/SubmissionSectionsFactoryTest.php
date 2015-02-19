<?php

namespace OlcsTest\Form\Element;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Form\Element\SubmissionSectionsFactory;
use Mockery as m;
use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager;
use \Olcs\TestHelpers\ControllerPluginManagerHelper;
use \Olcs\Data\Object\Cases;

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

        $mockCase = new \Olcs\Data\Object\Cases();
        $mockCase['id'] = 24;
        $mockCase['transportManager'] = ['id' => $transportManagerId];

        $mockCaseService = m::mock('Olcs\Service\Data\Cases');
        $mockCaseService->shouldReceive('fetchCaseData')->with($caseId)
            ->andReturn($mockCase);

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
        $service = $sut->createService($mockFormElementManager);

    }
}
