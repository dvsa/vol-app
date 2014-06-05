<?php

/**
 * AbstractController
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace SelfServe\Controller;

use Common\Controller\FormActionController;
use Zend\Filter\Word\CamelCaseToDash;
use Zend\Http\Response;
use Zend\View\Model\ViewModel;

/**
 * AbstractController
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractController extends FormActionController
{
    /**
     * Holds the caught response
     *
     * @var mixed
     */
    protected $caughtResponse = null;

    /**
     * Holds the loaded data
     *
     * @var array
     */
    protected $loadedData;

    /**
     * Holds the layout
     *
     * @var string
     */
    protected $layout;

    /**
     * Data map
     *
     * @var array
     */
    protected $dataMap = array(
        'main' => array(
            'mapFrom' => array(
                'data'
            )
        )
    );

    /**
     * Holds the Data Bundle
     *
     * @var array
     */
    protected $dataBundle = null;

    /**
     * Holds the service name
     *
     * @var string
     */
    protected $service = null;

    /**
     * Sets the caught response
     *
     * @param mixed $response
     */
    protected function setCaughtResponse($response)
    {
        $this->caughtResponse = $response;
    }

    /**
     * Getter for caughtResponse
     *
     * @return mixed
     */
    protected function getCaughtResponse()
    {
        return $this->caughtResponse;
    }

    /**
     * Set the layout
     *
     * @param string $layout
     */
    protected function setLayout($layout)
    {
        $this->layout = $layout;
    }

    /**
     * Get the layout
     *
     * @param string $layout
     */
    protected function getLayout()
    {
        return $this->layout;
    }

    /**
     * Gets the data map
     *
     * @return array
     */
    protected function getDataMap()
    {
        return $this->dataMap;
    }

    /**
     * Getter for data bundle
     *
     * @return array
     */
    protected function getDataBundle()
    {
        return $this->dataBundle;
    }

    /**
     * Get the service name
     *
     * @return string
     */
    protected function getService()
    {
        return $this->service;
    }

    /**
     * Get table settings
     *
     * This method should be overridden
     *
     * @return array
     */
    protected function getTableSettings()
    {
        return array();
    }

    /**
     * Check if a form exists
     *
     * @param string $formName
     * @return boolean
     */
    protected function formExists($formName)
    {
        return file_exists($this->getFormLocation($formName));
    }

    /**
     * Get a form location
     *
     * @param string $formName
     * @return string
     */
    protected function getFormLocation($formName)
    {
        return $this->getServiceLocator()->get('Config')['local_forms_path'] . $formName . '.form.php';
    }

    /*
     * Load an array of script files which will be rendered inline inside a view
     *
     * @param array $scripts
     * @return array
     */
    protected function loadScripts($scripts)
    {
        return $this->getServiceLocator()->get('Script')->loadFiles($scripts);
    }

    /**
     * Map the data on load
     *
     * @param array $data
     * @return array
     */
    protected function processLoad($data)
    {
        return $data;
    }

    /**
     * Save data
     *
     * @param array $data
     * @param string $service
     * @return array
     */
    protected function save($data, $service = null)
    {
        $method = 'POST';

        if (isset($data['id']) && !empty($data['id'])) {
            $method = 'PUT';
        }

        if (empty($service)) {
            $service = $this->getService();
        }

        return $this->makeRestCall($service, $method, $data);
    }

    /**
     * Complete section and save
     *
     * @param array $data
     * @return array
     */
    protected function processSave($data)
    {
        $data = $this->processDataMapForSave($data, $this->getDataMap());

        return $this->save($data);
    }

    /**
     * Process save when we have a table form
     *
     * @param array $data
     */
    protected function processSaveCrud($data)
    {
        $oldData = $data;

        $data = $this->processDataMapForSave($data, $this->getDataMap());

        $response = $this->saveCrud($data);

        if ($response instanceof Response || $response instanceof ViewModel) {
            $this->setCaughtResponse($response);
            return;
        }

        foreach (array_keys($this->formTables) as $table) {

            if (!is_array($oldData[$table]['action'])) {
                $action = strtolower($oldData[$table]['action']);
                $id = isset($oldData[$table]['id']) ? $oldData[$table]['id'] : null;
            } else {
                $action = array_keys($oldData[$table]['action'])[0];
                $id = (isset($oldData[$table]['action'][$action])
                    ? array_keys($oldData[$table]['action'][$action])[0]
                    : null);
            }

            if (!empty($action)) {

                $routeAction = $action;

                if ($table !== 'table') {
                    $routeAction = $table . '-' . $action;
                }

                if ($action == 'add') {
                    $this->setCaughtResponse(
                        $this->redirectToRoute(null, array('action' => $routeAction), array(), true)
                    );
                    return;
                }

                if (empty($id)) {
                    $this->setCaughtResponse($this->crudActionMissingId());
                    return;
                }

                $this->setCaughtResponse(
                    $this->redirectToRoute(
                        null,
                        array('action' => $routeAction, 'id' => $id),
                        array(),
                        true
                    )
                );
                return;
            }
        }
    }

    /**
     * Save crud data
     *
     * @param array $data
     * @return mixed
     */
    protected function saveCrud($data)
    {
        return $this->save($data);
    }

    /**
     * Load data for the form
     *
     * This method should be overridden
     *
     * @param int $id
     * @return array
     */
    protected function load($id)
    {
        if (empty($this->loadedData)) {
            $service = $this->getService();

            $result = $this->makeRestCall($service, 'GET', array('id' => $id), $this->getDataBundle());

            if (empty($result)) {
                $this->setCaughtResponse($this->notFoundAction());
                return;
            }

            $this->loadedData = $result;
        }

        return $this->loadedData;
    }

    /**
     * Process the data map for saving
     *
     * @param type $data
     */
    public function processDataMapForSave($oldData, $map = array(), $section = 'main')
    {
        if (empty($map)) {
            return $oldData;
        }

        if (isset($map['_addresses'])) {

            foreach ($map['_addresses'] as $address) {

                $oldData = $this->processAddressData($oldData, $address);
            }
        }

        if (isset($map[$section]['mapFrom'])) {

            $data = array();

            foreach ($map[$section]['mapFrom'] as $key) {

                if (!isset($oldData[$key])) {
                    return $oldData;
                }

                $data = array_merge($data, $oldData[$key]);
            }
        } else {
            $data = array();
        }

        if (isset($map[$section]['children'])) {

            foreach ($map[$section]['children'] as $child => $options) {

                $data[$child] = $this->processDataMapForSave($oldData, array($child => $options), $child);
            }
        }

        if (isset($map[$section]['values'])) {
            $data = array_merge($data, $map[$section]['values']);
        }

        return $data;
    }

    /**
     * Get the namespace parts
     *
     * @return array
     */
    public function getNamespaceParts()
    {
        $controller = get_called_class();

        return explode('\\', $controller);
    }

    /**
     * Convert camel case to dash
     *
     * @param string $string
     * @return string
     */
    protected function camelToDash($string)
    {
        $converter = new CamelCaseToDash();
        return strtolower($converter->filter($string));
    }
}
