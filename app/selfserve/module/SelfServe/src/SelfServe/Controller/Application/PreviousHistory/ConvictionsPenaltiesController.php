<?php

/**
 * ConvictionsPenalties Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace SelfServe\Controller\Application\PreviousHistory;

/**
 * ConvictionsPenalties Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ConvictionsPenaltiesController extends PreviousHistoryController
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
