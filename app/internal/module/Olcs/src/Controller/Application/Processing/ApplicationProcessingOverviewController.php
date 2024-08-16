<?php

/**
 * Overview Controller
 */

namespace Olcs\Controller\Application\Processing;

/**
 * Application Processing Overview Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class ApplicationProcessingOverviewController extends AbstractApplicationProcessingController
{
    protected $section = 'overview';

    /**
     * index Action
     *
     * @return \Laminas\Http\Response
     */
    public function indexAction()
    {
        $options = [
            'query' => $this->getRequest()->getQuery()->toArray()
        ];
        return $this->redirectToRoute('lva-application/processing/tasks', [], $options, true);
    }
}
