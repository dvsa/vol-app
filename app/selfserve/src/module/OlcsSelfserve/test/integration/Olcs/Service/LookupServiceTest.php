<?php

namespace integration\OlcsSelfserve\Service;

use \Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use \Zend\DB\Sql\Sql;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LookupServiceTest
 *
 * @author valtechuk
 */
class LookupServiceTest extends AbstractHttpControllerTestCase {

    protected $listOfLicences = array();
    protected $listOfPerson = array();
    protected $listOfUsers = array();

    public function setUp() {
        $this->setApplicationConfig(
                include __DIR__ . '/../../../../../../config/test/application.config.php'
        );
        parent::setUp();
    }

    public function tearDown() {

        foreach ($this->listOfLicences as $licenceId) {
            $adapter2 = $this->getDataBaseAdapter();
            $delete = "DELETE FROM tms_licence_link where licence_id = " . $licenceId;
            $adapter2->query($delete, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);

            $adapter1 = $this->getDataBaseAdapter();
            $delete = "DELETE FROM licence where id = " . $licenceId;
            $adapter1->query($delete, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        }


        foreach ($this->listOfPerson as $personId) {

            $adapter2 = $this->getDataBaseAdapter();
            $delete = "DELETE FROM tms_licence_link where person_id = " . $personId;
            $adapter2->query($delete, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);

            $adapter1 = $this->getDataBaseAdapter();
            $delete = "DELETE FROM person where id = " . $personId;
            $adapter1->query($delete, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        }

        foreach ($this->listOfUsers as $userId) {
            $adapter1 = $this->getDataBaseAdapter();
            $delete = "DELETE FROM user where id = " . $userId;
            $adapter1->query($delete, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        }
        parent::tearDown();
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

    private function setUpTestDataLicenceWithMultiplePeople() {

        $adapter = $this->getDataBaseAdapter();

        $results = $this->getDataBaseAdapter()->query("SELECT id FROM person ORDER BY id DESC LIMIT 1", \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);

        $row = $results->current();
        $id = $row['id'] + 1;
        $this->listOfPerson[] = $id;
        $insert = "INSERT INTO `person`  VALUES (" . $id . ",1,'TM  1 First Name','TM 1 Last Name','2000-01-01','person type',1)";
        $adapter->query($insert, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);

        $id = $id + 1;
        $this->listOfPerson[] = $id;
        $insert = "INSERT INTO `person`  VALUES (" . $id . ",1,'TM 2 First Name','TM 2 Last Name','2002-02-02','person type',1)";
        $adapter->query($insert, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);


        $id = $id + 1;
        $this->listOfPerson[] = $id;
        $insert = "INSERT INTO `person`  VALUES (" . $id . ",1,'TM 3 First Name','TM 3 Last Name','2003-03-03','person type',1)";
        $adapter->query($insert, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);

        $result3 = $this->getDataBaseAdapter()->query("SELECT id FROM licence ORDER BY id DESC LIMIT 1", \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        $row = $result3->current();
        //$licenceId = $row['id'];
        $licenceId = 1;
        $result4 = $this->getDataBaseAdapter()->query("SELECT id FROM tms_licence_link ORDER BY id DESC LIMIT 1", \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        $row = $result4->current();
        $tmsLinkID = $row['id'] + 1;
        foreach ($this->listOfPerson as $personId) {
            $insert = "INSERT INTO `tms_licence_link` (`id`,`licence_id`, `internal_external`,`person_id`) VALUES (" . $tmsLinkID . "," . $licenceId . ",'internal'," . $personId . ")";
            $adapter->query($insert, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
            $tmsLinkID = $tmsLinkID + 1;
        }

        return $licenceId;
    }

    private function setUpTestDataPersonWithMultipleLicence() {

        $adapter = $this->getDataBaseAdapter();

        $results = $this->getDataBaseAdapter()->query("SELECT id FROM licence ORDER BY id DESC LIMIT 1", \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);

        $row = $results->current();
        $id = $row['id'] + 1;
        $this->listOfLicences[] = $id;
        $insert = "INSERT INTO `licence` (`id`,`licenceNumber`,`status`,`licenceType`,`addressLine1`,`addressLine2`,`addressTown`,`addressPostcode`,`startDate`,`reviewDate`,`endDate`,`fabsReference`,`operatorId`,`version`) VALUES (" . $id . ",'1','valid','Sainburys Licence Type','address Line 1','addressLine 2','town 1','nw1 one','0000-00-00','0000-00-00','0000-00-00','Sainsburys Fab Reference 1',1,1)";
        $adapter->query($insert, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);

        $id = $id + 1;
        $this->listOfLicences[] = $id;
        $insert = "INSERT INTO `licence` (`id`,`licenceNumber`,`status`,`licenceType`,`addressLine1`,`addressLine2`,`addressTown`,`addressPostcode`,`startDate`,`reviewDate`,`endDate`,`fabsReference`,`operatorId`,`version`) VALUES (" . $id . ",'1','valid','Sainburys Licence Type','address Line 1','addressLine 2','town 1','nw1 one','0000-00-00','0000-00-00','0000-00-00','Sainsburys Fab Reference 1',1,1)";
        $adapter->query($insert, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);

        $id = $id + 1;
        $this->listOfLicences[] = $id;
        $insert = "INSERT INTO `licence` (`id`,`licenceNumber`,`status`,`licenceType`,`addressLine1`,`addressLine2`,`addressTown`,`addressPostcode`,`startDate`,`reviewDate`,`endDate`,`fabsReference`,`operatorId`,`version`) VALUES (" . $id . ",'1','valid','Sainburys Licence Type','address Line 1','addressLine 2','town 1','nw1 one','0000-00-00','0000-00-00','0000-00-00','Sainsburys Fab Reference 1',1,1)";

        $adapter->query($insert, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);

        $result3 = $this->getDataBaseAdapter()->query("SELECT id FROM person ORDER BY id DESC LIMIT 1", \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        $row = $result3->current();
        $personId = $row['id'];


        $result4 = $this->getDataBaseAdapter()->query("SELECT id FROM tms_licence_link ORDER BY id DESC LIMIT 1", \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        $row = $result4->current();
        $tmsLinkID = $row['id'] + 1;
        foreach ($this->listOfLicences as $licenceId) {
            $insert = "INSERT INTO `tms_licence_link` (`id`,`licence_id`, `internal_external`,`person_id`) VALUES (" . $tmsLinkID . "," . $licenceId . ",'internal'," . $personId . ")";
            $adapter->query($insert, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
            $tmsLinkID = $tmsLinkID + 1;
        }

        return $personId;
    }

    public function testForLookupTmLinksBasedOnPersonId() {

        $personId = $this->setUpTestDataPersonWithMultipleLicence();

        $serviceManager = $this->getApplicationServiceLocator();
        $lookupService = $serviceManager->get('LookupServiceFactory');
        $license = $lookupService->findLicencesFromTmLinksForPersonId($personId);

        $count = count($license);
        print_r($count);
    }

    public function testForLookupTmLinksBasedOnLicenceId() {

        $licenceId = $this->setUpTestDataLicenceWithMultiplePeople();

        $serviceManager = $this->getApplicationServiceLocator();
        $lookupService = $serviceManager->get('LookupServiceFactory');
        $persons = $lookupService->findPersonsFromTmLinksForLicenceId($licenceId);

        $count = count($persons);
        print_r($count);
    }

    private function createUserInfo() {
        $adapter = $this->getDataBaseAdapter();

        $results = $this->getDataBaseAdapter()->query("SELECT id FROM user ORDER BY id DESC LIMIT 1", \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);

        $row = $results->current();
        $id = $row['id'] + 1;
        $this->listOfUsers[] = $id;
        $insert = "INSERT INTO `user` (`id`,`version`,`username`,`current_role`,`current_location`) VALUES (" . $id . ",1,'test_user','users role','users location')";
        $adapter->query($insert, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
    }
}
