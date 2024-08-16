<?php

/**
 *
 * View Model Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace OlcsTest\View\Model;

use Olcs\View\Model\ViewModel;

/**
 * View Model Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ViewModelTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ViewModel
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new ViewModel();
    }

    public function testSimpleSetters()
    {
        $this->sut->setHorizontalNavigationId('navId');
        $this->sut->setContentTitle('Some title');
        $this->sut->setPageTitle('Some page title');
        $this->sut->setPageSubTitle('Some page sub title');

        $this->assertEquals('navId', $this->sut->horizontalNavigationId);
        $this->assertEquals('Some title', $this->sut->contentTitle);
        $this->assertEquals('Some page title', $this->sut->pageTitle);
        $this->assertEquals('Some page sub title', $this->sut->pageSubTitle);
    }

    public function testSetIsAjax()
    {
        $this->assertEquals('layout/base', $this->sut->getTemplate());

        $this->sut->setIsAjax(true);

        $this->assertEquals('layout/ajax', $this->sut->getTemplate());

        $this->sut->setIsAjax(false);

        $this->assertEquals('layout/base', $this->sut->getTemplate());
    }

    public function testSetContent()
    {
        $content = new \Laminas\View\Model\ViewModel();

        $this->sut->setContent($content);

        $contents = $this->sut->getChildrenByCaptureTo('content');

        $this->assertSame($content, $contents[0]);
    }

    public function testSetLeft()
    {
        $content = new \Laminas\View\Model\ViewModel();

        $this->sut->setLeft($content);

        $contents = $this->sut->getChildrenByCaptureTo('left');

        $this->assertSame($content, $contents[0]);
    }

    public function testSetRight()
    {
        $content = new \Laminas\View\Model\ViewModel();

        $this->sut->setRight($content);

        $contents = $this->sut->getChildrenByCaptureTo('right');

        $this->assertSame($content, $contents[0]);
    }

    public function testClearLeft()
    {
        $content = new \Laminas\View\Model\ViewModel();

        $this->sut->setLeft($content);

        $contents = $this->sut->getChildrenByCaptureTo('left');

        $this->assertSame($content, $contents[0]);

        $this->sut->clearLeft();

        $contents = $this->sut->getChildrenByCaptureTo('left');

        $this->assertEmpty($contents);
    }
}
