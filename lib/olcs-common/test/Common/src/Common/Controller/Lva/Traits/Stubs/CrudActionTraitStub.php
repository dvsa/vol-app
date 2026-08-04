<?php

namespace CommonTest\Common\Controller\Lva\Traits\Stubs;

use Common\Controller\Lva\Traits\CrudActionTrait;
use Common\Service\Helper\FlashMessengerHelperService;
use Laminas\Http\Response;
use Laminas\Mvc\Controller\AbstractActionController;

/**
 * CRUD Action Trait Stub
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CrudActionTraitStub extends AbstractActionController
{
    use CrudActionTrait;

    public $baseRoute;

    public $lva;

    public $flashMessengerHelper;

    public function __construct(FlashMessengerHelperService $flashMessengerHelper)
    {
        $this->flashMessengerHelper = $flashMessengerHelper;
    }


    public function callGetCrudAction(array $formTables = []): ?array
    {
        return $this->getCrudAction($formTables);
    }

    public function callGetActionFromCrudAction($data): string
    {
        return $this->getActionFromCrudAction($data);
    }

    public function callHandleCrudAction(
        array $data,
        array $rowsNotRequired = ['add'],
        string $childIdParamName = 'child_id',
        $route = null
    ): Response|string {
        return $this->handleCrudAction($data, $rowsNotRequired, $childIdParamName, $route);
    }

    public function callGetBaseRoute(): string|null
    {
        return $this->getBaseRoute();
    }
}
