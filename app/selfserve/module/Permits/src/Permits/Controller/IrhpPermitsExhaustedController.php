<?php

namespace Permits\Controller;

use Olcs\Controller\AbstractSelfserveController;

class IrhpPermitsExhaustedController extends AbstractSelfserveController
{
    protected $templateConfig = [
        'generic' => 'permits/exhausted'
    ];

    protected $templateVarsConfig = [
        'generic' => [
            'browserTitle' => 'permits.page.irhp-window-closed.browser.title',
        ]
    ];
}
