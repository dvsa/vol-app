<?php

namespace Olcs\View\Builder;

use Zend\View\Model\ViewModel;


final class PageLayoutBuilder extends AbstractBuilder
{
    private $wrapped;
    private $pageLayout;

    public function __construct(BuilderInterface $wrapped, $pageLayout)
    {
        $this->wrapped = $wrapped;
        $this->pageLayout = $pageLayout;
    }

    protected function decorateView(ViewModel $view)
    {
        $layout = new ViewModel();
        $layout->setTemplate( $this->pageLayout);

        $layout->addChild($view, 'content');

        return $this->wrapped->buildView($layout);
    }
}