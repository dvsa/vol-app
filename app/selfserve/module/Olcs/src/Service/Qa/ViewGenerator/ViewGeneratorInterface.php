<?php

namespace Olcs\Service\Qa\ViewGenerator;

interface ViewGeneratorInterface
{
    /**
     * Get the template name to be used by this view generator
     *
     * @return string
     */
    public function getTemplateName();

    /**
     * Get an array of additional view variables to be used in the view
     *
     * @param array $result
     *
     * @return array
     */
    public function getAdditionalViewVariables(array $result);
}
