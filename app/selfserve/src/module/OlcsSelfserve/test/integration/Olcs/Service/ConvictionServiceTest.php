<?php

namespace integration\OlcsSelfserve\Service;

use \Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class ConvictionServiceTest extends AbstractHttpControllerTestCase {

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

}
