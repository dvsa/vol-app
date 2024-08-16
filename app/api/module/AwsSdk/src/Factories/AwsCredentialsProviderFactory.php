<?php

namespace Dvsa\Olcs\AwsSdk\Factories;

use Aws\Credentials\CredentialProvider;
use Aws\Credentials\InstanceProfileProvider;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * Class AwsCredentailsProviderFactory
 *
 * @author  shaun.hare@dvsa.gov.uk
 * @package Dvsa\Olcs\AwsSdk
 */
class AwsCredentialsProviderFactory implements FactoryInterface
{
    protected function getEnvCredentialProvider()
    {
        return CredentialProvider::env();
    }

    protected function getInstanceProfileCredentialProvider(): InstanceProfileProvider
    {
        return CredentialProvider::instanceProfile();
    }

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $envCredentialsFlag = $container->get('Config')['awsOptions']['useEnvCredentials'] ?? false;
        $credentialsProvider = $envCredentialsFlag ? $this->getEnvCredentialProvider() : $this->getInstanceProfileCredentialProvider();
        return CredentialProvider::memoize($credentialsProvider);
    }
}
