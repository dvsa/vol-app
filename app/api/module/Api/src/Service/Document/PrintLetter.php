<?php

namespace Dvsa\Olcs\Api\Service\Document;

use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Utils\Helper\ValueHelper;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * @author Dmitry Golubev <dmitrij.golubev@valtech.com>
 */
class PrintLetter implements FactoryInterface
{
    /** @var Repository\DocTemplate */
    private $repoDocTemplate;

    /**
     * Check if we CAN email the document to the operator
     *
     * @param Entity\Doc\Document $document Document entity
     *
     * @return bool
     */
    public function canEmail(Entity\Doc\Document $document, $forceCorrespondence = false)
    {
        $licence = $document->getRelatedLicence();
        if ($licence === null) {
            return false;
        }

        // if the allow email preference is off and this correspondence isnt being force sent
        $org = $licence->getOrganisation();
        if (
            !ValueHelper::isOn($org->getAllowEmail())
            && $forceCorrespondence === false
        ) {
            return false;
        }

        // Cant send email if there are no email addresses attached to the org
        if (!$org->hasAdminEmailAddresses()) {
            return false;
        }

        $metadata = json_decode($document->getMetadata(), true);
        if (!isset($metadata['details']['documentTemplate'])) {
            return false;
        }

        $templateId = $metadata['details']['documentTemplate'];

        /** @var Entity\Doc\DocTemplate $template */
        $template = $this->repoDocTemplate->fetchById($templateId);

        return (
            $template !== null
            && ValueHelper::isOn($template->getSuppressFromOp()) === false
        );
    }

    /**
     * Can Print
     *
     * @param Entity\Doc\Document $doc Document entity
     *
     * @return bool
     */
    public function canPrint(Entity\Doc\Document $doc)
    {
        $licence = $doc->getRelatedLicence();

        return (
            $licence === null
            || ValueHelper::isOn($licence->getTranslateToWelsh()) === false
        );
    }

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $this->repoDocTemplate = $container->get('RepositoryServiceManager')->get('DocTemplate');
        return $this;
    }
}
