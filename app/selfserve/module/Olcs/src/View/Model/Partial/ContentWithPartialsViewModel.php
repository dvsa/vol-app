<?php

declare(strict_types=1);

namespace Olcs\View\Model\Partial;

use Laminas\View\Model\ViewModel;
use InvalidArgumentException;

/**
 * @see \OlcsTest\View\Model\Element\ContentWithPartialsViewModelTest
 */
class ContentWithPartialsViewModel extends ViewModel
{
    public const CONTENT_VARIABLE = 'content';
    public const PARTIALS_VARIABLE = 'partials';
    public const DEFAULT_TEMPLATE = 'partials/content-with-partials';

    /**
     * @inheritDoc
     */
    public function __construct($variables = null, $options = null)
    {
        $variables[static::CONTENT_VARIABLE] ??= '';
        parent::__construct($variables, $options);
        $this->setTemplate(static::DEFAULT_TEMPLATE);
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function setVariables($variables, $overwrite = false)
    {
        if (!isset($variables[static::PARTIALS_VARIABLE]) || !is_array($variables[static::PARTIALS_VARIABLE]) || empty($variables[static::PARTIALS_VARIABLE])) {
            throw new InvalidArgumentException('Expected at least one partial to be provided');
        }
        return parent::setVariables($variables, $overwrite);
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function setVariable($name, $value)
    {
        if ($name === static::PARTIALS_VARIABLE && !is_array($value) || empty($value)) {
            throw new InvalidArgumentException('Expected at least one partial to be provided');
        }
        return parent::setVariable($name, $value);
    }
}
