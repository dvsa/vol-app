<?php

namespace Dvsa\Olcs\Transfer\Query;

use Laminas\InputFilter\InputFilterInterface;

/**
 * Query Container Interface
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
interface QueryContainerInterface
{
    public function setInputFilter(InputFilterInterface $inputFilter);

    public function getInputFilter();

    public function isCustomCacheable(): bool;

    public function isShortTermCacheable();

    public function isMediumTermCacheable();

    public function isLongTermCacheable(): bool;

    public function isPersistentCacheable(): bool;

    public function isPublicCacheable(): bool;

    public function isSharedEncryptionCacheable(): bool;

    public function isStream();

    public function setDto(QueryInterface $dto);

    public function getDto();

    public function getDtoClassName(): string;

    public function setRouteName($routeName);

    public function getRouteName();

    public function isValid();

    public function getMessages();

    public function getEncryptionMode(): string;
}
