<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\Validation\Validators;

use Dvsa\Olcs\Api\Entity\Doc\DocumentAnalysis;
use Dvsa\Olcs\Api\Entity\Licence\Licence;

/**
 * Document Analysis Belongs To Licence
 *
 * An analysis has no licence of its own, so this mirrors the licence scope of the
 * DocumentAnalysisList query: the analysis belongs to a licence when the analysed document is
 * linked to that licence, or when the analysis' application (new or variation) is on it.
 */
class DocumentAnalysisBelongsToLicence extends AbstractBelongsToValidator
{
    protected $repo = 'DocumentAnalysis';

    #[\Override]
    public function isValid(object|int|string $entity, object|int|string|null $parent): bool
    {
        if ($parent === null) {
            return false;
        }

        /** @var DocumentAnalysis $analysis */
        $analysis = $this->resolveEntity($entity);
        $licenceId = $this->resolveId($parent);

        foreach ([$this->getRelatedEntity($analysis), $analysis->getApplication()?->getLicence()] as $licence) {
            if ($licence !== null && $this->resolveId($licence) === $licenceId) {
                return true;
            }
        }

        return false;
    }

    /**
     * The analysed document's own licence; the application's licence is checked in isValid().
     *
     * @param DocumentAnalysis $entity
     */
    #[\Override]
    protected function getRelatedEntity(object $entity): ?Licence
    {
        return $entity->getDocument()?->getLicence();
    }
}

