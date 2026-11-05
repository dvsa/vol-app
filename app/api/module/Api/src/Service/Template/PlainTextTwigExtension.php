<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Template;

use Twig\Extension\AbstractExtension;

/**
 * Adds nothing. Twig's compiled class names ignore autoescape but include the extension list,
 * so this keeps plain text builds apart from HTML-escaped builds of the same template (and of
 * the same createTemplate() string) once both are loaded in one process. A separate cache
 * directory alone does not do that. PlainTextTwigRendererFactoryTest fails if it stops working.
 */
final class PlainTextTwigExtension extends AbstractExtension
{
}
