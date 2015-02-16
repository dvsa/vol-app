<?php
namespace OlcsTest\Controller\Submission;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use OlcsTest\Bootstrap;
use Mockery as m;
use Zend\Http\Request;
use Zend\Http\Response;
use Olcs\TestHelpers\ControllerRouteMatchHelper;

/**
 * SubmissionSectionComment controller tests
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
class SubmissionSectionCommentControllerTest extends AbstractHttpControllerTestCase
{

    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__ . '/../../../../../../' . 'config/application.config.php'
        );

        $this->routeMatchHelper = new ControllerRouteMatchHelper();

        parent::setUp();
    }

    /**
     * Test processLoad of submissions
     *
     * @param $dataToLoad
     * @param $loadedData
     *
     * @dataProvider getSubmissionSectionsToLoadProvider
     */
    public function testProcessLoad($dataToLoad, $loadedData)
    {
        $sut = new \Olcs\Controller\Cases\Submission\SubmissionSectionCommentController();
        $event = $this->routeMatchHelper->getMockRouteMatch(array('controller' => 'submission_section_comment'));
        $sut->setEvent($event);

        $sut->getEvent()->getRouteMatch()->setParam('case', $dataToLoad['case']);
        $sut->getEvent()->getRouteMatch()->setParam('submission', $dataToLoad['submission']);
        $sut->getEvent()->getRouteMatch()->setParam('submissionSection', $dataToLoad['submissionSection']);

        $result = $sut->processLoad($dataToLoad);

        $this->assertEquals($result, $loadedData);

    }

    /**
     * Isolated test for the redirect action method.
     *
     * @dataProvider getSubmissionSectionsToLoadProvider
     */
    public function testRedirectToIndex()
    {
        $sut = new \Olcs\Controller\Cases\Submission\SubmissionSectionCommentController();

        $mockResponse = m::mock('\Zend\Http\Response');

        $submissionId = 99;

        $mockParamsPlugin = m::mock('\Zend\Controller\Plugin\Params');
        $mockParamsPlugin->shouldReceive('fromRoute')
            ->with('submission')
            ->andReturn($submissionId);

        $mockRedirectPlugin = m::mock('\Zend\Controller\Plugin\Redirect');
        $mockRedirectPlugin->shouldReceive('toRoute')->with(
            'submission',
            ['action' => 'details', 'id' => $submissionId],
            [],
            true
        )->andReturn($mockResponse);

        $mockControllerPluginManager = m::mock('\Zend\Mvc\Controller\PluginManager');
        $mockControllerPluginManager->shouldReceive('setController')->withAnyArgs();
        $mockControllerPluginManager->shouldReceive('get')->with('params', '')->andReturn($mockParamsPlugin);
        $mockControllerPluginManager->shouldReceive('get')->with('redirect', '')->andReturn($mockRedirectPlugin);

        $sut->setPluginManager($mockControllerPluginManager);

        $this->assertEquals($mockResponse, $sut->redirectToIndex());
    }


    public function testIndexAction()
    {
        $sut = new \Olcs\Controller\Cases\Submission\SubmissionSectionCommentController();

        $mockResponse = m::mock('\Zend\Http\Response');

        $submissionId = 99;

        $mockParamsPlugin = m::mock('\Zend\Controller\Plugin\Params');
        $mockParamsPlugin->shouldReceive('fromRoute')
            ->with('submission')
            ->andReturn($submissionId);

        $mockRedirectPlugin = m::mock('\Zend\Controller\Plugin\Redirect');
        $mockRedirectPlugin->shouldReceive('toRoute')->with(
            'submission',
            ['action' => 'details', 'id' => $submissionId],
            [],
            true
        )->andReturn($mockResponse);

        $mockControllerPluginManager = m::mock('\Zend\Mvc\Controller\PluginManager');
        $mockControllerPluginManager->shouldReceive('setController')->withAnyArgs();
        $mockControllerPluginManager->shouldReceive('get')->with('params', '')->andReturn($mockParamsPlugin);
        $mockControllerPluginManager->shouldReceive('get')->with('redirect', '')->andReturn($mockRedirectPlugin);

        $sut->setPluginManager($mockControllerPluginManager);

        $this->assertEquals($mockResponse, $sut->indexAction());
    }

    public function testAlterForm()
    {
        $sut = new \Olcs\Controller\Cases\Submission\SubmissionSectionCommentController();
        $event = $this->routeMatchHelper->getMockRouteMatch(array('controller' => 'submission_section_comment'));
        $sut->setEvent($event);

        $sectionId = 'section_1';

        $sut->getEvent()->getRouteMatch()->setParam('case', 24);
        $sut->getEvent()->getRouteMatch()->setParam('submission', 99);
        $sut->getEvent()->getRouteMatch()->setParam('submissionSection', $sectionId);

        $mockParamsPlugin = m::mock('\Zend\Controller\Plugin\Params');
        $mockParamsPlugin->shouldReceive('fromRoute')
            ->with('submissionSection')
            ->andReturn($sectionId);

        $mockControllerPluginManager = m::mock('\Zend\Mvc\Controller\PluginManager');
        $mockControllerPluginManager->shouldReceive('setController')->withAnyArgs();
        $mockControllerPluginManager->shouldReceive('get')->with('params', '')->andReturn($mockParamsPlugin);

        $form = m::mock('Zend\Form\Form');
        $mockSectionRefData = [
            'section_1' => 'Section 1 Title'
        ];

        $mockRefDataService = m::mock('Common\Service\Data\RefData');

        $mockRefDataService->shouldReceive('fetchListOptions')
            ->with('submission_section')
            ->andReturn($mockSectionRefData);

        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');

        $mockServiceManager->shouldReceive('get')->with('Common\Service\Data\RefData')
            ->andReturn($mockRefDataService);

        $sut->setServiceLocator($mockServiceManager);

        $form->shouldReceive('setOptions')
            ->withAnyArgs();

        $form = $sut->alterForm($form);

        $this->assertEquals('object', gettype($form));
    }

    public function getSubmissionSectionsToLoadProvider()
    {
        return array(
            array(
                array(
                    'case' => 24,
                    'submission' => 1,
                    'submissionSection' => 'foo'
                ),
                array(
                    'base' => array(
                        'case' => 24,
                    ),
                    'fields' => array(
                        'case' => 24,
                        'submission' => 1,
                        'submissionSection' => 'foo',
                    ),
                    'case' => 24,
                    'submission' => 1,
                    'submissionSection' => 'foo',
                ),
            )
        );
    }
}
