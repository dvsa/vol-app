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
        return $this->renderSection();
    }
}
