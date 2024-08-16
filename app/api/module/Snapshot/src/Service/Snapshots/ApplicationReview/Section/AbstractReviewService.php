<?php

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Dvsa\Olcs\Snapshot\Service\Formatter\Address;
use Dvsa\Olcs\Snapshot\Service\Snapshots\FormatReviewDataTrait;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Laminas\I18n\Translator\TranslatorInterface;

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
    use FormatReviewDataTrait;

    public const SIGNATURE = 'markup-application_undertakings_signature';
    public const SIGNATURE_ADDRESS_GB = 'markup-application_undertakings_signature_address_gb';
    public const SIGNATURE_ADDRESS_NI = 'markup-application_undertakings_signature_address_ni';

    /** @var TranslatorInterface */
    protected $translator;

    /**
     * Create service instance
     *
     *
     * @return AbstractReviewService
     */
    public function __construct(AbstractReviewServiceServices $abstractReviewServiceServices)
    {
        $this->translator = $abstractReviewServiceServices->getTranslator();
    }

    protected function formatText($text)
    {
        return nl2br((string) $text);
    }

    protected function findFiles($files, $category, $subCategory)
    {
        $foundFiles = [];

        foreach ($files as $file) {
            if ($file['category']['id'] == $category && $file['subCategory']['id'] == $subCategory) {
                $foundFiles[] = $file;
            }
        }

        return $foundFiles;
    }

    protected function formatNumber($number)
    {
        return number_format($number);
    }

    protected function formatAmount($amount)
    {
        return '£' . number_format($amount, 0);
    }

    protected function formatRefdata($refData)
    {
        return $refData['description'];
    }

    protected function formatConfirmed($value)
    {
        return $value === 'Y' ? 'Confirmed' : 'Unconfirmed';
    }

    protected function formatPersonFullName($person)
    {
        $parts = [];

        if (isset($person['title'])) {
            $parts[] = $person['title']['description'];
        }

        $parts[] = $person['forename'];
        $parts[] = $person['familyName'];

        return implode(' ', $parts);
    }

    protected function isPsv($data)
    {
        return !$data['isGoods'];
    }

    protected function isInternal($data)
    {
        return $data['isInternal'];
    }

    protected function getSignature($data)
    {
        $titles = [
            Organisation::ORG_TYPE_REGISTERED_COMPANY => 'undertakings_directors_signature',
            Organisation::ORG_TYPE_LLP => 'undertakings_directors_signature',
            Organisation::ORG_TYPE_PARTNERSHIP => 'undertakings_partners_signature',
            Organisation::ORG_TYPE_SOLE_TRADER => 'undertakings_owners_signature',
            Organisation::ORG_TYPE_OTHER => 'undertakings_responsiblepersons_signature',
            Organisation::ORG_TYPE_IRFO => 'undertakings_responsiblepersons_signature'
        ];
        $addresses = [
            'Y' => self::SIGNATURE_ADDRESS_NI,
            'N' => self::SIGNATURE_ADDRESS_GB
        ];
        $title = $titles[$data['licence']['organisation']['type']['id']];
        $address = $this->translate($addresses[$data['niFlag']]);

        $additionalParts = [
            $this->translate($title),
            $address
        ];
        return $this->translateReplace(self::SIGNATURE, $additionalParts);
    }

    /**
     * Get the translation key to be used by this section of the snapshot
     *
     * @param string $section
     * @return string
     */
    public function getHeaderTranslationKey(array $reviewData, $section)
    {
        return 'review-' . $section;
    }
}
