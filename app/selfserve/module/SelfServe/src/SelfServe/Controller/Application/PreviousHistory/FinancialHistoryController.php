<?php

/**
 * FinancialHistory Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace SelfServe\Controller\Application\PreviousHistory;

/**
 * FinancialHistory Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class FinancialHistoryController extends PreviousHistoryController
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
