<?php

/**
 * View Model
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\View\Model;

use Zend\View\Model\ViewModel as ZendViewModel;

/**
 * View Model
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ViewModel extends ZendViewModel
{
    protected $terminate = true;

    protected $template = 'layout/base';

    protected $variables = [
        'contentTitle' => null,
        'pageTitle' => null,
        'pageSubTitle' => null,
        'horizontalNavigationId' => null
    ];

    public function setHorizontalNavigationId($id)
    {
        $this->horizontalNavigationId = $id;
    }

    public function setContent(ZendViewModel $content)
    {
        $this->addChild($content, 'content');
    }

    public function setRight(ZendViewModel $right)
    {
        $this->addChild($right, 'right');
    }

    public function setLeft(ZendViewModel $left)
    {
        $this->addChild($left, 'left');
    }

    public function clearLeft()
    {
        $children = $this->getChildrenByCaptureTo('left');

        /** @var ZendViewModel $child */
        foreach ($children as $child) {
            $child->setCaptureTo('void');
        }
    }

    public function setContentTitle($title)
    {
        $this->contentTitle = $title;
    }

    public function setPageTitle($title)
    {
        $this->pageTitle = $title;
    }

    public function setPageSubTitle($subTitle)
    {
        $this->pageSubTitle = $subTitle;
    }

    public function setIsAjax($boolean)
    {
        $this->template = 'layout/' . ($boolean ? 'ajax' : 'base');
    }
}
