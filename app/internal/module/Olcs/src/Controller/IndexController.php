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

    // public function homeAction()
    // {
    //     $view = new ViewModel(['response' => 'Home page']);
    //     $view->setTemplate('index');
    //     return $view;
    // }

    public function formTestAction()
    {
        $form = new \Zend\Form\Form('testFormName');

        $form->add([
            'type' => 'Text',
            'name' => 'name',
            'options' => [
                'label' => 'Name',
                'help-block' => 'Text Field Help Text',
            ],
            'inputErrorClass' => 'error',
            'attributes' => [
                'id' => 'name',
                'placeholder' => 'Text Field Placeholder',
                'class' => 'long'
            ],
            'filters' => [
                ['name' => 'Zend\Filter\StringTrim'],
                ['name' => 'Zend\Filter\StringToLower'],
            ],
            'validators' => [
                new \Zend\Validator\StringLength(['min' => 10, 'max' => 100]),

            ]
        ]);

        // $form->add([
        //     'type' => 'DateSelect',
        //     'name' => 'dob',
        //     'options' => [
        //         'label' => 'Date of Birth',
        //         'label_attributes' => ['class' => ''],
        //         'column-size' => 'sm-6',
        //         'create_empty_option' => true,
        //         'render_delimiters' => false,
        //         'help-block' => 'Your date of birth',
        //     ],
        //     'attributes' => [
        //         'id' => 'dob',
        //     ]
        // ]);

        $form->add([
            'type' => '\Zend\Form\Element\Select',
            'name' => 'standardSelect',
            'options' => [
                'label' => 'Standard Select',
                'label_attributes' => ['class' => ''],
                'value_options' => [
                    '1' => 'Option 1',
                    '2' => 'Option 2',
                    '3' => 'Option 3',
                    '4' => 'Option 4',
                ],
                'empty_option' => 'Please Select',
                'disable_inarray_validator' => false,
                'help-block' => 'Standard Select Help Text',
            ],
            'attributes' => [
                'id' => 'standardSelect',
                'placeholder' => 'Standard Select Placeholder',
            ]
        ]);

        $form->add([
            'type' => '\Zend\Form\Element\MultiCheckbox',
            'name' => 'multiCheckbox',
            'options' => [
                'label' => 'Multi Checkbox',
                'label_attributes' => ['class' => ''],
                'value_options' => [
                    '1' => 'MultiCheckbox Option 1',
                    '2' => 'MultiCheckbox Option 2',
                    '3' => 'MultiCheckbox Option 3',
                    '4' => 'MultiCheckbox Option 4',
                ],
                'empty_option' => 'Please Select',
                'disable_inarray_validator' => false,
                'help-block' => 'Multi Checkbox Help Text',
            ],
            'attributes' => [
                'id' => 'multiCheckbox',
                'placeholder' => 'Multi Checkbox Placeholder',
            ]
        ]);

        $form->add([
            'type' => '\Zend\Form\Element\Radio',
            'name' => 'radioGender',
            'options' => [
                'label' => 'Radio Gender',
                'label_attributes' => ['class' => ''],
                'value_options' => [
                    '1' => 'Radio Gender Option 1',
                    '2' => 'Radio Gender Option 2',
                    '3' => 'Radio Gender Option 3',
                    '4' => 'Radio Gender Option 4',
                ],
                'empty_option' => 'Please Select',
                'disable_inarray_validator' => false,
                'help-block' => 'Radio Gender Help Text',
            ],
            'attributes' => [
                'id' => 'radioGender',
                'placeholder' => 'Radio Gender Placeholder',
            ]
        ]);

        $form->add([
            'type' => '\Zend\Form\Element\Textarea',
            'name' => 'freeText',
            'options' => [
                'label' => 'Free Text',
                'label_attributes' => ['class' => ''],
                'column-size' => 'sm-6',
                'help-block' => 'You can type anything in this box.',
            ],
            'attributes' => [
                'id' => 'freeText',
                'class' => 'extra-long'
            ],
            'filters' => [
                ['name' => 'Zend\Filter\StringTrim'],
                ['name' => 'Zend\Filter\StringToLower'],
            ],
            'validators' => [
                new \Zend\Validator\StringLength(['min' => 10, 'max' => 100]),

            ]
        ]);

        $form->add([
            'type' => 'Zend\Form\Element\Csrf',
            'name' => 'olcs_csrf'
        ]);

        $form->add([
            'type' => '\Zend\Form\Element\Button',
            'name' => 'submit',
            'options' => [
                'label' => 'Submit',
                'label_attributes' => ['class' => ''],
            ],
            'attributes' => [
                'type' => 'submit',
                'class' => 'button--primary'
            ]
        ]);

        if ($this->getRequest()->isPost()) {

            $data = $this->getRequest()->getPost();

            $form->setData($data);

            if ($form->isValid()) {
                //echo 'Valid';
            }
        }

        $view = new ViewModel(['form'=>$form]);
        $view->setTemplate('index/form-test.phtml');
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
