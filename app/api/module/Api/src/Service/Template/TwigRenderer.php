<?php

namespace Dvsa\Olcs\Api\Service\Template;

use Twig\Environment;

class TwigRenderer
{
    public function __construct(private readonly Environment $twig)
    {
    }

    /**
     * Render the template with the specified path using the supplied variables
     */
    public function render(string $templatePath, array $variables): string
    {
        return $this->twig->render($templatePath, $variables);
    }

    /**
     * Render the template within the supplied string using the supplied variables
     */
    public function renderString(string $templateString, array $variables): string
    {
        $template = $this->twig->createTemplate($templateString);
        return $this->twig->render($template, $variables);
    }
}
