<?php

namespace Common\Controller\Lva\Traits;

/**
 * Create Variation Trait
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
trait CreateVariationTrait
{
    protected function processForm()
    {
        // @NOTE The behaviour of this service differs internally to externally
        $processingService = $this->processingCreateVariation;

        $request = $this->getRequest();

        $form = $processingService->getForm($request);

        if ($request->isPost() && $form->isValid()) {
            $data = $processingService->getDataFromForm($form);

            $licenceId = $this->params('licence');

            $appId = $processingService->createVariation($licenceId, $data);

            if ($appId === null) {
                $this->flashMessengerHelper->addErrorMessage('unknown-error');
                return $this->redirect()->refreshAjax();
            }

            $route = 'lva-variation';

            $redirectRoute = $this->params('redirectRoute');
            if ($redirectRoute !== null) {
                $route .= '/' . $redirectRoute;
            }

            return $this->redirect()->toRouteAjax($route, ['application' => $appId]);
        }

        return $form;
    }
}
