<?php

/**
 * LicenceHistory Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace SelfServe\Controller\Application\PreviousHistory;

/**
 * LicenceHistory Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicenceHistoryController extends PreviousHistoryController
{
    /**
     * Render the section form
     *
     * @return Response
     */
    public function indexAction()
    {
        $view = $this->getViewModel();
        $view->setTemplate('self-serve/journey/placeholder');

        return $this->renderSection($view);
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
