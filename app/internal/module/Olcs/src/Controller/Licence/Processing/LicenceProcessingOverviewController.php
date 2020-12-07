<?php

/**
 * Overview Controller
 */
namespace Olcs\Controller\Licence\Processing;

use Laminas\View\Model\ViewModel;

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
    public function indexAction()
    {
        $options = [
            'query' => $this->getRequest()->getQuery()->toArray()
        ];
        return $this->redirectToRoute('licence/processing/tasks', [], $options, true);
    }
}
