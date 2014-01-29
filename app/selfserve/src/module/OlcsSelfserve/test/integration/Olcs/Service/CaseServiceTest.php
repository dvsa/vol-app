<?php

namespace integration\OlcsSelfserve\Service;

use \Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class CaseServiceTest extends AbstractHttpControllerTestCase {

    protected $traceError = true;
    protected $listOfCaseNotes = array();
    protected $listOfCases = array();

    public function setUp() {
        $this->setApplicationConfig(
                include __DIR__ . '/../../../../../../config/test/application.config.php'
        );
        parent::setUp();
    }

    public function getDataBaseAdapter() {
        $adapter = new \Zend\Db\Adapter\Adapter(array(
            'driver' => 'Pdo_Mysql',
            'database' => 'olcs',
            'username' => 'olcs',
            'password' => 'valtecholcs'
        ));
        return $adapter;
    }

    private function insertCase() {


        $results = $this->getDataBaseAdapter()->query("SELECT id FROM t_case ORDER BY id DESC LIMIT 1", \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        $row = $results->current();
        $id = $row['id'] + 1;
        $this->listOfCases[] = $id;

        $adapter2 = $this->getDataBaseAdapter();
        $insert = "INSERT INTO `t_case` (`id`,`licence`,`description`,`ecms`,`openTime`,`owner`,`version`,`caseNumber`) VALUES (" . $id . ",1,'Sainsburys case for licence 1','1','0000-00-00 00:00:00','',1,'test case one')";
        $adapter2->query($insert, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);

        return $id;
    }

    public function tearDown() {

        foreach ($this->listOfCaseNotes as $caseNoteId) {
            $adapter2 = $this->getDataBaseAdapter();
            $delete = "DELETE FROM case_note where id = " . $caseNoteId;
            $adapter2->query($delete, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        }
    }

    public function testCreateCaseDetailComment() {

        $serviceManager = $this->getApplicationServiceLocator();
        $caseService = $serviceManager->get('CaseServiceFactory');

        // create case
        $caseParameters = array(
            'licenceId' => 1,
            'description' => 'description of new case',
            'ecms' => '12345678',
            'operatorId' => 1,
            'categories' => array('Convictions')
        );
        $caseId = $caseService->createTcase($caseParameters);

        // attach a case note detail to it
        $params = array('caseId' => $caseId,
            'comment' => 'Lorem ipsum sic dolor');
        $commentId = $caseService->createCaseDetailComment($params);

        $this->assertNotEmpty($commentId);
    }

    public function testAddCaseNote() {

        $serviceManager = $this->getApplicationServiceLocator();
        $caseService = $serviceManager->get('CaseServiceFactory');

        $param = array('priority' => 1, 'note' => 'this is the note');

        $caseNote = $caseService->createCaseNote($param);

        $countBefore = $this->getCountOfCaseNotes();
        $caseId = 0;
        $res = $caseService->addCaseNote($caseNote, $caseId);
        $countAfter = $this->getCountOfCaseNotes();

        $this->listOfCaseNotes[] = $res;
        $this->assertNotEmpty($res);
        $this->assertEquals($countBefore + 1, $countAfter);
    }

    public function testToAddCaseNoteToCase() {


        $caseId = $this->insertCase();
        $serviceManager = $this->getApplicationServiceLocator();
        $caseService = $serviceManager->get('CaseServiceFactory');

        $param = array('priority' => 1, 'note' => 'this is the note');
        $caseNote = $caseService->createCaseNote($param);
        $res = $caseService->addCaseNote($caseNote, $caseId);

        $this->assertNotEmpty($res);

        $case = $caseService->findCaseById($caseId);
        $this->assertEquals(1, count($case->getCaseNotes()));
    }

    public function testToAddMultipleCaseNotesToACase() {

        $caseId = $this->insertCase();
        $serviceManager = $this->getApplicationServiceLocator();
        $caseService = $serviceManager->get('CaseServiceFactory');

        $param = array('priority' => 1, 'note' => 'this is the note 1');
        $caseNote = $caseService->createCaseNote($param);
        $res = $caseService->addCaseNote($caseNote, $caseId);

        $param = array('priority' => 1, 'note' => 'this is the note 2');
        $caseNote = $caseService->createCaseNote($param);
        $res = $caseService->addCaseNote($caseNote, $caseId);

        print_r($res);
        $this->assertNotEmpty($res);

        $case = $caseService->findCaseById($caseId);
        $this->assertEquals(2, count($case->getCaseNotes()));
    }

    private function getCountOfCaseNotes() {
        $adapter = $this->getDataBaseAdapter();
        $results = $adapter->query('SELECT count(id) as caseNoteCount  FROM `case_note`', array(5));
        $row = $results->current();
        $personCount = $row['caseNoteCount'];
        return $personCount;
    }

}
