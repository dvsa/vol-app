<?php

/**
 * Overview Controller
 */
namespace Olcs\Controller\Licence\Processing;

use Zend\View\Model\ViewModel;

/**
 * Licence Processing Overview Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class LicenceProcessingOverviewController extends AbstractLicenceProcessingController
{
    protected $section = 'overview';

    public function indexAction()
    {
        $query = $this->getRequest()->getQuery()->toArray();
        $options = [
            'query' => $query
        ];
        return $this->redirectToRoute('licence/processing/tasks', [], $options, true);
    }
}
