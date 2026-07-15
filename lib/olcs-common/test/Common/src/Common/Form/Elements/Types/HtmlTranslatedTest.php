<?php

/**
 * HtmlTranslatedTest
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

declare(strict_types=1);

namespace CommonTest\Form\Elements\Types;

use Common\Form\Elements\Types\HtmlTranslated;

/**
 * HtmlTranslatedTest
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class HtmlTranslatedTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Placeholder
     *
     */
    public function testElement(): void
    {
        $element = new HtmlTranslated();
        $this->assertInstanceOf(\Common\Form\Elements\Types\HtmlTranslated::class, $element);
    }
}
