<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\EditorJs\BlockRenderer;

use Setono\EditorJS\Block\Block;
use Setono\EditorJS\Block\ParagraphBlock;
use Setono\EditorJS\BlockRenderer\BlockRendererInterface;
use Setono\EditorJS\Exception\UnsupportedBlockException;
use Setono\HtmlElement\HtmlElement;

/** Renders body text as govuk-body. */
final class GovukParagraphBlockRenderer implements BlockRendererInterface
{
    private const BODY_CLASS = 'govuk-body';

    public function render(Block $block): HtmlElement|string
    {
        UnsupportedBlockException::assert($this->supports($block), $block, $this);

        return HtmlElement::p($block->text)->withClass(self::BODY_CLASS);
    }

    /**
     * @phpstan-assert-if-true ParagraphBlock $block
     */
    public function supports(Block $block): bool
    {
        return $block instanceof ParagraphBlock;
    }
}
