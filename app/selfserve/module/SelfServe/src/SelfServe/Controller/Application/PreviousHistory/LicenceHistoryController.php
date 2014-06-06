<?php

/**
 * LicenceHistory Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace SelfServe\Controller\Application\PreviousHistory;

/**
 * LicenceHistory Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class LicenceHistoryController extends PreviousHistoryController
{
    /**
     * Set the service for the "Free" save behaviour
     *
     * @var string
     */
    protected $service = 'PreviousHistory';
    
    /**
     * Render the section form
     *
     * @return Response
     */
    public function indexAction()
    {
        return $this->renderSection();
    }

    /**
     * Placeholder save method
     *
     * @param array $data
     * @parem string $service
     */
    protected function save($data, $service = null)
    {
    }
}
