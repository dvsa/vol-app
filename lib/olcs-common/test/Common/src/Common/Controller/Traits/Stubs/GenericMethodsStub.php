<?php

declare(strict_types=1);

namespace CommonTest\Common\Controller\Traits\Stubs;

use Common\Controller\Traits;
use Common\Service\Helper\FormHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;

class GenericMethodsStub
{
    use Traits\GenericMethods;

    public FormHelperService $formHelperService;
    private ScriptFactory $scriptFactory;
    private TableFactory $tableFactory;

    public function __construct(FormHelperService $formHelperService)
    {
        $this->formHelperService = $formHelperService;
    }
}
