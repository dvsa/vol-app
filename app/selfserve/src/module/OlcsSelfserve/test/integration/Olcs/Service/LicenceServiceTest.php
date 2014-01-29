<?php
namespace integration\OlcsSelfserve\Service;
use \Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;


/**
 * Description of LicenceServiceTest
 *
 * @author valtechuk
 */
class LicenceServiceTest extends AbstractHttpControllerTestCase {
    
    
    protected $listOfLicences = array();
    
    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__.'/../../../../../../config/test/application.config.php'
        );
        parent::setUp();
    }
    
     public function tearDown() {

         /*
        foreach ($this->listOfLicences as $licenceId) {
            $adapter2 = $this->getDataBaseAdapter();
            $delete = "DELETE FROM licence where id = " . $licenceId;
            $adapter2->query($delete, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        }
          * 
          */
    }

    public function getDataBaseAdapter(){
        $adapter = new \Zend\Db\Adapter\Adapter(array(
        'driver' => 'Pdo_Mysql',
        'database' => 'olcs',
        'username' => 'olcs',
        'password' => 'valtecholcs'
        ));
        return $adapter;
    }
    
    
    private function getCountOfPersons() {
        $adapter = $this->getDataBaseAdapter();
        $results = $adapter->query('SELECT count(id) as personCount  FROM `person`', array(5));
        $row = $results->current();
        $personCount = $row['personCount'];
        return $personCount;
    }
    private function getCountOfTransportManagers() {
    
        $adapter = $this->getDataBaseAdapter();
        $results = $adapter->query('SELECT count(licence_id) as licenceCount  FROM `tms_licence_link`', array(5));
        $row = $results->current();
        $licenceCount = $row['licenceCount'];
        return $licenceCount;
    }
    
    private function insertLicence() {


        $results = $this->getDataBaseAdapter()->query("SELECT id FROM licence ORDER BY id DESC LIMIT 1", \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        $row = $results->current();
        $id = $row['id'] + 1;
        $this->listOfLicences[] = $id;

        $adapter2 = $this->getDataBaseAdapter();
        $insert = "INSERT INTO `licence` (`id`,`licenceNumber`,`status`,`licenceType`,`addressLine1`,`addressLine2`,`addressTown`,`addressPostcode`,`startDate`,`reviewDate`,`endDate`,`fabsReference`,`operatorId`,`version`) VALUES (".$id.",'1','valid','Sainburys Licence Type','address Line 1','addressLine 2','town 1','nw1 one','0000-00-00','0000-00-00','0000-00-00','Sainsburys Fab Reference 1',1,1);";
        $adapter2->query($insert, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);

        return $id;
    }

    public function testToAddCaseNoteToLicence() {


        $licenceId = $this->insertLicence();
        $serviceManager = $this->getApplicationServiceLocator();
        $caseService = $serviceManager->get("CaseServiceFactory");
        $licenceService = $serviceManager->get('LicenceServiceFactory');
        $lookupService = $serviceManager->get('LookupServiceFactory');

        $param = array('priority' => 1, 'note' => 'this is the note');
        $caseNote = $caseService->createCaseNote($param);
        $res = $licenceService->addCaseNote($caseNote, $licenceId);

        print_r("caer not id");
        print_r($res);
        $this->assertNotEmpty($res);

        $licence = $lookupService->findLicence($licenceId);
        $this->assertEquals(1, count($licence->getCaseNotes()));
    }
    
    

    public function testToAddMultipleCaseNotesToALicence() {

        $licenceId = $this->insertLicence();

        $serviceManager = $this->getApplicationServiceLocator();
        $caseService = $serviceManager->get("CaseServiceFactory");
        $licenceService = $serviceManager->get('LicenceServiceFactory');
        $lookupService = $serviceManager->get('LookupServiceFactory');

        $param = array('priority' => 1, 'note' => 'this is the note for licence 1');
        $caseNote = $caseService->createCaseNote($param);
        $res = $licenceService->addCaseNote($caseNote, $licenceId);
        

        $param = array('priority' => 1, 'note' => 'this is the note for licence 2');
        $caseNote = $caseService->createCaseNote($param);
        $res = $licenceService->addCaseNote($caseNote, $licenceId);

        $this->assertNotEmpty($res);

        $licence = $lookupService->findLicence($licenceId);
        $this->assertEquals(2, count($licence->getCaseNotes()));
    }


    private function getCountOfCaseNotes() {
        $adapter = $this->getDataBaseAdapter();
        $results = $adapter->query('SELECT count(id) as caseNoteCount  FROM `case_note`', array(5));
        $row = $results->current();
        $personCount = $row['caseNoteCount'];
        return $personCount;
    }
    
}

?>
