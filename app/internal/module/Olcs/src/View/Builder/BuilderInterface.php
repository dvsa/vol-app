<?php

namespace Olcs\View\Builder;

use Zend\View\Model\ViewModel;

interface BuilderInterface
{
    /**
     * allow for very simple views to be passed as a string. Obviously this
     * precludes the passing of any template variables but can still come
     * in handy when no extra variables need to be set
     */
    public function buildViewFromTemplate($template);

    public function buildView(ViewModel $view);
}