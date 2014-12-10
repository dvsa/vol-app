<?php

/**
 * Transport Manager Document Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Olcs\Controller\TransportManager;

use Olcs\Controller\TransportManager\TransportManagerController;
use Olcs\Controller\Traits;

/**
 * Transport Manager Document Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class TransportManagerDocumentController extends TransportManagerController
{

    use Traits\DocumentActionTrait;
    use Traits\DocumentSearchTrait;
    use Traits\ListDataTrait;

    /**
     * @var string
     */
    protected $section = 'documents';


    /**
     * Route (prefix) for document action redirects
     * @see Olcs\Controller\Traits\DocumentActionTrait
     * @return string
     */
    protected function getDocumentRoute()
    {
        return 'transport-manager/documents';
    }

    /**
     * Route params for document action redirects
     * @see Olcs\Controller\Traits\DocumentActionTrait
     * @return array
     */
    protected function getDocumentRouteParams()
    {
        return array(
            'transportManager' => $this->getFromRoute('transportManager'),
        );
    }

    /**
     * Get view model for document action
     * @see Olcs\Controller\Traits\DocumentActionTrait
     * @return ViewModel
     */
    protected function getDocumentView()
    {
        $transportManagerId = $this->getFromRoute('transportManager');

        $filters = $this->mapDocumentFilters(
            array('tmId' => $transportManagerId)
        );

        $table = $this->getDocumentsTable($filters);
        $form  = $this->getDocumentForm($filters);

        return $this->getViewWithTm(
            array(
                'table' => $table,
                'form'  => $form
            )
        );
    }
}
