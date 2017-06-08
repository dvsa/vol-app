<?php
/**
 * Application Controller Trait Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace OlcsTest\Controller\Lva\Traits;

use Dvsa\Olcs\Transfer\Query\Application\Application as ApplicationQry;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\TestHelpers\Controller\Traits\ControllerTestTrait;
use OlcsTest\Bootstrap;

/**
 * Application Controller Trait Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

class ApplicationControllerTraitTest extends MockeryTestCase
{
    use ControllerTestTrait;

    protected function getServiceManager()
    {
        return Bootstrap::getServiceManager();
    }

    protected function setUp()
    {
        $this->sm = $this->getServiceManager();

        $this->sut = m::mock('OlcsTest\Controller\Lva\Traits\Stubs\ApplicationControllerTraitStub')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $this->sut->setServiceLocator($this->sm);
    }

    /**
     * @group lva_controller_traits
     * @dataProvider stepProgressProvider
     */
    public function testGetSectionStepProgress($sectionName, $stubbedOverviewData, $expected)
    {
        $stubbedSectionStatus = array(
            'type_of_licence' => array(
                'enabled' => true
            ),
            'foo' => array(
                'enabled' => false
            )
        );

        $this->sut
            ->shouldReceive('getIdentifier')
                ->andReturn(123)
            ->shouldReceive('setEnabledAndCompleteFlagOnSections')
                ->with($stubbedOverviewData['sections'], $stubbedOverviewData['applicationCompletion'])
                ->andReturn($stubbedSectionStatus);

        $this->expectQuery(ApplicationQry::class, ['id' => 123], $stubbedOverviewData, true, 2);

        $progress = $this->sut->getSectionStepProgress($sectionName);
        $this->assertEquals($expected, $progress);

        $progress = $this->sut->getSectionStepProgress('something_else');
        $this->assertEquals([], $progress);
    }

    public function stepProgressProvider()
    {
        return [
            'main section' => [
                'type_of_licence',
                [
                    'isVariation' => false,
                    'applicationCompletion' => ['foo' => 'bar'],
                    'sections' => [],
                ],
                ['stepX' => 1, 'stepY' => 2],
            ],
            'sub section' => [
                'something_else',
                [
                    'isVariation' => false,
                    'applicationCompletion' => ['foo' => 'bar'],
                    'sections' => [],
                ],
                [],
            ],
            'variation' => [
                'type_of_licence',
                [
                    'isVariation' => true,
                    'applicationCompletion' => ['foo' => 'bar'],
                    'sections' => [],
                ],
                [],
            ],
        ];
    }

    public function testRenderWithNormalRequest()
    {
        $this->sut->shouldReceive('attachCurrentMessages')
            ->shouldReceive('getRequest')
            ->andReturn(
                m::mock()
                ->shouldReceive('isXmlHttpRequest')
                ->andReturn(false)
                ->getMock()
            );

        $this->sut->shouldReceive('getSectionStepProgress')
            ->with('my-page')
            ->andReturn(['stepX' => 2, 'stepY' => 12])
            ->shouldReceive('getApplicationData')
            ->andReturn(
                [
                    'status' => ['id' => 'apsts_not_submitted'], 'licence' => ['licNo' => 'OB1']
                ]
            )
            ->once()
            ->shouldReceive('getApplicationId')
            ->andReturn(1)
            ->twice();

        $view = $this->sut->callRender('my-page');

        $this->assertEquals('layout/layout', $view->getTemplate());
        $this->assertTrue($view->terminate());

        $this->assertEquals(1, $view->count());

        $children = $view->getChildren();

        $this->assertEquals(
            [
                'title' => 'lva.section.title.my-page',
                'form' => null,
                'stepX' => 2,
                'stepY' => 12,
                'status' => 'apsts_not_submitted',
                'lva' => 'application',
                'reference' => 'OB1 / 1'
            ],
            (array)$children[0]->getVariables()
        );
    }

    public function testRenderWhenSectionNameAndViewTemplateDiffer()
    {
        $this->sut->shouldReceive('attachCurrentMessages')
            ->shouldReceive('getRequest')
            ->andReturn(
                m::mock()
                ->shouldReceive('isXmlHttpRequest')
                ->andReturn(false)
                ->getMock()
            );

        $this->sut->shouldReceive('getSectionStepProgress')
            ->with('people')
            ->andReturn(['stepX' => 2, 'stepY' => 12])
            ->shouldReceive('getApplicationData')
            ->andReturn(
                [
                    'status' => ['id' => 'apsts_not_submitted'], 'licence' => ['licNo' => 'OB1']
                ]
            )
            ->once()
            ->shouldReceive('getApplicationId')
            ->andReturn(1)
            ->twice();

        $view = $this->sut->callRender('person');

        $children = $view->getChildren();

        $this->assertEquals(
            [
                'title' => 'lva.section.title.person',
                'form'  => null,
                'stepX' => 2,
                'stepY' => 12,
                'status' => 'apsts_not_submitted',
                'lva' => 'application',
                'reference' => 'OB1 / 1'
            ],
            (array)$children[0]->getVariables()
        );
    }

    public function testPostSaveUndertakings()
    {
        $applicationId = 123;
        $section = 'undertakings';

        $this->sut->shouldReceive('getApplicationId')->andReturn($applicationId);

        $this->sm->setService(
            'Entity\ApplicationCompletion',
            m::mock()
                ->shouldReceive('updateCompletionStatuses')
                ->once()
                ->with($applicationId, $section)
                ->getMock()
        );
        $this->sm->setService(
            'Entity\Application',
            m::mock()
                ->shouldReceive('forceUpdate')
                ->never()
                ->getMock()
        );

        $this->sut->postSave('undertakings');
    }

    public function testPostSaveOtherSection()
    {
        $applicationId = 123;
        $section = 'some_section';

        $this->sut->shouldReceive('getApplicationId')->andReturn($applicationId);

        $this->sm->setService(
            'Entity\ApplicationCompletion',
            m::mock()
                ->shouldReceive('updateCompletionStatuses')
                ->once()
                ->with($applicationId, $section)
                ->getMock()
        );
        $this->sm->setService(
            'Entity\Application',
            m::mock()
                ->shouldReceive('forceUpdate')
                ->once()
                ->with($applicationId, ['declarationConfirmation' => 'N'])
                ->getMock()
        );

        $this->sut->postSave('some_section');
    }
}
