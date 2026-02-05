<?php

/**
 * Overview Controller
 */

namespace Olcs\Controller\Licence\Processing;

/**
 * Licence Processing Overview Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class LicenceProcessingOverviewController extends AbstractLicenceProcessingController
{
    protected $section = 'overview';

    /**
     * index Action
     *
     * @return \Laminas\Http\Response
     */
    #[\Override]
    public function indexAction()
    {
        $options = [
            'query' => $this->getRequest()->getQuery()->toArray()
        ];
        return $this->redirectToRoute('licence/processing/tasks', [], $options, true);
    }
}
