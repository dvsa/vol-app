<?php

namespace Olcs\Controller\IrhpPermits;

/**
 * Irhp Application Processing Overview Controller
 */
class IrhpApplicationProcessingOverviewController extends AbstractIrhpPermitProcessingController
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

        return $this->redirectToRoute('licence/irhp-application-processing/notes', [], $options, true);
    }
}
