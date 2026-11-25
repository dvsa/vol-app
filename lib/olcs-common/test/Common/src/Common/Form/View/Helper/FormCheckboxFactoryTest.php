<?php

declare(strict_types=1);

namespace CommonTest\Form\View\Helper;

use Common\Form\View\Helper\FormCheckbox;
use Common\Form\View\Helper\FormCheckboxFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

final class FormCheckboxFactoryTest extends TestCase
{
    public function testInvoke(): void
    {
        $container = $this->createStub(ContainerInterface::class);
        $factory = new FormCheckboxFactory();

        self::assertInstanceOf(FormCheckbox::class, $factory($container, FormCheckbox::class));
    }
}
