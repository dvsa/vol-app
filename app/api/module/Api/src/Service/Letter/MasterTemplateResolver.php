<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Letter;

use Dvsa\Olcs\Api\Domain\Repository\MasterTemplate as MasterTemplateRepo;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstance;
use Dvsa\Olcs\Api\Entity\Letter\MasterTemplate;

/**
 * Picks the right MasterTemplate row for a given letter instance.
 *
 * Each LetterType points at a "base" MasterTemplate; sibling rows share that base's
 * name but differ by locale (en_GB, en_NI, cy_GB, future customN_*). The resolver
 * computes a preferred locale from the letter's context (currently driven only by
 * isNi) and returns the matching sibling — falling back to the base if no exact
 * match exists.
 *
 * VOL-7305.
 */
class MasterTemplateResolver
{
    public function __construct(
        private readonly MasterTemplateRepo $masterTemplateRepo
    ) {
    }

    /**
     * @param LetterInstance $letterInstance
     * @return MasterTemplate|null
     */
    public function resolve(LetterInstance $letterInstance): ?MasterTemplate
    {
        $base = $letterInstance->getLetterType()?->getMasterTemplate();
        if ($base === null) {
            return null;
        }

        $preferredLocale = $this->preferredLocaleFor($letterInstance);

        if ($base->getLocale() === $preferredLocale) {
            return $base;
        }

        $sibling = $this->masterTemplateRepo->findByNameAndLocale($base->getName(), $preferredLocale);
        return $sibling ?? $base;
    }

    /**
     * Maps the letter context to a MasterTemplate.locale code.
     *
     * Today this is driven only by isNi — NI letters get en_NI, everything else en_GB.
     * Welsh (cy_GB) and custom-chrome (customN_*) selection are deliberate future
     * extension points, not in scope for VOL-7305.
     *
     * @param LetterInstance $letter
     * @return string
     */
    private function preferredLocaleFor(LetterInstance $letter): string
    {
        $isNi = $letter->getLicence()?->isNi() ?? false;

        // future: read a per-LetterType chrome override here, or a Welsh-language
        //         flag carried in the LetterInstance, to pick cy_GB or customN_*.

        return $isNi ? MasterTemplate::LOCALE_EN_NI : MasterTemplate::LOCALE_EN_GB;
    }
}
