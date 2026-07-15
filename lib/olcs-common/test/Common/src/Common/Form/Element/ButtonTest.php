<?php

declare(strict_types=1);

namespace CommonTest\Form\Element;

use Common\Test\MockeryTestCase;
use Common\Form\Element\Button;
use InvalidArgumentException;
use Common\Form\Element\Attribute\ClassList;
use Mockery;

#[\PHPUnit\Framework\Attributes\CoversClass(\Common\Form\Element\Button::class)]
final class ButtonTest extends MockeryTestCase
{
    protected const string TYPE_ATTRIBUTE = 'type';

    protected const string A_BUTTON_NAME = 'A BUTTON NAME';

    protected const string A_BUTTON_LABEL = 'A BUTTON LABEL';

    protected const string CLASS_ATTRIBUTE = 'class';

    protected const string AN_INVALID_BUTTON_TYPE = 'AN INVALID BUTTON TYPE';

    protected const string INVALID_BUTTON_TYPE_MESSAGE = 'Invalid type';

    protected const array EMPTY_ARRAY = [];

    protected const string AN_INVALID_BUTTON_SIZE = 'AN INVALID BUTTON SIZE';

    protected const string INVALID_BUTTON_SIZE_MESSAGE = 'Invalid button size';

    protected const string AN_INVALID_THEME = 'AN INVALID THEME';

    protected const string INVALID_BUTTON_THEME_MESSAGE = 'Invalid button theme';

    /**
     * @var Button|null
     */
    protected $sut;

    #[\PHPUnit\Framework\Attributes\Test]
    public function constructSetsTypeAttributeToButton(): void
    {
        // Setup
        $this->setUpSut(static::A_BUTTON_NAME, static::A_BUTTON_LABEL);

        // Assert
        $this->assertEquals(Button::BUTTON, $this->sut->getAttribute(static::TYPE_ATTRIBUTE));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function constructSetsClassToClassList(): void
    {
        // Setup
        $this->setUpSut(static::A_BUTTON_NAME, static::A_BUTTON_LABEL);

        // Assert
        $this->assertInstanceOf(ClassList::class, $this->sut->getAttribute(static::CLASS_ATTRIBUTE));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function constructSetsName(): void
    {
        // Setup
        $this->setUpSut(static::A_BUTTON_NAME, static::A_BUTTON_LABEL);

        // Assert
        $this->assertEquals(static::A_BUTTON_NAME, $this->sut->getName());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function constructSetsLabel(): void
    {
        // Setup
        $this->setUpSut(static::A_BUTTON_NAME, static::A_BUTTON_LABEL);

        // Assert
        $this->assertEquals(static::A_BUTTON_LABEL, $this->sut->getLabel());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function constructSetsSizeToLarge(): void
    {
        // Setup
        $this->setUpSut(static::A_BUTTON_NAME, static::A_BUTTON_LABEL);

        // Assert
        $this->assertTrue($this->sut->getAttribute('class')->has(Button::LARGE));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function setAttributeIsCallable(): void
    {
        // Setup
        $this->setUpSut(static::A_BUTTON_NAME, static::A_BUTTON_LABEL);

        // Assert
        $this->assertIsCallable([$this->sut, 'setAttribute']);
    }

    #[\PHPUnit\Framework\Attributes\Depends('setAttributeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function setAttributeThrowsExceptionIfButtonTypeIsInvalid(): void
    {
        // Setup
        $this->setUpSut(static::A_BUTTON_NAME, static::A_BUTTON_LABEL);

        // Expect
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(static::INVALID_BUTTON_TYPE_MESSAGE);

        // Execute
        $this->sut->setAttribute(static::TYPE_ATTRIBUTE, static::AN_INVALID_BUTTON_TYPE);
    }

    public static function validButtonTypesDataProvider(): \Iterator
    {
        yield 'type button' => [Button::BUTTON];
        yield 'type submit' => [Button::SUBMIT];
        yield 'type reset' => [Button::RESET];
    }

    #[\PHPUnit\Framework\Attributes\Depends('setAttributeIsCallable')]
    #[\PHPUnit\Framework\Attributes\DataProvider('validButtonTypesDataProvider')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function setAttributeAcceptsValidButtonTypes(string $buttonType): void
    {
        // Setup
        $this->setUpSut(static::A_BUTTON_NAME, static::A_BUTTON_LABEL);

        // Execute
        $this->sut->setAttribute(static::TYPE_ATTRIBUTE, $buttonType);

        // Assert
        $this->assertTrue(true);
    }

    protected const string A_CLASS = 'A_CLASS';

    protected const string B_CLASS = 'B_CLASS';

    protected const string AB_STRING_CLASS_LIST = self::A_CLASS . ' ' . self::B_CLASS;

    protected const array AB_ARRAY_CLASS_LIST = [self::A_CLASS, self::B_CLASS];

    #[\PHPUnit\Framework\Attributes\Depends('setAttributeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function setAttributeConvertsClassValuesToClassListsWhenSettingAStringClassList(): void
    {
        // Setup
        $this->setUpSut(static::A_BUTTON_NAME, static::A_BUTTON_LABEL);

        // Execute
        $this->sut->setAttribute(static::CLASS_ATTRIBUTE, static::AB_STRING_CLASS_LIST);
        $result = $this->sut->getAttribute('class');

        // Assert
        $this->assertInstanceOf(ClassList::class, $result);
        $this->assertEquals(ClassList::fromString(static::AB_STRING_CLASS_LIST), $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('setAttributeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function setAttributeConvertsClassValuesToClassListsWhenSettingAnArrayClassList(): void
    {
        // Setup
        $this->setUpSut(static::A_BUTTON_NAME, static::A_BUTTON_LABEL);

        // Execute
        $this->sut->setAttribute(static::CLASS_ATTRIBUTE, static::AB_ARRAY_CLASS_LIST);
        $result = $this->sut->getAttribute('class');

        // Assert
        $this->assertInstanceOf(ClassList::class, $result);
        $this->assertEquals(new ClassList(static::AB_ARRAY_CLASS_LIST), $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('setAttributeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function setAttributeKeepsClassListsWhenSettingAClassList(): void
    {
        // Setup
        $this->setUpSut(static::A_BUTTON_NAME, static::A_BUTTON_LABEL);

        // Execute
        $this->sut->setAttribute(static::CLASS_ATTRIBUTE, $classList = ClassList::fromString(static::AB_STRING_CLASS_LIST));
        $result = $this->sut->getAttribute(static::CLASS_ATTRIBUTE);

            // Assert
        $this->assertSame($classList, $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function setSizeIsCallable(): void
    {
        // Setup
        $this->setUpSut(static::A_BUTTON_NAME, static::A_BUTTON_LABEL);

        // Assert
        $this->assertIsCallable([$this->sut, 'setSize']);
    }

    #[\PHPUnit\Framework\Attributes\Depends('setSizeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function setSizeThrowsExceptionIfSizeValueIsInvalid(): void
    {
        // Setup
        $this->setUpSut(static::A_BUTTON_NAME, static::A_BUTTON_LABEL);

        // Expect
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(static::INVALID_BUTTON_SIZE_MESSAGE);

        // Execute
        $this->sut->setSize(static::AN_INVALID_BUTTON_SIZE);
    }

    public static function validButtonSizeDataProvider(): \Iterator
    {
        yield 'large size' => [Button::LARGE];
    }

    #[\PHPUnit\Framework\Attributes\Depends('setSizeIsCallable')]
    #[\PHPUnit\Framework\Attributes\DataProvider('validButtonSizeDataProvider')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function setSizeAcceptsValidSizes(string $val): void
    {
        // Setup
        $this->setUpSut(static::A_BUTTON_NAME, static::A_BUTTON_LABEL);

        // Execute
        $this->sut->setSize($val);

        // Assert
        $this->assertTrue(true);
    }

    #[\PHPUnit\Framework\Attributes\Depends('setSizeAcceptsValidSizes')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function setSizeSetsSizeAsClass(): void
    {
        // Setup
        $this->setUpSut(static::A_BUTTON_NAME, static::A_BUTTON_LABEL);

        // Execute
        $this->sut->setSize(Button::LARGE);

        // Assert
        $this->assertTrue($this->sut->getAttribute('class')->has(Button::LARGE));
    }

    #[\PHPUnit\Framework\Attributes\Depends('setSizeAcceptsValidSizes')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function setSizeRemovesAnyExistingSizeClassesFromClassList(): void
    {
        // Setup
        $this->setUpSut(static::A_BUTTON_NAME, static::A_BUTTON_LABEL);
        $mockClassList = Mockery::mock(ClassList::class)->makePartial();
        $this->sut->setAttribute('class', $mockClassList);

        // Expect
        $mockClassList->expects('remove')->with(Button::SIZES);

        // Execute
        $this->sut->setSize(Button::LARGE);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function setThemeIsCallable(): void
    {
        // Setup
        $this->setUpSut(static::A_BUTTON_NAME, static::A_BUTTON_LABEL);

        // Assert
        $this->assertIsCallable([$this->sut, 'setTheme']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function setThemeThrowsExceptionIfThemeIsInvalid(): void
    {
        // Setup
        $this->setUpSut(static::A_BUTTON_NAME, static::A_BUTTON_LABEL);

        // Expect
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(static::INVALID_BUTTON_THEME_MESSAGE);

        // Execute
        $this->sut->setTheme(static::AN_INVALID_THEME);
    }

    /**
     * @return \Iterator<(int | string), array<mixed>>
     */
    public static function validThemesDataProvider(): \Iterator
    {
        yield 'primary theme' => [Button::PRIMARY];
        yield 'tertiary theme' => [Button::TERTIARY];
    }

    #[\PHPUnit\Framework\Attributes\Depends('setThemeIsCallable')]
    #[\PHPUnit\Framework\Attributes\DataProvider('validThemesDataProvider')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function setThemeAcceptsValidThemes(string $theme): void
    {
        // Setup
        $this->setUpSut(static::A_BUTTON_NAME, static::A_BUTTON_LABEL);

        // Execute
        $this->sut->setTheme($theme);

        // Assert
        $this->assertTrue($this->sut->getAttribute('class')->has($theme));
    }

    #[\PHPUnit\Framework\Attributes\Depends('setThemeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function setThemeRemovesAnyExistingThemes(): void
    {
        // Setup
        $this->setUpSut(static::A_BUTTON_NAME, static::A_BUTTON_LABEL);
        $mockClassList = Mockery::mock(ClassList::class)->makePartial();
        $this->sut->setAttribute('class', $mockClassList);

        // Expect
        $mockClassList->expects('remove')->with(Button::THEMES);

        // Execute
        $this->sut->setTheme(Button::PRIMARY);
    }

    protected function setUpSut(...$args): void
    {
        $this->sut = new Button(...$args);
    }
}
