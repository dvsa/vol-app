<?php

declare(strict_types=1);

namespace AdminTest\Controller;

use Admin\Controller\LongTextController;
use Admin\Controller\EditableTranslationsController;
use PHPUnit\Framework\TestCase;

final class LongTextControllerTest extends TestCase
{
    public function testListIncludesEveryRichKeyAndPickerFindsMarkupKeys(): void
    {
        self::assertTrue(is_subclass_of(LongTextController::class, EditableTranslationsController::class));

        $controller = (new \ReflectionClass(LongTextController::class))->newInstanceWithoutConstructor();
        $filters = new \ReflectionMethod(LongTextController::class, 'getListFilters');

        self::assertSame(['format' => 'editorjs'], $filters->invoke($controller, false));
        self::assertSame(['markupOnly' => true], $filters->invoke($controller, true));
    }
}
