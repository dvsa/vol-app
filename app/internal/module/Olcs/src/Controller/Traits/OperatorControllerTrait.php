<?php

/**
 * Operator Controller Trait
 */

namespace Olcs\Controller\Traits;

use Dvsa\Olcs\Transfer\Query\Operator\BusinessDetails as BusinessDetailsQry;

/**
 * Operator Controller Trait
 */
trait OperatorControllerTrait
{
    /**
     * Get view with Operator
     *
     * @param  array $variables
     * @return \Laminas\View\Model\ViewModel
     */
    protected function getViewWithOrganisation($variables = [])
    {
        $organisationId = $this->params()->fromRoute('organisation');

        if ($organisationId) {
            $org = $this->getBusinessDetailsData($organisationId);
            $this->pageTitle = $org['name'] ?? '';
            $variables['disable'] = false;
        } else {
            $org = null;
            $variables['disable'] = true;
            $variables['hideQuickActions'] = true;
        }
        $variables['organisation'] = $org;
        $variables['section'] = $this->section;

        $view = $this->getView($variables);

        return $view;
    }

    protected function getBusinessDetailsData($organisationId)
    {
        $retv = [];
        $queryToSend = $this->transferAnnotationBuilder
            ->createQuery(
                BusinessDetailsQry::create(['id' => $organisationId])
            );

        $response = $this->queryService->send($queryToSend);

        if ($response->isClientError() || $response->isServerError()) {
            $this->flashMessengerHelper->addErrorMessage('unknown-error');
        }

        if ($response->isOk()) {
            $retv = $response->getResult();
        }
        return $retv;
    }

    /**
     * Gets the main navigation
     *
     * @return \Laminas\Navigation\Navigation
     */
    public function getNavigation()
    {
        return $this->navigation;
    }

    /**
     * Gets the sub navigation
     *
     * @return \Laminas\Navigation\Page\Mvc
     */
    public function getSubNavigation()
    {
        return $this->getNavigation()->findOneBy('id', $this->getSubNavRoute());
    }

    /**
     * Returns the sub nav route
     *
     * @return string
     */
    public function getSubNavRoute()
    {
        return $this->subNavRoute;
    }
}
