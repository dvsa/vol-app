<?php

namespace Olcs\View\Builder;

use Laminas\View\Model\ViewModel;

/**
 * Class AbstractBuilder
 * @package Olcs\View\Builder
 */
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

    /**
     * @param ViewModel $view
     * @return ViewModel
     */
    public function buildView(ViewModel $view)
    {
        return $this->decorateView($view);
    }

    /**
     * @return ViewModel
     */
    abstract protected function decorateView(ViewModel $view);
}
