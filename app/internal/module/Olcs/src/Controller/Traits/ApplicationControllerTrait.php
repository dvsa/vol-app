<?php

namespace Olcs\Controller\Traits;

use Olcs\Controller\Interfaces\LeftViewProvider;
use Common\Service\Entity\LicenceEntityService;

/**
 * Application Controller Trait
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
trait ApplicationControllerTrait
{
    /**
     * @param \Zend\View\Model\ViewModel $view
     * @param string|null  $title
     * @param array $variables
     *
     * @return mixed
     */
    protected function render($view, $title = null, array $variables = [])
    {
        if ($title === null) {
            $title = $view->getVariable('title');
        }

        if (count($variables) === 0) {
            $variables = $view->getVariables();
        }

        return $this->renderPage($view, $title, $variables);
    }

    protected function renderPage($content, $title, array $variables = [])
    {
        $this->placeholder()->setPlaceholder('contentTitle', $title);

        $layout = $this->viewBuilder()->buildView($content);

        if (!($this instanceof LeftViewProvider)) {
            $left = $this->getLeft($variables);

            if ($left) {
                $layout->setLeft($left);
            }
        }

        return $layout;
    }

    protected function getLeft(array $variables = [])
    {
        return null;
    }

    /**
     * Get headers params
     *
     * @return array
     */
    protected function getHeaderParams()
    {
        $data = $this->getServiceLocator()->get('Entity\Application')->getHeaderData($this->params('application'));

        return array(
            'applicationId' => $data['id'],
            'licNo' => $data['licence']['licNo'],
            'licenceId' => $data['licence']['id'],
            'companyName' => $data['licence']['organisation']['name']
        );
    }

    /**
     * Gets the application by ID.
     *
     * @param integer $id
     * @return array
     */
    protected function getApplication($id = null)
    {
        if (is_null($id)) {
            $id = $this->params('application');
        }

        return $this->getServiceLocator()->get('Entity\Application')
            ->getDataForProcessing($id);
    }

    /**
     * Get view with application
     *
     * @param array $variables
     * @return \Zend\View\Model\ViewModel
     */
    protected function getViewWithApplication($variables = array())
    {
        $application = $this->getApplication();

        if ($application['licence']['goodsOrPsv']['id'] == LicenceEntityService::LICENCE_CATEGORY_GOODS_VEHICLE) {
            $this->getServiceLocator()->get('Navigation')->findOneBy('id', 'licence_bus')->setVisible(0);
        }

        $variables = array_merge(
            $variables,
            $this->getHeaderParams()
        );

        return $this->getView($variables);
    }
}
