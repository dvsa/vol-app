<?php

/**
 * Abstract Review Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Service\Review;

use Common\RefData;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\Formatter\Address;

/**
 * Abstract Review Service
 *
 * @NOTE Not yet decided whether I should use this abstract to share code, or whether it would be better to use another
 * service, another service would be easier to test in isolation
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractReviewService implements ReviewServiceInterface
{
    /** @var TranslationHelperService */
    protected $translationHelper;

    /**
     * Create service instance
     *
     *
     * @return AbstractReviewService
     */
    public function __construct(AbstractReviewServiceServices $abstractReviewServiceServices, private Address $addressFormatter)
    {
        $this->translationHelper = $abstractReviewServiceServices->getTranslationHelper();
    }

    protected function formatText($text): string
    {
        return nl2br($text);
    }

    /**
     * @psalm-return list<mixed>
     */
    protected function findFiles($files, $category, $subCategory): array
    {
        $foundFiles = [];

        foreach ($files as $file) {
            if ($file['category']['id'] != $category) {
                continue;
            }
            if ($file['subCategory']['id'] != $subCategory) {
                continue;
            }
            $foundFiles[] = $file;
        }

        return $foundFiles;
    }

    protected function formatNumber($number): string
    {
        return number_format($number);
    }

    protected function formatAmount($amount): string
    {
        return '£' . number_format($amount, 0);
    }

    protected function formatRefdata($refData)
    {
        return $refData['description'];
    }

    protected function formatShortAddress($address): string
    {
        return $this->addressFormatter->format($address);
    }

    protected function formatFullAddress($address): string
    {
        return $this->addressFormatter->format($address, ['addressFields' => 'FULL']);
    }

    protected function formatConfirmed($value): string
    {
        return $value === 'Y' ? 'Confirmed' : 'Unconfirmed';
    }

    protected function formatDate($date, $format = 'd F Y'): string
    {
        return date($format, strtotime($date));
    }

    protected function formatYesNo($value): string
    {
        return $value === 'Y' ? 'Yes' : 'No';
    }

    protected function formatPersonFullName($person): string
    {
        $parts = [];

        if (isset($person['title'])) {
            $parts[] = $person['title']['description'];
        }

        $parts[] = $person['forename'];
        $parts[] = $person['familyName'];

        return implode(' ', $parts);
    }

    protected function isPsv($data): bool
    {
        return $data['goodsOrPsv']['id'] === RefData::LICENCE_CATEGORY_PSV;
    }

    protected function translate($string): string
    {
        return $this->translationHelper->translate($string);
    }
}
