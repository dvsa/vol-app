<?php

namespace Dvsa\Olcs\Api\Service\Submission\Sections;

use Dvsa\Olcs\Api\Domain\QueryHandlerManager;
use Dvsa\Olcs\Api\Domain\Repository\RepositoryInterface;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Entity\Person\Person;
use Laminas\View\Renderer\PhpRenderer;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;

/**
 * Class AbstractSection
 * @package Dvsa\Olcs\Api\Service\Submission\Section
 */
abstract class AbstractSection implements SectionGeneratorInterface
{
    private $repos = [];

    /**
     * @return array
     */
    public function getRepos(): array
    {
        return $this->repos;
    }

    public function setRepos(array $repos): void
    {
        $this->repos = $repos;
    }


    /**
     * @return RepositoryInterface
     */
    public function getRepo($name)
    {
        if (!array_key_exists($name, $this->repos)) {
            throw new RuntimeException('You have not injected the ' . $name . ' repository in this section');
        }

        return $this->repos[$name];
    }

    public function __construct(
        private readonly QueryHandlerManager $queryHandler,
        private readonly PhpRenderer $viewRenderer
    ) {
    }

    protected function handleQuery($query)
    {
        return $this->queryHandler->handleQuery($query);
    }

    /**
     * Extract personData if exists, used by child classes
     * @param $contactDetails
     * @return array
     */
    protected function extractPerson($contactDetails = null)
    {
        $personData = [
            'title' => '',
            'forename' => '',
            'familyName' => '',
            'birthDate' => '',
            'birthPlace' => ''
        ];

        if ($contactDetails instanceof ContactDetails && ($contactDetails->getPerson() instanceof Person)) {
            $person = $contactDetails->getPerson();

            $personData = [
                'title' => !empty($person->getTitle()) ? $person->getTitle()->getDescription() : '',
                'forename' => $person->getForename(),
                'familyName' => $person->getFamilyName(),
                'birthDate' => $this->formatDate($person->getBirthDate()),
                'birthPlace' => $person->getBirthPlace()
            ];
        }
        return $personData;
    }

    /**
     * Format a date
     *
     * @param null|mixed $datetime
     * @return string
     */
    protected function formatDate($datetime = null)
    {
        if (!empty($datetime)) {
            if (is_string($datetime)) {
                $datetime = new \DateTime($datetime);
            }

            return $datetime->format('d/m/Y');
        }

        return '';
    }

    /**
     * @return mixed
     */
    public function getViewRenderer()
    {
        return $this->viewRenderer;
    }
}
