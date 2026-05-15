<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Template;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryByIdHandler;
use Dvsa\Olcs\Api\Domain\Repository\Template as TemplateRepo;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Psr\Container\ContainerInterface;

/**
 * Template source
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
final class TemplateSource extends AbstractQueryByIdHandler
{
    protected $repoServiceName = 'Template';

    private bool $notifyTestEnabled = false;

    private string $notifyTestHint = '';

    #[\Override]
    public function handleQuery(QueryInterface $query)
    {
        /** @var TemplateRepo $repo */
        $repo = $this->getRepo();
        $template = $repo->fetchUsingId($query);

        // VOL-7238: surface (a) the other locale/format siblings of this template name so
        // the admin edit modal can render quick-jump pills, (b) a flag telling the admin
        // whether the env has a notify_test DSN configured, and (c) an env-aware hint
        // telling the admin what address they should use (any in Mailpit-backed envs vs
        // safelisted only on real Notify trial keys).
        $siblings = $repo->fetchSiblings(
            (string) $template->getName(),
            (int) $template->getId(),
        );

        return $this->result($template, $this->bundle, [
            'siblings' => $siblings,
            'notifyTestEnabled' => $this->notifyTestEnabled,
            'notifyTestHint' => $this->notifyTestHint,
        ]);
    }

    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');
        $dsn = $config['email']['notify_test']['dsn'] ?? null;
        $isValid = is_string($dsn) && $dsn !== '' && !preg_match('/^%[^%]+%$/', $dsn);

        $this->notifyTestEnabled = $isValid;
        $this->notifyTestHint = $this->buildNotifyTestHint(is_string($dsn) ? $dsn : '');

        return parent::__invoke($container, $requestedName, $options);
    }

    /**
     * Env-aware hint shown next to the Send-test recipient input. Mailpit-backed envs accept
     * any address (delivery is intercepted); real-Notify envs reject addresses not on the
     * service's safelist.
     */
    private function buildNotifyTestHint(string $dsn): string
    {
        if (str_starts_with($dsn, 'govuknotify+mailpit')) {
            return 'Any email address works — messages land in this environment\'s Mailpit.';
        }
        if (str_starts_with($dsn, 'govuknotify')) {
            return 'Use an email address on the Notify safelist for this environment (e.g. your own if you\'ve been added, or a known shared mailbox). Anything else will be rejected.';
        }
        return '';
    }
}
