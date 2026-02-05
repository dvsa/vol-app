<?php

declare(strict_types=1);

namespace Olcs\View\Model\Element;

use Laminas\View\Model\ViewModel;
use InvalidArgumentException;

/**
 * @see \OlcsTest\View\Model\Element\AnchorViewModelTest
 */
class AnchorViewModel extends ViewModel
{
    public const DEFAULT_CLASS = 'govuk-link';
    public const DEFAULT_TEMPLATE = 'element/anchor';
    public const URL_VARIABLE = 'url';
    public const ROUTE_VARIABLE = 'route';
    public const CLASS_VARIABLE = 'class';

    /**
     * @inheritDoc
     */
    public function __construct($variables = null, $options = null)
    {
        $variables[static::CLASS_VARIABLE] ??= static::DEFAULT_CLASS;
        parent::__construct($variables, $options);
        $this->setTemplate(static::DEFAULT_TEMPLATE);
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function setVariable($name, $value)
    {
        $currentVariables = $this->getVariables();

        if ($name === static::ROUTE_VARIABLE && isset($currentVariables[static::URL_VARIABLE])) {
            throw new InvalidArgumentException('Unable to set "' . static::ROUTE_VARIABLE . '" while "' . static::URL_VARIABLE . '" is set');
        }

        if ($name === static::URL_VARIABLE && isset($currentVariables[static::ROUTE_VARIABLE])) {
            throw new InvalidArgumentException('Unable to set "' . static::URL_VARIABLE . '" while "' . static::ROUTE_VARIABLE . '" is set');
        }

        return parent::setVariable($name, $value);
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function setVariables($variables, $overwrite = false)
    {
        if (isset($variables[static::URL_VARIABLE]) && isset($variables[static::ROUTE_VARIABLE])) {
            throw new InvalidArgumentException('Expected "' . static::URL_VARIABLE . '" variable or "' . static::ROUTE_VARIABLE . '" variable but received both');
        }

        return parent::setVariables($variables, $overwrite);
    }
}
