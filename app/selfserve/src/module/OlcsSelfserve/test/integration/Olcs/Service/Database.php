<?php
namespace integration\OlcsSelfserve\Service;
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DatabaseTrait
 *
 * @author valtechuk
 */
trait Database {

    public function getDataBaseAdapter(){
        $adapter = new \Zend\Db\Adapter\Adapter(array(
        'driver' => 'Pdo_Mysql',
        'database' => 'olcs',
        'username' => 'olcs',
        'password' => 'valtecholcs'
        ));
        return $adapter;
    }
}

?>
