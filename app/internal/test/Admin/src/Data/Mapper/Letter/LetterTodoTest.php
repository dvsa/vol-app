<?php

declare(strict_types=1);

namespace AdminTest\Data\Mapper\Letter;

use Admin\Data\Mapper\Letter\LetterTodo;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @see LetterTodo
 */
final class LetterTodoTest extends MockeryTestCase
{
    public function testMapFromResultReadsRequiresInputFromTheCurrentVersion(): void
    {
        $formData = LetterTodo::mapFromResult([
            'id' => 5,
            'todoKey' => 'FI01',
            'currentVersion' => [
                'description' => ['blocks' => []],
                'helpText' => 'Last three months',
                'requiresInput' => true,
            ],
        ]);

        $this->assertTrue($formData['letterTodo']['requiresInput']);
    }

    public function testMapFromResultDefaultsRequiresInputToFalse(): void
    {
        $formData = LetterTodo::mapFromResult(['id' => 5, 'todoKey' => 'FI01']);

        $this->assertFalse($formData['letterTodo']['requiresInput']);
    }

    public function testMapFromFormCastsRequiresInputToBool(): void
    {
        $commandData = LetterTodo::mapFromForm([
            'letterTodo' => [
                'id' => 5,
                'todoKey' => 'FI01',
                'requiresInput' => '1',
            ],
        ]);

        $this->assertTrue($commandData['requiresInput']);
    }

    public function testMapFromFormCastsUncheckedRequiresInputToFalse(): void
    {
        $commandData = LetterTodo::mapFromForm([
            'letterTodo' => [
                'id' => 5,
                'todoKey' => 'FI01',
                'requiresInput' => '0',
            ],
        ]);

        $this->assertFalse($commandData['requiresInput']);
    }
}
