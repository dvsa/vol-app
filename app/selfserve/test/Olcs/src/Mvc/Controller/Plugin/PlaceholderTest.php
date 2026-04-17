<?php

declare(strict_types=1);

/**
 * Placeholder Test
 */

namespace OlcsTest\Mvc\Controller\Plugin;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Mvc\Controller\Plugin\Placeholder;
use Laminas\View\Helper\Placeholder as ViewPlaceholder;

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

    public function setUp(): void
    {
        $this->viewPlaceholder = new ViewPlaceholder();

        $this->sut = new Placeholder($this->viewPlaceholder);
    }

    public function testSetPlaceholder(): void
    {
        $this->assertEquals('', (string)$this->viewPlaceholder->getContainer('key'));

        $this->sut->__invoke()->setPlaceholder('key', 'value');

        $this->assertEquals('value', $this->viewPlaceholder->getContainer('key'));
    }
}
