<?php
/**
 * Application Controller Trait Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace OlcsTest\Controller\Lva\Traits;

use OlcsTest\Bootstrap;
use Common\Service\Entity\ApplicationEntityService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Application Controller Trait Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

class ApplicationControllerTraitTest extends MockeryTestCase
{
    protected $sut;

    protected $sm;

    protected function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();

        $this->sut = m::mock('OlcsTest\Controller\Lva\Traits\Stubs\ApplicationControllerTraitStub')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $this->sut->setServiceLocator($this->sm);
    }

    /**
     * @group lva_controller_traits
     */
    public function testGetSectionStepProgress()
    {
        $stubbedOverviewData = array(
            'applicationCompletions' => array(
                array(
                    'foo' => 'bar'
                )
            )
        );
        $stubbedAccessibleSections = array(
            'bar' => 'cake'
        );
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
            ->shouldReceive('getAccessibleSections')
                ->andReturn($stubbedAccessibleSections)
            ->shouldReceive('setEnabledAndCompleteFlagOnSections')
                ->with($stubbedAccessibleSections, ['foo' => 'bar'])
                ->andReturn($stubbedSectionStatus);

        $this->sm->setService(
            'Entity\Application',
            m::mock()
                ->shouldReceive('getOverview')
                ->with(123)
                ->andReturn($stubbedOverviewData)
                ->getMock()
        );

        $progress = $this->sut->getSectionStepProgress('type_of_licence');
        $this->assertEquals(['stepX' => 1, 'stepY' => 2], $progress);

        $progress = $this->sut->getSectionStepProgress('something_elese');
        $this->assertEquals([], $progress);
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
            ->andReturn(['stepX' => 2, 'stepY' => 12]);

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
            ->andReturn(['stepX' => 2, 'stepY' => 12]);

        $view = $this->sut->callRender('person');

        $children = $view->getChildren();

        $this->assertEquals(
            [
                'title' => 'lva.section.title.person',
                'form'  => null,
                'stepX' => 2,
                'stepY' => 12,
            ],
            (array)$children[0]->getVariables()
        );
    }
}
