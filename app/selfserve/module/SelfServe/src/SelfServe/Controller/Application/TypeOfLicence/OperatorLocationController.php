<?php

/**
 * OperatorLocation Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace SelfServe\Controller\Application\TypeOfLicence;

/**
 * OperatorLocation Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class OperatorLocationController extends TypeOfLicenceController
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
