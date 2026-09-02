<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\EditorJs\BlockRenderer;

use Setono\EditorJS\Block\Block;
use Setono\EditorJS\Block\ListBlock;
use Setono\EditorJS\BlockRenderer\BlockRendererInterface;
use Setono\EditorJS\Exception\UnsupportedBlockException;
use Setono\HtmlElement\HtmlElement;

/** The GOV.UK modifier is named for the marker (bullet/number) while the parser models the list semantically (uno */
final class GovukListBlockRenderer implements BlockRendererInterface
{
    public function render(Block $block): HtmlElement|string
    {
        UnsupportedBlockException::assert($this->supports($block), $block, $this);

        $modifier = $block->tag === 'ol' ? 'number' : 'bullet';

        return (new HtmlElement($block->tag, ...array_map(
            static fn (string $item) => HtmlElement::li($item),
            $block->items,
        )))->withClass(sprintf('govuk-list govuk-list--%s', $modifier));
    }

    /**
     * @phpstan-assert-if-true ListBlock $block
     */
    public function supports(Block $block): bool
    {
        return $block instanceof ListBlock;
    }
}
