<?php

/**
 * Application Controller Trait Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\Controller\Traits;

use PHPUnit_Framework_TestCase;
use Zend\View\Model\ViewModel;
use OlcsTest\Bootstrap;
use OlcsTest\Controller\Traits\Stub\StubApplicationController;
use Common\Service\Entity\ApplicationEntityService;
use Common\Service\Entity\LicenceEntityService;

/**
 * Application Controller Trait Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationControllerTraitTest extends PHPUnit_Framework_TestCase
{
    protected $sm;
    protected $stub;

    protected function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();
        $this->stub = new StubApplicationController();
        $this->stub->setServiceLocator($this->sm);
    }

    /**
     * @group controller_traits
     */
    public function testRenderWithShowGrant()
    {
        $this->stub->setParams(array('application' => 1));

        $viewModel = new ViewModel();

        $mockApplicationHelper = $this->getMock('\stdClass', array('getStatus', 'getHeaderData'));
        $mockApplicationHelper->expects($this->once())
            ->method('getStatus')
            ->will($this->returnValue(ApplicationEntityService::APPLICATION_STATUS_UNDER_CONSIDERATION));

        $headerData = array(
            'id' => 1,
            'status' => array(
                'id' => 'Foo'
            ),
            'licence' => array(
                'id' => 123,
                'licNo' => 'asdjlkads',
                'organisation' => array(
                    'name' => 'sdjfhkjsdhf'
                )
            )
        );

        $mockApplicationHelper->expects($this->once())
            ->method('getHeaderData')
            ->will($this->returnValue($headerData));

        $this->sm->setAllowOverride(true);
        $this->sm->setService('Entity\Application', $mockApplicationHelper);

        $view = $this->stub->doRender($viewModel);

        $this->assertInstanceOf('\Olcs\View\Model\Application\Layout', $view);

        // @NOTE Don't really like this, but getChildrenByCaptureTo recursively gets an array of children with that name
        // and there are 2 with the name "content"
        // as we can't be sure which is which, we need to loop through them
        $contents = $view->getChildrenByCaptureTo('content');
        $applicationLayout = $viewContent = null;
        foreach ($contents as $content) {
            if ($content instanceof \Olcs\View\Model\Application\ApplicationLayout) {
                $applicationLayout = $content;
            } else {
                $viewContent = $content;
            }
        }

        $this->assertNotNull($viewContent);
        $this->assertNotNull($applicationLayout);

        $this->assertSame($viewModel, $viewContent);

        $variables = $view->getChildrenByCaptureTo('actions')[0]->getVariables();

        $this->assertTrue($variables['showGrant']);
        $this->assertFalse($variables['showUndoGrant']);
    }

    /**
     * @group controller_traits
     */
    public function testRenderWithNoButtons()
    {
        $this->stub->setParams(array('application' => 1));

        $viewModel = new ViewModel();

        $mockApplicationHelper = $this->getMock('\stdClass', array('getStatus', 'getHeaderData', 'getApplicationType'));
        $mockApplicationHelper->expects($this->once())
            ->method('getStatus')
            ->will($this->returnValue(ApplicationEntityService::APPLICATION_STATUS_GRANTED));

        $headerData = array(
            'id' => 1,
            'status' => array(
                'id' => 'Foo'
            ),
            'licence' => array(
                'id' => 123,
                'licNo' => 'asdjlkads',
                'organisation' => array(
                    'name' => 'sdjfhkjsdhf'
                )
            )
        );

        $mockApplicationHelper->expects($this->once())
            ->method('getHeaderData')
            ->will($this->returnValue($headerData));

        $mockApplicationHelper->expects($this->once())
            ->method('getApplicationType')
            ->will($this->returnValue(ApplicationEntityService::APPLICATION_TYPE_VARIATION));

        $this->sm->setAllowOverride(true);
        $this->sm->setService('Entity\Application', $mockApplicationHelper);

        $view = $this->stub->doRender($viewModel);

        $this->assertInstanceOf('\Olcs\View\Model\Application\Layout', $view);

        // @NOTE Don't really like this, but getChildrenByCaptureTo recursively gets an array of children with that name
        // and there are 2 with the name "content"
        // as we can't be sure which is which, we need to loop through them
        $contents = $view->getChildrenByCaptureTo('content');
        $applicationLayout = $viewContent = null;
        foreach ($contents as $content) {
            if ($content instanceof \Olcs\View\Model\Application\ApplicationLayout) {
                $applicationLayout = $content;
            } else {
                $viewContent = $content;
            }
        }

        $this->assertNotNull($viewContent);
        $this->assertNotNull($applicationLayout);

        $this->assertSame($viewModel, $viewContent);

        $variables = $view->getChildrenByCaptureTo('actions')[0]->getVariables();

        $this->assertFalse($variables['showGrant']);
        $this->assertFalse($variables['showUndoGrant']);
    }

    /**
     * @group controller_traits
     */
    public function testRenderWithShowUndoGrantFalse()
    {
        $this->stub->setParams(array('application' => 1));

        $viewModel = new ViewModel();

        $mockApplicationHelper = $this->getMock(
            '\stdClass',
            array('getStatus', 'getCategory', 'getHeaderData', 'getApplicationType')
        );
        $mockApplicationHelper->expects($this->once())
            ->method('getStatus')
            ->will($this->returnValue(ApplicationEntityService::APPLICATION_STATUS_GRANTED));

        $mockApplicationHelper->expects($this->once())
            ->method('getCategory')
            ->will($this->returnValue(LicenceEntityService::LICENCE_CATEGORY_PSV));

        $headerData = array(
            'id' => 1,
            'status' => array(
                'id' => 'Foo'
            ),
            'licence' => array(
                'id' => 123,
                'licNo' => 'asdjlkads',
                'organisation' => array(
                    'name' => 'sdjfhkjsdhf'
                )
            )
        );

        $mockApplicationHelper->expects($this->once())
            ->method('getHeaderData')
            ->will($this->returnValue($headerData));

        $mockApplicationHelper->expects($this->once())
            ->method('getApplicationType')
            ->will($this->returnValue(ApplicationEntityService::APPLICATION_TYPE_NEW));

        $this->sm->setAllowOverride(true);
        $this->sm->setService('Entity\Application', $mockApplicationHelper);

        $view = $this->stub->doRender($viewModel);

        $this->assertInstanceOf('\Olcs\View\Model\Application\Layout', $view);

        // @NOTE Don't really like this, but getChildrenByCaptureTo recursively gets an array of children with that name
        // and there are 2 with the name "content"
        // as we can't be sure which is which, we need to loop through them
        $contents = $view->getChildrenByCaptureTo('content');
        $applicationLayout = $viewContent = null;
        foreach ($contents as $content) {
            if ($content instanceof \Olcs\View\Model\Application\ApplicationLayout) {
                $applicationLayout = $content;
            } else {
                $viewContent = $content;
            }
        }

        $this->assertNotNull($viewContent);
        $this->assertNotNull($applicationLayout);

        $this->assertSame($viewModel, $viewContent);

        $variables = $view->getChildrenByCaptureTo('actions')[0]->getVariables();

        $this->assertFalse($variables['showGrant']);
        $this->assertFalse($variables['showUndoGrant']);
    }

    /**
     * @group controller_traits
     */
    public function testRenderWithShowUndoGrant()
    {
        $this->stub->setParams(array('application' => 1));

        $viewModel = new ViewModel();

        $mockApplicationHelper = $this->getMock(
            '\stdClass',
            array('getStatus', 'getCategory', 'getHeaderData', 'getApplicationType')
        );
        $mockApplicationHelper->expects($this->once())
            ->method('getStatus')
            ->will($this->returnValue(ApplicationEntityService::APPLICATION_STATUS_GRANTED));

        $mockApplicationHelper->expects($this->once())
            ->method('getCategory')
            ->will($this->returnValue(LicenceEntityService::LICENCE_CATEGORY_GOODS_VEHICLE));

        $headerData = array(
            'id' => 1,
            'status' => array(
                'id' => 'Foo'
            ),
            'licence' => array(
                'id' => 123,
                'licNo' => 'asdjlkads',
                'organisation' => array(
                    'name' => 'sdjfhkjsdhf'
                )
            )
        );

        $mockApplicationHelper->expects($this->once())
            ->method('getHeaderData')
            ->will($this->returnValue($headerData));

        $mockApplicationHelper->expects($this->once())
            ->method('getApplicationType')
            ->will($this->returnValue(ApplicationEntityService::APPLICATION_TYPE_NEW));

        $this->sm->setAllowOverride(true);
        $this->sm->setService('Entity\Application', $mockApplicationHelper);

        $view = $this->stub->doRender($viewModel);

        $this->assertInstanceOf('\Olcs\View\Model\Application\Layout', $view);

        // @NOTE Don't really like this, but getChildrenByCaptureTo recursively gets an array of children with that name
        // and there are 2 with the name "content"
        // as we can't be sure which is which, we need to loop through them
        $contents = $view->getChildrenByCaptureTo('content');
        $applicationLayout = $viewContent = null;
        foreach ($contents as $content) {
            if ($content instanceof \Olcs\View\Model\Application\ApplicationLayout) {
                $applicationLayout = $content;
            } else {
                $viewContent = $content;
            }
        }

        $this->assertNotNull($viewContent);
        $this->assertNotNull($applicationLayout);

        $this->assertSame($viewModel, $viewContent);

        $variables = $view->getChildrenByCaptureTo('actions')[0]->getVariables();

        $this->assertFalse($variables['showGrant']);
        $this->assertTrue($variables['showUndoGrant']);
    }
}
