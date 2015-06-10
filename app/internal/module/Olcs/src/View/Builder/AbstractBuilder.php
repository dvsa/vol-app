<?php

namespace Olcs\View\Builder;

use Zend\View\Model\ViewModel;

abstract class AbstractBuilder implements BuilderInterface
{
    /**
     * allow for very simple views to be passed as a string. Obviously this
     * precludes the passing of any template variables but can still come
     * in handy when no extra variables need to be set
     */
    public function buildViewFromTemplate($template)
    {
        $view = new ViewModel();
        $view->setTemplate($template);
        return $this->decorateView($view);
    }

    public function buildView(ViewModel $view)
    {
        // no, I don't know why it's not getTerminal or isTerminal either...
        if ($view->terminate()) {
            return $view;
        }

        return $this->decorateView($view);
    }

    abstract protected function decorateView(ViewModel $view);
}