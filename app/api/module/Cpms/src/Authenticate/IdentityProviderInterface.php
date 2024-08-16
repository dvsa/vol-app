<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Cpms\Authenticate;

/**
 * Interface IdentityProviderInterface
 */
interface IdentityProviderInterface
{
    /**
     * OAuth 2.0 client_id
     *
     * @return string
     */
    public function getClientId(): string;

    /**
     * OAuth 2.0 client_secret
     *
     * @return string
     */
    public function getClientSecret(): string;

    /**
     * Logged in user (user.id)
     *
     * @return string
     */
    public function getUserId(): string;

    /**
     * Get the reference to the customer the payment is for
     *
     */
    public function getCustomerReference(): ?string;

    /**
     * Get the cost centre
     *
     * @return string
     */
    public function getCostCentre(): string;
}
