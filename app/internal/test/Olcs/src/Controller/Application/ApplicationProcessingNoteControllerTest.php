<?php
/**
 * Application Processing Note controller tests
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace OlcsTest\Controller\Application;

use OlcsTest\Controller\ProcessingNoteControllerTestAbstract;

/**
 * Application Processing Note controller tests
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class ApplicationProcessingNoteControllerTest extends ProcessingNoteControllerTestAbstract
{
    protected $testClass = '\Olcs\Controller\Application\Processing\ApplicationProcessingNoteController';
    protected $mainIdRouteParam = 'application';

    public function testIndexAction()
    {
        $mockServiceLocator = $this->getMock('\StdClass', ['get']);
        $mockServiceLocator->expects($this->once())
            ->method('get')
            ->with('Entity\Application')
            ->will($this->returnValue($this->getMockApplicationEntityService()));
        $this->controller->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($mockServiceLocator));
        return parent::testIndexAction();
    }
  /**
     * Gets a mock version of application entity service
     * @group task
     */
    private function getMockApplicationEntityService()
    {
        $mock = $this->getMock(
            '\StdClass',
            ['getLicenceIdForApplication']
        );
        $mock->expects($this->any())
            ->method('getLicenceIdForApplication')
            ->will($this->returnValue(1234));
        return $mock;
    }


    /**
     * Tests for a crud edit/delete redirect from index action
     *
     * @dataProvider indexActionModifyRedirectProvider
     *
     * @param string $action
     */
    public function testIndexActionModifyRedirect($action) {
        $this->markTestIncomplete();
    }

    public function testIndexActionAddRedirect() {
        $this->markTestIncomplete();
    }

    public function testAddAction() {
        $this->markTestIncomplete();
    }

    // testAddAction
    // testEditAction
    // testProcessAddNotes
    // testProcessAddNotesFail
    // testProcessAddNotesMissingLinkException
    // testProcessEditNotes
    // testProcessEditNotesFail
}
