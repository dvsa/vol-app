<?php
namespace Permits;

//all used for getServiceConfig (db services like factories and dat)
use Permits\Model\Product;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;

class Module {
	public function getAutoloaderConfig(){
		return array(
			'Zend\Loader\ClassMapAutoloader' => array(
				__DIR__ . '/autoload_classmap.php',
			),
			'Zend\Loader\StandardAutoloader' => array(
				'namespaces' => array(
					__NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
					),
			),
		);
	}
	
	public function getConfig(){
		return include __DIR__ . '/config/module.config.php';
	}
	
	public function getServiceConfig(){
		/*
		return array(
			'Permits\Model\BookTable' => function($sm){
				$tableGateway = $sm->get('BookTableGateway');
				$table = new BookTable($tableGateway);
				return $table;
			},
			'BookTableGateway' => function($sm) {
				$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
				$resultSetPrototype = new ResultSet();
				$resultSetPrototype->setArrayObjectPrototype(new Book());
				return new TableGateway('book', $dbAdapter, null, resultSetPrototype);
			},
			
			'Permits\Controller\Book' => function($sm){
				echo "TEST";
				return null;
			},
			
		);
		*/
	}
}

?>