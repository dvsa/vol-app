<?php

namespace Permits\Controller;

use Olcs\Controller\AbstractSelfserveController;

class WindowClosedController extends AbstractSelfserveController
{
    protected $templateConfig = [
        'generic' => 'permits/window-closed'
    ];

    protected $templateVarsConfig = [
        'generic' => [
            'browserTitle' => 'permits.page.irhp-window-closed.browser.title',
        ]
    ];
}
