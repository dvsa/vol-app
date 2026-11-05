<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Template;

use Twig\Extension\AbstractExtension;

/**
 * Adds nothing. Twig's compiled class names ignore autoescape but include the extension list,
 * so this keeps plain text builds apart from HTML-escaped builds of the same template.
 */
final class PlainTextTwigExtension extends AbstractExtension
{
}
