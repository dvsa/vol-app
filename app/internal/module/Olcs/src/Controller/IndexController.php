<?php

/**
 * @package    olcs
 * @subpackage
 * @author     Mike Cooper
 */

namespace Olcs\Controller;

use Common\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Common\Exception\ResourceNotFoundException;
use Common\Exception\BadRequestException;
use OlcsEntities\Entity\User;
use Common\Exception\ResourceConflictException;

class IndexController extends AbstractActionController
{

    public function notFoundAction()
    {
        $routeMatch = $this->getEvent()->getRouteMatch();
        $method = $routeMatch->getParam('action');

        $view = new ViewModel(['method' => $method]);
        $view->setTemplate('index/' . $method . '.phtml');
        return $view;
    }

    public function indexAction()
    {
        $id = $this->getEvent()->getRouteMatch()->getParam('id');
        $this->log('MWC - index '.date('d-m-Y'));

        $response = $this->getUserById($id);

        $view = new ViewModel(['response' => $response]);
        $view->setTemplate('index');
        return $view;
    }

    private function getUserById($id)
    {
        try {

            $response = $this->makeRestCall('User', 'GET', array('id' => $id));

        } catch (ResourceNotFoundException $ex) {

            // @todo: Handle 404 not found exception
            die('404 not found');

        } catch (\Exception $ex) {

            // @todo Handle unexpected exception
            die('Unknown problem: ' . $ex->getMessage());
        }

        return $response;
    }

    public function searchAction()
    {
        $username = $this->getEvent()->getRouteMatch()->getParam('username');
        $this->log('MWC - index '.date('d-m-Y'));

        try {

            $response = $this->makeRestCall('User', 'GET', array('username' => '%' . $username . '%'));

        } catch (ResourceNotFoundException $ex) {

            // @todo: Handle 404 not found exception
            die('404 not found');

        } catch (\Exception $ex) {

            // @todo Handle unexpected exception
            die('Unknown problem: ' . $ex->getMessage());
        }

        $view = new ViewModel(['response' => $response]);
        $view->setTemplate('index');
        return $view;
    }

    public function createAction()
    {
        $this->log('MWC - index '.date('d-m-Y'));

        $entity = new User();

        $entity->setUsername('Bobby123');
        $entity->setPassword('password');

        try {

            $response = $this->makeRestCall('User', 'POST', $entity);

        } catch (BadRequestException $ex) {

            // @todo Handle 400 Bad request
            // This most likely means there is something wrong with the entity
            die('Bad request: ' . $ex->getMessage());

        } catch (\Exception $ex) {

            // @todo Handle unexpected exception
            die('Unknown problem: ' . $ex->getMessage());
        }

        $view = new ViewModel(['response' => $response]);
        $view->setTemplate('index');
        return $view;
    }

    public function updateAction()
    {
        $this->log('MWC - index '.date('d-m-Y'));

        $id = $this->getEvent()->getRouteMatch()->getParam('id');

        // Ensure when updating you have a version number
        $entity = array(
            'username' => 'Updated Username',
            'version' => 2
        );

        try {

            $response = $this->makeRestCall('User', 'PUT', array('id' => $id, 'details' => $entity));

        } catch (BadRequestException $ex) {

            // @todo Handle 400 Bad request
            // This most likely means there is something wrong with the entity
            die('Bad request: ' . $ex->getMessage());

        } catch (ResourceNotFoundException $ex) {

            // @todo: Handle 404 not found exception
            die('404 not found');

        } catch (ResourceConflictException $ex) {

            // @todo: Handle 409 resource conflict
            die('409 resource conflict (Optimistic Locking)');

        } catch (\Exception $ex) {

            // @todo Handle unexpected exception
            die('Unknown problem: ' . $ex->getMessage());
        }

        $view = new ViewModel(['response' => $response]);
        $view->setTemplate('index');
        return $view;
    }

    public function patchAction()
    {
        $this->log('MWC - index '.date('d-m-Y'));

        $id = $this->getEvent()->getRouteMatch()->getParam('id');

        // Ensure when updating you have a version number
        $entity = array(
            'username' => 'Updated Username',
            'version' => 4
        );

        try {

            $response = $this->makeRestCall('User', 'PATCH', array('id' => $id, 'details' => $entity));

        } catch (BadRequestException $ex) {

            // @todo Handle 400 Bad request
            // This most likely means there is something wrong with the entity
            die('Bad request: ' . $ex->getMessage());

        } catch (ResourceNotFoundException $ex) {

            // @todo: Handle 404 not found exception
            die('404 not found');

        } catch (ResourceConflictException $ex) {

            // @todo: Handle 409 resource conflict
            die('409 resource conflict (Optimistic Locking)');

        } catch (\Exception $ex) {

            // @todo Handle unexpected exception
            die('Unknown problem: ' . $ex->getMessage());
        }

        $view = new ViewModel(['response' => $response]);
        $view->setTemplate('index');
        return $view;
    }

    public function deleteAction()
    {
        $id = $this->getEvent()->getRouteMatch()->getParam('id');
        $this->log('MWC - index '.date('d-m-Y'));

        try {

            $response = $this->makeRestCall('User', 'DELETE', array('id' => $id));

        } catch (ResourceNotFoundException $ex) {

            // @todo: Handle 404 not found exception
            die('404 not found');

        } catch (\Exception $ex) {

            // @todo Handle unexpected exception
            die('Unknown problem: ' . $ex->getMessage());
        }

        $view = new ViewModel(['response' => $response]);
        $view->setTemplate('index');
        return $view;
    }

}
