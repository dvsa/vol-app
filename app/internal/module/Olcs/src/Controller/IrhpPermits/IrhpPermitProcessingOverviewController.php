<?php

namespace Olcs\Controller\IrhpPermits;

/**
 * Irhp Permit Processing Overview Controller
 */
class IrhpPermitProcessingOverviewController extends AbstractIrhpPermitProcessingController
{
    /**
     * index Action
     *
     * @return \Zend\Http\Response
     */
    public function indexAction()
    {
        $options = [
            'query' => $this->getRequest()->getQuery()->toArray()
        ];
        return $this->redirectToRoute('licence/irhp-processing/tasks', [], $options, true);
    }
}
