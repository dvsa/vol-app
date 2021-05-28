<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Application\View\Model;

use Laminas\View\Model\ViewModel;
use InvalidArgumentException;

/**
 * @see JourneyProgressDescriptionViewModelTest
 */
class JourneyProgressDescriptionViewModel extends ViewModel
{
    /**
     * @var string
     */
    protected $template = 'partials/translated-text';

    /**
     * @param string $currentSectionId
     * @param array $sections
     */
    public function __construct(string $currentSectionId, array $sections)
    {
        $currentSectionIndex = array_search($currentSectionId, array_keys($sections));
        if ($currentSectionIndex === false) {
            throw new InvalidArgumentException('Current section id must reference a section in the sections provided');
        }
        parent::__construct(['text' => 'application.steps', 'data' => [$currentSectionIndex + 1, count($sections)]], []);
    }
}
