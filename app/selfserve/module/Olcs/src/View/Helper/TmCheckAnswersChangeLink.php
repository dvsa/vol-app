<?php

namespace Olcs\View\Helper;

use Zend\View\Helper\AbstractHelper;

class TmCheckAnswersChangeLink extends AbstractHelper
{
    public function __invoke($view, $section = '')
    {
        $url = $view->url('lva-variation', ['application' => '1000000']);
        return "Qualcosa";
    }
}
