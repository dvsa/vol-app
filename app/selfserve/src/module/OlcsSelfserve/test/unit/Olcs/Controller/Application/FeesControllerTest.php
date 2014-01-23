<?php
namespace unit\Olcs\Controller\Application;

use \OlcsCommon\Controller\AbstractHttpControllerTestCase;
use \Mockery as m;

class FeesControllerTest extends AbstractHttpControllerTestCase
{
    protected $traceError = true;
    
    public function setUp($noConfig = false)
    {
        $this->setApplicationConfig(
            include __DIR__.'/../../../../../../../config/test/application.config.php'
        );
        parent::setUp();
    }

    /*
     * List of fees page
     * TODO: add service call tests when fee list is added
     */
    public function testShowFeesPageAction()
    {
        //$this->mockService('Olcs\Licence', 'create', array('caseNoteId' => 2))->with('1/case-notes', m::any());
        $this->dispatch('/application/1/fees', 'GET');
        $this->assertControllerClass('FeesController');
        $this->assertActionName('feesList');
        $this->assertResponseStatusCode(200);
    }
    
    public function testShowFeesDeclinedPageAction()
    {
        //$this->mockService('Olcs\Licence', 'create', array('caseNoteId' => 2))->with('1/case-notes', m::any());
        $this->dispatch('/application/1/fees', 'GET', array('declined'=>1, 'v'=>123));
        $this->assertControllerClass('FeesController');
        $this->assertActionName('feesList');
        $this->assertResponseStatusCode(200);
        
    }
    
    //TODO Test for 
    /*public function testShowStubCardPaymentPageAction()
    {
        //$this->mockService('Olcs\Licence', 'create', array('caseNoteId' => 2))->with('1/case-notes', m::any());
        $this->mockService('Vosa\Service\Payment', 'POST', array('grant_type' => "client_credentials",
                                                'client_id' => 'OLCS',
                                                'client_secret' => 'olcssecret',
                                                'scope' => 'payment',
                                                'redirect_uri' => '/application/1/123/complete',
                                                'payment_data' => array('amount' => '43.75')))
                
                ->with(m::any());
        $this->dispatch('/application/1/fees', 'POST', array('paymentTypeSelect'=>'card-payment'));
        $this->assertControllerClass('FeesController');
        $this->assertActionName('feesList');
        $this->assertResponseStatusCode(200);
        
    }*/
    
    public function testShowFeesSuccessPageAction()
    {
        //$this->mockService('Olcs\Licence', 'create', array('caseNoteId' => 2))->with('1/case-notes', m::any());
        $this->dispatch('/application/1/fees', 'GET', array('success'=>1, 'v'=>123));
        $this->assertControllerClass('FeesController');
        $this->assertActionName('feesList');
        $this->assertResponseStatusCode(200);
    }
    
    public function testCompletePaymentPageAction()
    {
        //$this->mockService('Olcs\Licence', 'create', array('caseNoteId' => 2))->with('1/case-notes', m::any());
        $this->dispatch('/application/1/fees', 'GET', array('success'=>1, 'v'=>123));
        $this->assertControllerClass('FeesController');
        $this->assertActionName('feesList');
        $this->assertResponseStatusCode(200);
    }

}
