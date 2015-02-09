<?php

namespace OlcsTest\Controller\ConditionUndertaking;

use Mockery as m;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use OlcsTest\Bootstrap;
use Olcs\TestHelpers\ControllerPluginManagerHelper;
use Olcs\Controller\Cases\ConditionUndertaking\ConditionUndertakingController;
use Common\Service\Entity\ConditionUndertakingEntityService;

/**
 * ConditionUndertaking controller tests
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ConditionUndertakingControllerTest extends AbstractHttpControllerTestCase
{
    /**
     * @var ControllerPluginManagerHelper
     */
    protected $pluginManagerHelper;

    /**
     * @var ConditionUndertakingController
     */
    protected $sut;

    protected $sm;
    protected $pm;
    protected $mockParams;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();

        $this->sut = new ConditionUndertakingController();
        $this->sut->setServiceLocator($this->sm);

        $this->pluginManagerHelper = new ControllerPluginManagerHelper();
        $this->pm = $this->pluginManagerHelper->getMockPluginManager(
            [
                'params' => 'Params',
                'FlashMessenger' => 'FlashMessenger',
                'redirect' => 'Redirect'
            ]
        );
        $this->sut->setPluginManager($this->pm);

        parent::setUp();
    }

    public function testAlterFormBeforeValidation()
    {
        // Params
        $mockForm = m::mock('Zend\Form\Form');
        $parentId = 123;

        $caseId = 88;
        $licenceId = $parentId;
        $mockCase = [
            'id' => $caseId,
            'licence' => [
                'id' => $licenceId
            ]
        ];

        // Mocks
        $mockAdapter = m::mock();
        $this->sm->setService('LicenceConditionsUndertakingsAdapter', $mockAdapter);

        // Expectations
        $mockForm->shouldReceive('getLabel')->andReturn('some_label');
        $mockForm->shouldReceive('setLabel')->with('some_label Conditions / Undertakings');

        $mockAdapter->shouldReceive('alterForm')
            ->with($mockForm, $parentId);

        $this->mockGetCase($mockCase, $caseId);

        $this->assertSame($mockForm, $this->sut->alterFormBeforeValidation($mockForm));
    }

    public function testAlterFormBeforeValidationWithApplication()
    {
        // Params
        $mockForm = m::mock('Zend\Form\Form');
        $parentId = 123;

        $caseId = 88;
        $applicationId = $parentId;
        $mockCase = [
            'id' => $caseId,
            'application' => [
                'id' => $applicationId,
                'isVariation' => false
            ]
        ];

        // Mocks
        $mockAdapter = m::mock();
        $this->sm->setService('ApplicationConditionsUndertakingsAdapter', $mockAdapter);

        // Expectations
        $mockForm->shouldReceive('getLabel')->andReturn('some_label');
        $mockForm->shouldReceive('setLabel')->with('some_label Conditions / Undertakings');

        $mockAdapter->shouldReceive('alterForm')
            ->with($mockForm, $parentId);

        $this->mockGetCase($mockCase, $caseId);

        $this->assertSame($mockForm, $this->sut->alterFormBeforeValidation($mockForm));
    }

    public function testAlterFormBeforeValidationWithVariation()
    {
        // Params
        $mockForm = m::mock('Zend\Form\Form');
        $parentId = 123;

        $caseId = 88;
        $applicationId = $parentId;
        $mockCase = [
            'id' => $caseId,
            'application' => [
                'id' => $applicationId,
                'isVariation' => true
            ]
        ];

        // Mocks
        $mockAdapter = m::mock();
        $this->sm->setService('VariationConditionsUndertakingsAdapter', $mockAdapter);

        // Expectations
        $mockForm->shouldReceive('getLabel')->andReturn('some_label');
        $mockForm->shouldReceive('setLabel')->with('some_label Conditions / Undertakings');

        $mockAdapter->shouldReceive('alterForm')
            ->with($mockForm, $parentId);

        $this->mockGetCase($mockCase, $caseId);

        $this->assertSame($mockForm, $this->sut->alterFormBeforeValidation($mockForm));
    }

    public function testAlterFormBeforeValidationWithException()
    {
        $this->setExpectedException('\Exception');

        // Params
        $mockForm = m::mock('Zend\Form\Form');

        $caseId = 88;
        $mockCase = [
            'id' => $caseId
        ];

        // Expectations
        $mockForm->shouldReceive('getLabel')->andReturn('some_label');
        $mockForm->shouldReceive('setLabel')->with('some_label Conditions / Undertakings');

        $this->mockGetCase($mockCase, $caseId);

        $this->assertSame($mockForm, $this->sut->alterFormBeforeValidation($mockForm));
    }

    public function testProcessLoad()
    {
        // Params
        $caseId = 99;
        $mockCase = [
            'id' => $caseId,
            'licence' => ['id' => 321]
        ];
        $expectedData = [
            'foo',
            'case' => 99,
            'fields' => [
                'case' => 99
            ],
            'base' => [
                'case' => 99
            ]
        ];

        // Mocks
        $mockAdapter = m::mock();
        $this->sm->setService('LicenceConditionsUndertakingsAdapter', $mockAdapter);

        $mockParams = $this->getMockParams();
        $mockParams->shouldReceive('fromQuery')
            ->with('case', null)
            ->andReturn($caseId);

        // Expectations
        $this->mockGetCase($mockCase, $caseId);
        $mockAdapter->shouldReceive('processDataForForm')
            ->with($expectedData)
            ->andReturn(['foo' => 'bar']);

        $this->assertEquals(['foo' => 'bar'], $this->sut->processLoad(['foo']));
    }

    public function testProcessSaveUpdate()
    {
        $caseId = 321;
        $mockCase = [
            'id' => $caseId,
            'licence' => [
                'id' => 123
            ]
        ];
        $expectedData = [
            'foo' => 'bar',
            'fields' => ['addedVia' => ConditionUndertakingEntityService::ADDED_VIA_CASE]
        ];

        $mockAdapter = m::mock();
        $this->sm->setService('LicenceConditionsUndertakingsAdapter', $mockAdapter);
        $mockAdapter->shouldReceive('processDataForSave')
            ->with($expectedData, 123)
            ->andReturn($expectedData);

        $mockParams = $this->getMockParams();
        $mockParams->shouldReceive('fromRoute')
            ->with('case')
            ->andReturn($caseId);

        $this->mockGetCase($mockCase, $caseId);

        $mockDataToSave = ['id' => 99];

        $mockFlashMessenger = $this->pm->get('FlashMessenger', '');
        $mockFlashMessenger->shouldReceive('addSuccessMessage')->with(m::type('string'));

        $mockRedirectPlugin = $this->pm->get('redirect', '');

        $mockRedirectPlugin->shouldReceive('toRouteAjax')->with(
            '',
            ['action' => 'index', 'id'=>''],
            ['code' => 303],
            true
        )->andReturn('mockResponse');
        $this->sut->setPluginManager($this->pm);

        $mockRestHelper = m::mock('RestHelper');
        $mockRestHelper->shouldReceive('makeRestCall')->with(
            'ConditionUndertaking',
            'PUT',
            $mockDataToSave,
            ""
        )->andReturnNull();

        $mockDataHelper = m::mock('DataHelper');
        $mockDataHelper->shouldReceive('processDataMap')->with(
            m::Type('array'),
            m::Type('array'),
            m::Type('string')
        )->andReturn($mockDataToSave);

        $this->sm->setService('Helper\Rest', $mockRestHelper);
        $this->sm->setService('Helper\Data', $mockDataHelper);

        $this->assertEquals('mockResponse', $this->sut->processSave(['foo' => 'bar']));
    }

    /**
     * Helper method to mock getCase
     *
     * @param array $return
     * @param int $caseId
     */
    protected function mockGetCase($return, $caseId)
    {
        $mockParams = $this->getMockParams();

        $mockParams->shouldReceive('getParam')
            ->with('case')
            ->andReturn($caseId);

        $mockParams->shouldReceive('fromRoute')
            ->with('case')
            ->andReturn($caseId);

        $mockRestHelper = m::mock('RestHelper');
        $mockRestHelper->shouldReceive('makeRestCall')
            ->with('Cases', 'GET', array('id' => $caseId), m::type('array'))
            ->andReturn($return);

        $mockCaseService = m::mock('Olcs\Service\Data\Cases');
        $mockCaseService->shouldReceive('fetchCaseData')
            ->with($caseId)
            ->andReturn($return);

        $this->sm->setService('DataServiceManager', $this->sm);
        $this->sm->setService('Olcs\Service\Data\Cases', $mockCaseService);
        $this->sm->setService('Helper\Rest', $mockRestHelper);
    }

    protected function getMockParams()
    {
        if ($this->mockParams === null) {
            $this->mockParams = $this->pm->get('params', '');
        }

        return $this->mockParams;
    }
}
