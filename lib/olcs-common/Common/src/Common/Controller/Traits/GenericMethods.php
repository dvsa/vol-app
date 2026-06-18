<?php

namespace Common\Controller\Traits;

use Common\Service\Helper\FormHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Common\Util\FlashMessengerTrait;
use Laminas\Mvc\Controller\AbstractController;
use Laminas\Mvc\Controller\Plugin\Params;
use Laminas\Mvc\Controller\Plugin\Redirect;

/**
 * Generic Methods from legacy Abstract Action controller
 *
 * @property ScriptFactory $scriptFactory
 * @property FormHelperService $formHelperService
 * @property TableFactory $tableFactory
 * @method AbstractController getRequest()
 * @method AbstractController getPluginManager()
 * @method FlashMessengerTrait addErrorMessage($message)
 * @method Params|mixed params(string $param = null, mixed $default = null)
 * @method Redirect redirect()
 */
trait GenericMethods
{
    /**
     * Load an array of script files which will be rendered inline inside a view
     *
     * @param array $scripts Scripts
     */
    protected function loadScripts($scripts): void
    {
        $this->scriptFactory->loadFiles($scripts);
    }

    /**
     * Gets a from from either a built or custom form config.
     *
     * @param string $type Form name or class
     *
     * @return \Common\Form\Form
     */
    public function getForm($type)
    {
        $formHelper = $this->formHelper ?? $this->formHelperService;
        $form = $formHelper->createForm($type);
        $formHelper->setFormActionFromRequest($form, $this->getRequest());
        $formHelper->processAddressLookupForm($form, $this->getRequest());

        return $form;
    }

    /**
     * Method to process posted form data and validate it and process a callback
     *
     * @param \Common\Form\Form $form             Form
     * @param callable          $callback         onSuccess callback
     * @param array             $additionalParams onSuccess callback additional params
     * @param bool              $validateForm     is need validate form
     * @param array             $fieldValues      Forced Form Values
     *
     * @return \Common\Form\Form
     */
    public function formPost(
        $form,
        $callback = null,
        $additionalParams = [],
        $validateForm = true,
        $fieldValues = []
    ) {
        if (method_exists($this, 'alterFormBeforeValidation')) {
            $form = $this->alterFormBeforeValidation($form);
        }

        /* @var \Laminas\Http\Request $request */
        $request = $this->getRequest();

        if ($request->isPost()) {
            // if Files and Post are empty this is as symptom that the PHP ini post_max_size has been exceeded
            if (empty($request->getFiles()->toArray()) && empty($request->getPost()->toArray())) {
                $this->addErrorMessage('message.post_max_size_exceeded');
                return $form;
            }

            $data = array_merge((array) $request->getPost(), $fieldValues);
            $form->setData($data);

            if (method_exists($this, 'postSetFormData')) {
                $form = $this->postSetFormData($form);
            }

            /**
             * validateForm is true by default, we set it to false if we want to continue processing the form without
             * validation.
             */
            if (!$validateForm || $form->isValid()) {
                $validatedData = $validateForm ? $form->getData() : $data;
                $params = [
                    'validData' => $validatedData,
                    'form' => $form,
                    'params' => $additionalParams
                ];
                $this->callCallbackIfExists($callback, $params);
            } elseif (!$validateForm || !$form->isValid()) {
                if (method_exists($this, 'onInvalidPost')) {
                    $this->onInvalidPost($form);
                }
            }
        }

        return $form;
    }

    /**
     * Calls the callback function/method if exists.
     *
     * @param callable $callback Callback
     * @param array    $params   Callback params
     *
     * @throws \Exception
     */
    public function callCallbackIfExists($callback, $params): void
    {
        if (is_callable($callback)) {
            $callback($params);
        } elseif (is_callable([$this, $callback])) {
            call_user_func([$this, $callback], $params);
        } elseif (!empty($callback)) {
            throw new \Exception('Invalid form callback: ' . $callback);
        }
    }

    /**
     * Wraps the redirect()->toRoute to help with unit testing
     *
     * @param string $route   Route
     * @param array  $params  Parameters to use in url generation, if any
     * @param array  $options RouteInterface-specific options to use in url generation, if any
     * @param bool   $reuse   Whether to reuse matched parameters
     *
     * @return \Laminas\Http\Response
     */
    public function redirectToRoute($route = null, $params = [], $options = [], $reuse = false)
    {
        return $this->redirect()->toRoute($route, $params, $options, $reuse);
    }

    /**
     * Wraps the redirect()->toRouteAjax method to help with unit testing
     *
     * @param string $route   Route
     * @param array  $params  Parameters to use in url generation, if any
     * @param array  $options RouteInterface-specific options to use in url generation, if any
     * @param bool   $reuse   Whether to reuse matched parameters
     *
     * @return \Laminas\Http\Response
     */
    public function redirectToRouteAjax($route = null, $params = [], $options = [], $reuse = false)
    {
        return $this->redirect()->toRouteAjax($route, $params, $options, $reuse);
    }

    /**
     * Build a table from config and results, and return the table object
     *
     * @param string $table   Table config file
     * @param array  $results Table Data
     * @param array  $data    Params
     *
     * @return \Common\Service\Table\TableBuilder|string
     */
    public function getTable($table, $results, $data = [])
    {
        if (!isset($data['url'])) {
            $data['url'] = $this->getPluginManager()->get('url');
        }

        return $this->tableFactory->buildTable($table, $results, $data, false);
    }

    /**
     * Check if a button was pressed
     *
     * @param string $button Button name
     * @param array  $data   Post data
     *
     * @return bool
     */
    public function isButtonPressed($button, $data = null)
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();

        if (is_null($data)) {
            $data = (array)$request->getPost();
        }

        return $request->isPost() && isset($data['form-actions'][$button]);
    }

    /**
     * Get param from route
     *
     * @param string $name Route param name
     *
     * @return string
     */
    public function getFromRoute($name)
    {
        return $this->params()->fromRoute($name);
    }

    /**
     * Generate a form with data
     *
     * @param string   $name        Form name or class
     * @param callable $callback    onSuccess callback
     * @param array    $data        Form data
     * @param boolean  $tables      Is tables
     * @param array    $fieldValues onSuccess callback additional params
     *
     * @return \Common\Form\Form
     * @deprecated Used in 2 places only, better do not use and remove in future
     */
    public function generateFormWithData(
        $name,
        $callback,
        $data = null,
        $tables = false,
        $fieldValues = []
    ) {
        $form = $this->generateForm($name, $callback, $tables, $fieldValues);

        if (!$this->getRequest()->isPost() && is_array($data)) {
            $form->setData($data);
        }

        return $form;
    }

    /**
     * Generate a form with a callback
     *
     * @param string   $name        Form name or class
     * @param callable $callback    onSuccess callback
     * @param bool     $tables      is table
     * @param array    $fieldValues onSuccess callback additional params
     *
     * @return \Common\Form\Form
     * @deprecated Used twice only, better don't use and remove in future
     */
    protected function generateForm($name, $callback, $tables = false, $fieldValues = [])
    {
        $form = $this->getForm($name);

        if ($tables) {
            return $form;
        }

        return $this->formPost($form, $callback, [], true, $fieldValues);
    }
}
