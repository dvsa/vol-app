<?php

namespace Dvsa\Olcs\Api\Service\Template;

use Twig\Environment;

class TwigRenderer
{
    /**
     * Create service instance
     *
     *
     * @return TwigRenderer
     */
    public function __construct(private readonly Environment $twig)
    {
    }

    /**
     * Render the template with the specified path using the supplied variables
     *
     * @param string $templatePath
     *
     * @return string
     */
    public function render($templatePath, array $variables)
    {
        return $this->twig->render($templatePath, $variables);
    }

    /**
     * Render the template within the supplied string using the supplied variables
     *
     * @param string $templateString
     *
     * @return string
     */
    public function renderString($templateString, array $variables)
    {
        $template = $this->twig->createTemplate($templateString);
        return $template->render($variables);
    }
}
