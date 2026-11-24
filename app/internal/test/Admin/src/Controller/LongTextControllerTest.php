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
        self::assertSame(['markupOnly' => true, 'format' => 'text'], $filters->invoke($controller, true));
    }

    public function testEditableTranslationsListOnlyShowsPlainTextKeys(): void
    {
        $controller = (new \ReflectionClass(EditableTranslationsController::class))->newInstanceWithoutConstructor();
        $filters = new \ReflectionMethod(EditableTranslationsController::class, 'getListFilters');

        self::assertSame(['format' => 'text'], $filters->invoke($controller, false));
    }
}
