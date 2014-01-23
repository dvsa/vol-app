<?php
namespace unit\Olcs\Controller\VCase;

use \OlcsCommon\Controller\AbstractHttpControllerTestCase;
use \Mockery as m;

class CaseNotesControllerTest extends AbstractHttpControllerTestCase
{
    protected $traceError = true;

    public function testLicenceAddAction()
    {
        $this->mockService('Olcs\Licence', 'create', array('caseNoteId' => 2))->with('1/case-notes', m::any());
        $this->dispatch('/case/note/licence-add', 'GET', array('comment' => 'this is a case note','priority' => 1, 'licenceId' => 1));
        $this->assertControllerClass('CaseNotesController');
        $this->assertResponseStatusCode(200);
    }

    public function testCaseAddAction()
    {
        $this->mockService('Olcs\Case', 'create', array('caseNoteId' => 2))->with('1/case-notes', m::any());
        $this->dispatch('/case/note/case-add', 'GET', array('comment' => 'this is a case note','priority' => 1, 'caseId' => 1));
        $this->assertControllerClass('CaseNotesController');
        $this->assertResponseStatusCode(200);        
    }

    public function testCaseReadAction()
    {
        $this->mockService('Olcs\Case', 'get', array(
            'rows' => array(
                array(
                    'caseNoteId' => 6,
                    'createdAt' => '2013-10-06T10:37:26+0000',
                    'priority' => '1447',
                    'comment' => 'Foobar',
                ),
            ),
        ))->with('1/case-notes');

        $this->dispatch('/case/note/case-read', 'GET', array('caseId' => 1));
        $this->assertControllerClass('CaseNotesController');
        $this->assertResponseStatusCode(200);
    }

    public function testLicenceReadAction()
    {
        $this->mockService('Olcs\Licence', 'get', array(
            'rows' => array(
                array(
                    'caseNoteId' => 6,
                    'createdAt' => '2013-10-06T10:37:26+0000',
                    'priority' => '1447',
                    'comment' => 'Foobar',
                ),
            ),
        ))->with('1/case-notes');

        $this->dispatch('/case/note/licence-read', 'GET', array('licenceId' => 1));
        $this->assertControllerClass('CaseNotesController');
        $this->assertResponseStatusCode(200);
    }
}
