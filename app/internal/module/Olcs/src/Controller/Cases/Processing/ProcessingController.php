<?php

/**
 * Processing Controller
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
namespace Olcs\Controller\Cases\Processing;

use Olcs\Controller as OlcsController;
use Olcs\Controller\Traits as ControllerTraits;
use Olcs\Controller\Interfaces\CaseControllerInterface;

/**
 * Case Processing Controller
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
class ProcessingController extends OlcsController\CrudAbstract implements CaseControllerInterface
{
    use ControllerTraits\CaseControllerTrait;

    /**
     * Details View
     *
     * @var string
     */
    protected $identifierName = 'case';

    public function overviewAction()
    {
        return $this->redirectToRoute($this->getRouteToRedirectTo(), ['action' => 'index'], [], true);
    }

    public function redirectToIndex()
    {
        return $this->redirectToRoute($this->getRouteToRedirectTo(), [], [], true);
    }

    public function detailsAction()
    {
        return $this->redirectToIndex();
    }

    private function getRouteToRedirectTo()
    {
        return ($this->getCase()->isTm()) ? 'processing_decisions' : 'processing_in_office_revocation';
    }
}
