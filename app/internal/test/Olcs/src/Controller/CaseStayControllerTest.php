<?php

namespace OlcsTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class CaseStayControllerTest extends AbstractHttpControllerTestCase {

    protected $traceError = true;

    public function setUp() {
        $this->setApplicationConfig(
                include __DIR__ . '/../../../../config/application.config.php'
        );

        parent::setUp();
    }
    
    /**
     * @dataProvider addActionProvider
     */
    public function testAddAction($caseId) {
        $response = $this->dispatch('/case/24/action/manage/stays/add', 'GET', array('action' => 'add', 'case' => $caseId));
        $this->assertResponseStatusCode(200);
        $this->assertControllerName('casestaycontroller');
        $this->assertControllerClass('CaseStayController');
        $this->assertMatchedRouteName('case_stay_action');
        $this->assertActionName('add');
        $case = ['key' => 'case'];
        $viewTemplate = 'case/add-stay';

        $view = $this->getMock(
                'Zend\View\Model\ViewModel', ['setVariables', 'setTemplate']
        );

        $sut = $this->getMock('\Olcs\Controller\CaseStayController', ['fromRoute', 'getCase', 'generateFormWithData', 'setTemplate', 'notFoundAction']);

        $sut->expects($this->at(0))
                ->method('fromRoute')
                ->with($this->equalTo('case'))
                ->will($this->returnValue($caseId));

        $sut->expects($this->at(1))
                ->method('getCase')
                ->with($this->equalTo($caseId))
                ->will($this->returnValue($this->caseProvider($caseId)));
        
        if(empty($this->caseProvider($caseId))){
            $sut->expects($this->at(2))
                ->method('notFoundAction');
        }
        else{
            $sut->expects($this->at(2))
                ->method('generateFormWithData');

            //$view->expects($this->at(0))
            //->method('setTemplate')
            //->with($this->equalTo($viewTemplate));
            
            $sut->expects($this->never())
                ->method('notFoundAction');
        }


        $sut->addAction();
    }
    
    /**
     * @dataProvider editActionProvider
     */
    public function testEditAction($caseId, $stayId) {
        $response = $this->dispatch('/case/24/action/manage/stays/edit/10', 'GET', array('action' => 'edit', 'case' => $caseId, 'stay' => $stayId));
        $this->assertResponseStatusCode(200);
        $this->assertControllerName('casestaycontroller');
        $this->assertControllerClass('CaseStayController');
        $this->assertMatchedRouteName('case_stay_action');
        $this->assertActionName('edit');

        $restEnd = 'Stay';
        $restComm = 'GET';
        $restParam = array('id' => $stayId);
        $viewTemplate = 'case/add-stay';

        $sut = $this->getMock(
                '\Olcs\Controller\CaseStayController', ['fromRoute', 'makeRestCall', 'getCase', 'generateFormWithData', 'notFoundAction', 'setTemplate']
        );

        $view = $this->getMock(
                'Zend\View\Model\ViewModel', ['setVariables', 'setTemplate']
        );

        $sut->expects($this->at(0))
                ->method('fromRoute')
                ->with($this->equalTo('stay'))
                ->will($this->returnValue($stayId));

        $sut->expects($this->at(1))
                ->method('makeRestCall')
                ->with($this->equalTo($restEnd), $this->equalTo($restComm), $this->equalTo($restParam))
                ->will($this->returnValue($this->stayProvider($stayId)));
        
        if(empty($this->stayProvider($stayId))){
            $sut->expects($this->at(2))
                ->method('notFoundAction');
        }
        else{
            $sut->expects($this->at(2))
                    ->method('fromRoute')
                    ->with($this->equalTo('case'))
                    ->will($this->returnValue($caseId));

            $sut->expects($this->at(3))
                    ->method('getCase')
                    ->with($this->equalTo($caseId))
                    ->will($this->returnValue($this->caseProvider($caseId)));
            
            if(empty($this->caseProvider($caseId))){
                $sut->expects($this->at(4))
                ->method('notFoundAction');
            }
            else{
                $sut->expects($this->at(4))
                    ->method('generateFormWithData');
                
                $sut->expects($this->never())
                ->method('notFoundAction');
                
                //$view->expects($this->once())
                    //->method('setTemplate')
                    //->with($this->equalTo($viewTemplate));
            }
        }
        

        $sut->editAction();
    }

    public function testProcessAddStay() {
        $result['id'] = 1;
        $data['fields'] = array();
        $entity = 'Stay';
        $redirect = 'case_stay_action';
        $redirectOptions = array('action' => 'edit', 'case' => 1, 'stay' => $result['id']);

        $sut = $this->getMock(
                '\Olcs\Controller\CaseStayController', ['redirect', 'processAdd', 'toRoute']
        );

        $sut->expects($this->at(0))
                ->method('processAdd')
                ->will($this->returnValue($result));

        //$sut->expects($this->at(1))
        //->method('toRoute')
        //->with($this->equalTo($redirect),$this->equalTo($redirectOptions));

        $sut->processAddStay($data);
    }

    public function testProcessEditStay() {
        $result['id'] = 1;
        $data['fields'] = array();
        $entity = 'Stay';
        $redirect = 'case_stay_action';
        $redirectOptions = array('action' => 'edit', 'case' => 1, 'stay' => $result['id']);

        $sut = $this->getMock(
                '\Olcs\Controller\CaseStayController', ['redirect', 'processEdit', 'toRoute']
        );

        $sut->expects($this->at(0))
                ->method('processEdit')
                ->will($this->returnValue($result));

        //$sut->expects($this->at(1))
        //->method('toRoute')
        //->with($this->equalTo($redirect),$this->equalTo($redirectOptions));

        $sut->processEditStay($data);
    }
    
    /**
     * Returns a fake case object or null if input data is not int
     * 
     * @param int $caseId
     * @return array|null
     */
    public function caseProvider($caseId){
        if((int)$caseId){
            $case = ['key' => 'case'];
            return $case;
        }
        
        return null;
    }
    
    /**
     * Returns a fake stay object or null if input data is not int
     * 
     * @param int $caseId
     * @return array|null
     */
    public function stayProvider($stayId){
        if((int)$stayId){
            $stay = ['key' => 'stay'];
            return $stay;
        }
        
        return null;
    }
    
    /**
     * Data provider for testEditAction
     * 
     * @return array
     */
    public function editActionProvider(){
        return array(
            array(24,10),
            array(0,0),
            array(24,0),
            array(0,10),
            array('',''),
            array('a','b'),
            array(false,true)
        );
    }
    
    /**
     * Data provider for testAddAction
     * 
     * @return array
     */
    public function addActionProvider(){
        return array(
            array(24),
            array(0),
            array(''),
            array(false),
            array(true),
            array('a')
        );
    }
}
