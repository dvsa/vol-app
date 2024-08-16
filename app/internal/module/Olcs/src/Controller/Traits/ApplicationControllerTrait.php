<?php

namespace Olcs\Controller\Traits;

use Common\RefData;
use Dvsa\Olcs\Transfer\Query\Application\Application as ApplicationQry;
use Olcs\Controller\Interfaces\LeftViewProvider;

/**
 * Application Controller Trait
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
trait ApplicationControllerTrait
{
    /**
     * @param \Laminas\View\Model\ViewModel $view
     * @param string|null                   $title
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

    protected function renderPage($content, $title = '', array $variables = [])
    {
        if ($title) {
            $this->placeholder()->setPlaceholder('contentTitle', $title);
        }

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
        $data = $this->getApplication($this->params('application'));

        return [
            'applicationId' => $data['id'],
            'licNo' => $data['licence']['licNo'],
            'licenceId' => $data['licence']['id'],
            'companyName' => $data['licence']['organisation']['name']
        ];
    }

    /**
     * Gets the application by ID.
     *
     * @param  integer $id
     * @return array
     */
    protected function getApplication($id = null)
    {
        if (is_null($id)) {
            $id = $this->params('application');
        }

        $query = ApplicationQry::create(['id' => $id]);
        $response = $this->handleQuery($query);
        return $response->getResult();
    }

    /**
     * Get view with application
     *
     * @param  array $variables
     * @return \Laminas\View\Model\ViewModel
     */
    protected function getViewWithApplication($variables = [])
    {
        $application = $this->getApplication();
        $goodsOrPsv = $application['licence']['goodsOrPsv']['id'] ?? null;

        if ($goodsOrPsv === RefData::LICENCE_CATEGORY_GOODS_VEHICLE) {
            $this->navigation->findOneBy('id', 'licence_bus')->setVisible(0);
        }

        $variables = array_merge(
            $variables,
            $this->getHeaderParams()
        );

        return $this->getView($variables);
    }
}
