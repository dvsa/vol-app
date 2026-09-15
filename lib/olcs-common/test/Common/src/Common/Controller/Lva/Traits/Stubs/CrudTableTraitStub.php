<?php

namespace CommonTest\Common\Controller\Lva\Traits\Stubs;

use Common\Controller\Lva\Traits\CrudTableTrait;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Laminas\Http\Response;
use Laminas\Mvc\Controller\AbstractActionController;

/**
 * CRUD Table Trait Stub
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class CrudTableTraitStub extends AbstractActionController
{
    use CrudTableTrait;

    /**
     * @var \Common\Service\Helper\FlashMessengerHelperService
     */
    public $flashMessengerHelper;

    /**
     * @var \Common\Service\Helper\FormHelperService
     */
    public $formHelper;

    protected $section = 'fake-section';

    public function __construct(FlashMessengerHelperService $flashMessengerHelper, FormHelperService $formHelper)
    {
        $this->flashMessengerHelper = $flashMessengerHelper;
        $this->formHelper = $formHelper;
    }

    public function callHandlePostSave(string|null $prefix = null, array $options = []): Response|string
    {
        return $this->handlePostSave($prefix, $options);
    }
}
