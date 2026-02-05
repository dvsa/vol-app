<?php

namespace Dvsa\Olcs\Api\Service\Publication\Context\Application;

use Dvsa\Olcs\Api\Service\Publication\Context\AbstractContext;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking as ConditionUndertakingEntity;
use Dvsa\Olcs\Api\Service\Helper\AddressFormatterAwareTrait;
use Dvsa\Olcs\Api\Service\Helper\AddressFormatterAwareInterface;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class ConditionUndertaking
 * @package Dvsa\Olcs\Api\Service\Publication\Context\Application
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
final class ConditionUndertaking extends AbstractContext implements AddressFormatterAwareInterface
{
    use AddressFormatterAwareTrait;

    public const string ATTACHED_LIC = 'Attached to Licence.';
    public const string ATTACHED_OC = 'Attached to Operating Centre: %s';

    public const string COND_NEW = 'New %s: %s';
    public const string COND_REMOVE = '%s to be removed: %s';
    public const string COND_UPDATE = 'Current %s: %s';
    public const string COND_AMENDED = 'Amended to: %s';

    /**
     * @param PublicationLink $publication
     * @param \ArrayObject $context
     * @return \ArrayObject
     */
    public function provide(PublicationLink $publication, \ArrayObject $context)
    {
        $data = [];

        /** @var ArrayCollection $conditionUndertakings */
        $conditionUndertakings = $publication->getApplication()->getConditionUndertakings();

        if (!$conditionUndertakings->isEmpty()) {
            $addressFormatter = $this->getAddressFormatter();

            /** @var ConditionUndertakingEntity $conditionUndertaking */
            foreach ($conditionUndertakings as $conditionUndertaking) {
                $action = $conditionUndertaking->getAction();

                //work out the action
                $actionString = match ($action) {
                    'A' => self::COND_NEW,
                    'D' => self::COND_REMOVE,
                    'U' => self::COND_UPDATE,
                    default => self::COND_NEW,
                };

                $string = sprintf(
                    $actionString,
                    $conditionUndertaking->getConditionType()->getDescription(),
                    $conditionUndertaking->getNotes()
                );

                $operatingCentre = $conditionUndertaking->getOperatingCentre();

                //work out if it's a licence or an oc
                if (!is_null($operatingCentre)) {
                    $string .= ' Attached to Operating Centre: ' .
                        $addressFormatter->format($operatingCentre->getAddress());
                } else {
                    $string .= ' Attached to Licence.';
                }

                if ($action === 'U') {
                    $string .= " " . sprintf(self::COND_AMENDED, $conditionUndertaking->getNotes());
                }

                $data[] = $string;
            }
        }

        $context->offsetSet('conditionUndertaking', $data);

        return $context;
    }
}
