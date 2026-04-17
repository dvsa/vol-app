<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\Validation\Validators;

use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Transfer\Query;

class CanAccessDocument extends AbstractCanAccessEntity
{
    protected $repo = Repository\Document::class;

    public const PRINTABLE_LICENCE_DESCRIPTIONS = [
        'GV Licence',
        'GV Licence LGV Only',
        'PSV Licence',
        'PSV-SR Licence'
    ];

    /**
     * @throws NotFoundException
     */
    #[\Override]
    public function isValid($entityId): bool
    {
        if ($this->getUser()->hasRoles([Entity\User\Role::ROLE_OPERATOR_TM])) {
            return $this->canTransportManagerAccessDocument($entityId);
        }

        if ($this->getUser()->hasRoles([Entity\User\Role::ROLE_LOCAL_AUTHORITY_USER, Entity\User\Role::ROLE_LOCAL_AUTHORITY_ADMIN])) {
            return $this->canLocalAuthorityAccessDocument($entityId);
        }

        if ($this->isExternalUser() && !$this->canExternalUserAccessDocument($entityId)) {
            return false;
        }

        return parent::isValid($entityId);
    }

    private function canLocalAuthorityAccessDocument(string $documentId): bool
    {
        $txcInboxRepo = $this->getRepo(Repository\TxcInbox::class);
        $txcEntities = $txcInboxRepo->fetchLinkedToDocument($documentId);

        if (!is_array($txcEntities) || count($txcEntities) === 0) {
            return false;
        }

        $localAuthorityUser = $this->getCurrentLocalAuthority();

        foreach ($txcEntities as $txcEntity) {
            $txcLocalAuthority = $txcEntity->getLocalAuthority();

            if ($txcLocalAuthority instanceof Entity\Bus\LocalAuthority && $txcLocalAuthority->getId() === $localAuthorityUser->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @throws NotFoundException
     */
    private function canExternalUserAccessDocument(string $documentId): bool
    {
        return $this->checkDocumentWasExternallyUploaded($documentId)
            || $this->checkDocumentInCorrespondence($documentId)
            || $this->isGVorPSVLicencePrintDocument($documentId)
            || $this->checkIsTxcDocument($documentId);
    }

    /**
     * Checks if the cat/sub-cat and description match those expected for a printable licence document linked from SS
     *
     * @throws NotFoundException
     */
    private function isGVorPSVLicencePrintDocument(string $documentId)
    {
        $document = $this->getRepo(Repository\Document::class)->fetchById($documentId);

        if ($document === null) {
            return false;
        }

        $category = $document->getCategory();
        $subCategory = $document->getSubCategory();

        $correctCat = $category !== null && $category->getId() === Entity\System\Category::CATEGORY_LICENSING;
        $correctSubCat = $subCategory !== null && $subCategory->getId() === Entity\System\SubCategory::DOC_SUB_CATEGORY_LICENCING_OTHER_DOCUMENTS;
        $correctDescr = in_array($document->getDescription(), self::PRINTABLE_LICENCE_DESCRIPTIONS);

        return $correctCat && $correctSubCat && $correctDescr;
    }

    private function checkDocumentInCorrespondence(string $documentId): bool
    {
        $currentUserOrganisationId = $this->getCurrentOrganisation() ? $this->getCurrentOrganisation()->getId() : null;
        if ($currentUserOrganisationId === null) {
            return false;
        }

        $query = Query\Correspondence\Correspondences::create([
            'organisation' => $currentUserOrganisationId,
        ]);

        $correspondences = $this->getRepo(Repository\Correspondence::class)->fetchList($query);
        $correspondencesDocumentIds = array_map(fn($element) => $element['document']['id'] ?? null, iterator_to_array($correspondences));

        return in_array($documentId, $correspondencesDocumentIds);
    }

    /**
     * @throws NotFoundException
     */
    private function checkDocumentWasExternallyUploaded(string $documentId): bool
    {
        $document = $this->getRepo(Repository\Document::class)->fetchById($documentId);
        return $document->getIsExternal();
    }

    private function checkIsTxcDocument(string $documentId): bool
    {
        $txcInboxRepo = $this->getRepo(Repository\TxcInbox::class);
        $txcEntities = $txcInboxRepo->fetchLinkedToDocument($documentId);

        return is_array($txcEntities) && count($txcEntities) > 0;
    }

    /**
     * @throws NotFoundException
     */
    private function canTransportManagerAccessDocument(string $documentId): bool
    {
        $document = $this->getRepo(Repository\Document::class)->fetchById($documentId);
        return $document->getCreatedBy()->getId() === $this->getCurrentUser()->getId();
    }
}
