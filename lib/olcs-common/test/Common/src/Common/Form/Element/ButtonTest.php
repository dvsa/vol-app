<?php

declare(strict_types=1);

namespace CommonTest\Form\Element;

use Common\Test\MockeryTestCase;
use Common\Form\Element\Button;
use InvalidArgumentException;
use Common\Form\Element\Attribute\ClassList;
use Mockery;

/**
 * @covers \Common\Form\Element\Button
 */
class ButtonTest extends MockeryTestCase
{
    protected const TYPE_ATTRIBUTE = 'type';

    protected const A_BUTTON_NAME = 'A BUTTON NAME';

    protected const A_BUTTON_LABEL = 'A BUTTON LABEL';

    protected const CLASS_ATTRIBUTE = 'class';

    protected const AN_INVALID_BUTTON_TYPE = 'AN INVALID BUTTON TYPE';

    protected const INVALID_BUTTON_TYPE_MESSAGE = 'Invalid type';

    protected const EMPTY_ARRAY = [];

    protected const AN_INVALID_BUTTON_SIZE = 'AN INVALID BUTTON SIZE';

    protected const INVALID_BUTTON_SIZE_MESSAGE = 'Invalid button size';

    protected const AN_INVALID_THEME = 'AN INVALID THEME';

    protected const INVALID_BUTTON_THEME_MESSAGE = 'Invalid button theme';

    /**
     * @var Button|null
     */
    protected $sut;

    /**
     * @test
     */
    public function constructSetsTypeAttributeToButton(): void
    {
        // Setup
        $this->setUpSut(static::A_BUTTON_NAME, static::A_BUTTON_LABEL);

        // Assert
        $this->assertEquals(Button::BUTTON, $this->sut->getAttribute(static::TYPE_ATTRIBUTE));
    }

    /**
     * @test
     */
    public function constructSetsClassToClassList(): void
    {
        // Setup
        $this->setUpSut(static::A_BUTTON_NAME, static::A_BUTTON_LABEL);

        // Assert
        $this->assertInstanceOf(ClassList::class, $this->sut->getAttribute(static::CLASS_ATTRIBUTE));
    }

    /**
     * @test
     */
    public function constructSetsName(): void
    {
        // Setup
        $this->setUpSut(static::A_BUTTON_NAME, static::A_BUTTON_LABEL);

        // Assert
        $this->assertEquals(static::A_BUTTON_NAME, $this->sut->getName());
    }

    /**
     * @test
     */
    public function constructSetsLabel(): void
    {
        // Setup
        $this->setUpSut(static::A_BUTTON_NAME, static::A_BUTTON_LABEL);

        // Assert
        $this->assertEquals(static::A_BUTTON_LABEL, $this->sut->getLabel());
    }

    /**
     * @test
     */
    public function constructSetsSizeToLarge(): void
    {
        // Setup
        $this->setUpSut(static::A_BUTTON_NAME, static::A_BUTTON_LABEL);

        // Assert
        $this->assertTrue($this->sut->getAttribute('class')->has(Button::LARGE));
    }

    /**
     * @test
     */
    public function setAttributeIsCallable(): void
    {
        // Setup
        $this->setUpSut(static::A_BUTTON_NAME, static::A_BUTTON_LABEL);

        // Assert
        $this->assertIsCallable([$this->sut, 'setAttribute']);
    }

    /**
     * @test
     * @depends setAttributeIsCallable
     */
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

    public function validButtonTypesDataProvider(): array
    {
        return [
            'type button' => [Button::BUTTON],
            'type submit' => [Button::SUBMIT],
            'type reset' => [Button::RESET],
        ];
    }

    /**
     * @test
     * @depends setAttributeIsCallable
     * @dataProvider validButtonTypesDataProvider
     */
    public function setAttributeAcceptsValidButtonTypes(string $buttonType): void
    {
        // Setup
        $this->setUpSut(static::A_BUTTON_NAME, static::A_BUTTON_LABEL);

        // Execute
        $this->sut->setAttribute(static::TYPE_ATTRIBUTE, $buttonType);

        // Assert
        $this->assertTrue(true);
    }

    protected const A_CLASS = 'A_CLASS';

    protected const B_CLASS = 'B_CLASS';

    protected const AB_STRING_CLASS_LIST = self::A_CLASS . ' ' . self::B_CLASS;

    protected const AB_ARRAY_CLASS_LIST = [self::A_CLASS, self::B_CLASS];

    /**
     * @test
     * @depends setAttributeIsCallable
     */
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

    /**
     * @test
     * @depends setAttributeIsCallable
     */
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

    /**
     * @test
     * @depends setAttributeIsCallable
     */
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

    /**
     * @test
     */
    public function setSizeIsCallable(): void
    {
        // Setup
        $this->setUpSut(static::A_BUTTON_NAME, static::A_BUTTON_LABEL);

        // Assert
        $this->assertIsCallable([$this->sut, 'setSize']);
    }

    /**
     * @test
     * @depends setSizeIsCallable
     */
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

    public function validButtonSizeDataProvider(): array
    {
        return [
            'large size' => [Button::LARGE],
        ];
    }

    /**
     * @test
     * @depends setSizeIsCallable
     * @dataProvider validButtonSizeDataProvider
     */
    public function setSizeAcceptsValidSizes(string $val): void
    {
        // Setup
        $this->setUpSut(static::A_BUTTON_NAME, static::A_BUTTON_LABEL);

        // Execute
        $this->sut->setSize($val);

        // Assert
        $this->assertTrue(true);
    }

    /**
     * @test
     * @depends setSizeAcceptsValidSizes
     */
    public function setSizeSetsSizeAsClass(): void
    {
        // Setup
        $this->setUpSut(static::A_BUTTON_NAME, static::A_BUTTON_LABEL);

        // Execute
        $this->sut->setSize(Button::LARGE);

        // Assert
        $this->assertTrue($this->sut->getAttribute('class')->has(Button::LARGE));
    }

    /**
     * @test
     * @depends setSizeAcceptsValidSizes
     */
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

    /**
     * @test
     */
    public function setThemeIsCallable(): void
    {
        // Setup
        $this->setUpSut(static::A_BUTTON_NAME, static::A_BUTTON_LABEL);

        // Assert
        $this->assertIsCallable([$this->sut, 'setTheme']);
    }

    /**
     * @test
     */
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
     * @return array[]
     */
    public function validThemesDataProvider(): array
    {
        return [
            'primary theme' => [Button::PRIMARY],
            'tertiary theme' => [Button::TERTIARY],
        ];
    }

    /**
     * @test
     * @depends setThemeIsCallable
     * @dataProvider validThemesDataProvider
     */
    public function setThemeAcceptsValidThemes(string $theme): void
    {
        // Setup
        $this->setUpSut(static::A_BUTTON_NAME, static::A_BUTTON_LABEL);

        // Execute
        $this->sut->setTheme($theme);

        // Assert
        $this->assertTrue($this->sut->getAttribute('class')->has($theme));
    }

    /**
     * @test
     * @depends setThemeIsCallable
     */
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
