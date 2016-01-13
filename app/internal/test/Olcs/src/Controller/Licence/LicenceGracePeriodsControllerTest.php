<?php

/**
 * LicenceGracePeriodsControllerTest.php
 */
namespace OlcsTest\Controller\Licence;

use Mockery as m;

use OlcsTest\Controller\Lva\AbstractLvaControllerTestCase;

/**
 * Class LicenceGracePeriodsControllerTest
 *
 * LicenceGracePeriodsController tests.
 *
 * @package OlcsTest\Controller\Licence
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
class LicenceGracePeriodsControllerTest extends AbstractLvaControllerTestCase
{
    protected $sut = null;

    public function setUp()
    {
        $this->markTestSkipped();

        parent::setUp();

        $this->mockController('\Olcs\Controller\Licence\LicenceGracePeriodsController');
    }

    public function testIndexGetAction()
    {
        $licenceId = 1;
        $licenceData = array(
            'id' => $licenceId
        );

        $gracePeriodData = array(
            'Count' => 1,
            'Results' => array(
                array(
                    'id' => 1,
                    'startDate' => '1970-01-01',
                    'endDate' => '1970-01-01',
                    'description' => ''
                )
            )
        );

        $this->sut->shouldReceive('params->fromRoute')->with('licence', null)->andReturn($licenceId);

        // Licence Entity Service
        $this->mockService('Entity\Licence', 'getExtendedOverview')
            ->with($licenceId)
            ->andReturn($licenceData);

        // Grace Period Entity Service.
        $this->mockService('Entity\GracePeriod', 'getGracePeriodsForLicence')
            ->with($licenceData['id'])
            ->andReturn($gracePeriodData);

        // Form and form helper service.
        $this->createMockForm('GracePeriods')
            ->shouldReceive('get->get')
            ->with('table')
            ->andReturnSelf()
            ->shouldReceive('setTable');

        $this->getMockFormHelper()
            ->shouldReceive('removeFieldList');

        // Table data and table.
        $this->mockService('Helper\LicenceGracePeriod', 'isActive')
            ->andReturn(true);

        $this->mockService('Table', 'prepareTable');

        $this->mockService('Script', 'loadFile')->with('table-actions');

        $this->mockRender();

        $this->sut->indexAction();
    }

    public function testIndexPostAction()
    {
        $this->setPost([]);

        $this->sut->shouldReceive('params->fromRoute')->with('licence', null)->andReturn(null);

        $this->sut->shouldReceive('getCrudAction')->andReturn('not-null');

        $this->sut->shouldReceive('handleCrudAction')->andReturn('redirect');

        $this->assertEquals('redirect', $this->sut->indexAction());
    }

    public function testAddPostActionSuccess()
    {
        $licenceId = 1;
        $postData = array(
            'details' => array(
                'startDate' => '1970-01-01',
                'endDate' => '1970-01-01'
            )
        );

        $this->setPost($postData);

        $this->sut->shouldReceive('params->fromRoute')->with('child_id', null)->andReturn(null);
        $this->sut->shouldReceive('params->fromRoute')->with('licence', null)->andReturn($licenceId);

        // Add/Edit GracePeriod form.
        $this->createMockForm('GracePeriod')
            ->shouldReceive('setData')
            ->with($postData)
            ->shouldReceive('isValid')
            ->andReturn(true)
            ->shouldReceive('getData')
            ->andReturn($postData);

        // GracePeriod business service.
        $this->mockService('BusinessServiceManager', 'get')
            ->with('Lva\GracePeriod')
            ->andReturn(
                m::mock()
                    ->shouldReceive('process')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('getType')
                            ->andReturn(\Common\BusinessService\ResponseInterface::TYPE_SUCCESS)
                            ->getMock()
                    )->getMock()
            );

        // FlashMessenger
        $this->sut->shouldReceive('flashMessenger->addSuccessMessage')->with('licence.grace-period.saved.success');

        $this->sut->shouldReceive('redirect->toRouteAjax')
            ->with(
                'licence/grace-periods',
                array('licence' => $licenceId)
            );

        $this->sut->addAction('add');
    }

    public function testAddPostActionFailure()
    {
        $licenceId = 1;
        $postData = array(
            'details' => array(
                'startDate' => '1970-01-01',
                'endDate' => '1970-01-01'
            )
        );

        $this->setPost($postData);

        $this->sut->shouldReceive('params->fromRoute')->with('child_id', null)->andReturn(null);
        $this->sut->shouldReceive('params->fromRoute')->with('licence', null)->andReturn($licenceId);

        // Add/Edit GracePeriod form.
        $this->createMockForm('GracePeriod')
            ->shouldReceive('setData')
            ->with($postData)
            ->shouldReceive('isValid')
            ->andReturn(true)
            ->shouldReceive('getData')
            ->andReturn($postData);

        // GracePeriod business service.
        $this->mockService('BusinessServiceManager', 'get')
            ->with('Lva\GracePeriod')
            ->andReturn(
                m::mock()
                    ->shouldReceive('process')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('getType')
                            ->andReturn(\Common\BusinessService\ResponseInterface::TYPE_FAILED)
                            ->getMock()
                    )->getMock()
            );

        // FlashMessenger
        $this->sut->shouldReceive('flashMessenger->addErrorMessage')->with('licence.grace-period.saved.failure');

        $this->sut->shouldReceive('redirect->toRouteAjax')
            ->with(
                'licence/grace-periods',
                array('licence' => $licenceId)
            );

        $this->sut->addAction('add');
    }

    public function testEditGetAction()
    {
        $childId = 1;

        $gracePeriodData = array(
            'id' => 1
        );

        // Grace period entity service.
        $this->sut->shouldReceive('params->fromRoute')->with('child_id', null)->andReturn($childId);
        $this->mockService('Entity\GracePeriod', 'getById')
            ->with($childId)
            ->andReturn($gracePeriodData);

        // Add/Edit GracePeriod form.
        $this->createMockForm('GracePeriod')
            ->shouldReceive('setData')
            ->with(
                array(
                    'details' => $gracePeriodData
                )
            );

        $this->getMockFormHelper()
            ->shouldReceive('remove');

        $this->mockRender();

        $this->sut->editAction('edit');
    }

    public function testDelete()
    {
        $ids = array(1, 2, 3);

        $this->mockService('Entity\GracePeriod', 'deleteListByIds')
            ->with(array('id' => $ids));

        $this->sut->shouldReceive('params->fromRoute')->with('child_id', null)->andReturn(implode($ids, ','));

        $this->sut->delete();
    }

    public function testIndexActionExplicitNullCrudAction()
    {
        $licenceId = 1;
        $licenceData = array(
            'id' => $licenceId
        );

        $gracePeriodData = array(
            'Count' => 1,
            'Results' => array(
                array(
                    'id' => 1,
                    'startDate' => '1970-01-01',
                    'endDate' => '1970-01-01',
                    'description' => ''
                )
            )
        );

        $this->setPost([]);

        $this->sut->shouldReceive('params->fromRoute')->with('licence', null)->andReturn($licenceId);

        // Licence Entity Service
        $this->mockService('Entity\Licence', 'getExtendedOverview')
            ->with($licenceId)
            ->andReturn($licenceData);

        $this->sut->shouldReceive('getCrudAction')->andReturn(null);

        // Grace Period Entity Service.
        $this->mockService('Entity\GracePeriod', 'getGracePeriodsForLicence')
            ->with($licenceData['id'])
            ->andReturn($gracePeriodData);

        // Form and form helper service.
        $this->createMockForm('GracePeriods')
            ->shouldReceive('get->get')
            ->with('table')
            ->andReturnSelf()
            ->shouldReceive('setTable');

        $this->getMockFormHelper()
            ->shouldReceive('removeFieldList');

        // Table data and table.
        $this->mockService('Helper\LicenceGracePeriod', 'isActive')
            ->andReturn(true);

        $this->mockService('Table', 'prepareTable');

        $this->mockService('Script', 'loadFile')->with('table-actions');

        $this->mockRender();

        $this->sut->indexAction();
    }

    public function testAddExplicitInvalidForm()
    {
        $licenceId = 1;
        $postData = array(
            'details' => array(
                'startDate' => '1970-01-01',
                'endDate' => '1970-01-01'
            )
        );

        $this->setPost($postData);

        $this->sut->shouldReceive('params->fromRoute')->with('child_id', null)->andReturn(null);
        $this->sut->shouldReceive('params->fromRoute')->with('licence', null)->andReturn($licenceId);

        // Add/Edit GracePeriod form.
        $this->createMockForm('GracePeriod')
            ->shouldReceive('setData')
            ->with($postData)
            ->shouldReceive('isValid')
            ->andReturn(false);

        $this->mockRender();

        $this->sut->addAction('add');
    }
}
