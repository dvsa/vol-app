<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\EditorJs\BlockRenderer;

use Setono\EditorJS\Block\Block;
use Setono\EditorJS\Block\HeaderBlock;
use Setono\EditorJS\BlockRenderer\BlockRendererInterface;
use Setono\EditorJS\Exception\UnsupportedBlockException;
use Setono\HtmlElement\HtmlElement;

final class GovukHeaderBlockRenderer implements BlockRendererInterface
{
    private const LEVEL_CLASSES = [
        1 => 'govuk-heading-xl',
        2 => 'govuk-heading-l',
        3 => 'govuk-heading-m',
        4 => 'govuk-heading-s',
    ];

    private const FALLBACK_CLASS = 'govuk-heading-l';

    #[\Override]
    public function render(Block $block): HtmlElement|string
    {
        UnsupportedBlockException::assert($this->supports($block), $block, $this);

        return (new HtmlElement($block->getTag(), $block->text))
            ->withClass(self::LEVEL_CLASSES[$block->level] ?? self::FALLBACK_CLASS);
    }

    /**
     * @phpstan-assert-if-true HeaderBlock $block
     */
    #[\Override]
    public function supports(Block $block): bool
    {
        return $block instanceof HeaderBlock;
    }
}
