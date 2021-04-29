<?php

declare(strict_types=1);

namespace Olcs\View\Model;

use Laminas\View\Model\ViewModel;

class AnchorViewModel extends ViewModel
{
    /**
     * @inheritDoc
     */
    public function __construct($variables = null, $options = null)
    {
        assert(! (isset($variables['url']) && isset($variables['route'])), 'Expected "url" variable or "route" variable but received both');

        // Set default class
        $variables['class'] = $variables['class'] ?? 'govuk-link';

        parent::__construct($variables, $options);
        $this->setTemplate('elements/anchor');
    }
}
