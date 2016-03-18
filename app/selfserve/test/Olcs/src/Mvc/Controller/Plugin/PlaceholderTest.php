<?php

/**
 * Placeholder Test
 */
namespace OlcsTest\Mvc\Controller\Plugin;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Mvc\Controller\Plugin\Placeholder;
use Zend\View\Helper\Placeholder as ViewPlaceholder;

/**
 * Placeholder Test
 */
class PlaceholderTest extends MockeryTestCase
{
    /**
     * @var Placeholder
     */
    private $sut;

    /**
     * @var ViewPlaceholder
     */
    private $viewPlaceholder;

    public function setUp()
    {
        $this->viewPlaceholder = new ViewPlaceholder();

        $this->sut = new Placeholder($this->viewPlaceholder);
    }

    public function testSetPlaceholder()
    {
        $this->assertEquals('', (string)$this->viewPlaceholder->getContainer('key'));

        $this->sut->__invoke()->setPlaceholder('key', 'value');

        $this->assertEquals('value', $this->viewPlaceholder->getContainer('key'));
    }
}
