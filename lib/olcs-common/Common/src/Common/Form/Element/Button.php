<?php

declare(strict_types=1);

namespace Common\Form\Element;

use Common\Form\Element\Attribute\ClassList;
use InvalidArgumentException;

/**
 * A form button element.
 *
 * @see \CommonTest\Form\Element\ButtonTest
 */
class Button extends \Laminas\Form\Element\Button
{
    public const PRIMARY = 'govuk-button';

    public const SECONDARY = 'govuk-button govuk-button--secondary';

    public const TERTIARY = 'govuk-button govuk-button--secondary';

    public const THEMES = [self::PRIMARY, self::SECONDARY, self::TERTIARY];

    public const LARGE = 'large';

    public const SIZES = [self::LARGE];

    public const RESET = 'reset';

    public const SUBMIT = 'submit';

    public const BUTTON = 'button';

    public const TYPES = [self::BUTTON, self::SUBMIT, self::RESET];

    /**
     * @inheritDoc
     */
    protected $attributes = [
        'type' => 'button',
        'data-module' => 'govuk-button',
        'allowWrap' => false,
    ];

    public function __construct(string $name, string $label)
    {
        parent::__construct($name, []);
        $this->setLabel($label);
        $this->setAttribute('class', new ClassList());
        $this->setSize(static::LARGE);
    }

    /**
     * @inheritDoc
     *
     * @psalm-param 'class'|'type' $key
     */
    #[\Override]
    public function setAttribute($key, $value)
    {
        if ($key === 'type' && !in_array($value, static::TYPES)) {
            throw new InvalidArgumentException('Invalid type');
        }

        if ($key === 'class' && !($value instanceof ClassList)) {
            if (is_string($value)) {
                $value = ClassList::fromString($value);
            } else {
                $value = new ClassList($value);
            }
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * @return $this
     */
    public function setTheme(string $theme): self
    {
        if (! in_array($theme, static::THEMES)) {
            throw new InvalidArgumentException('Invalid button theme');
        }

        $classList = $this->getAttribute('class');
        assert($classList instanceof ClassList);
        $classList->remove(static::THEMES);
        $classList->add($theme);
        return $this;
    }

    public function setSize(string $size): self
    {
        if (! in_array($size, static::SIZES)) {
            throw new InvalidArgumentException('Invalid button size');
        }

        $classList = $this->getAttribute('class');
        assert($classList instanceof ClassList);
        $classList->remove(static::SIZES);
        $classList->add($size);
        return $this;
    }
}
