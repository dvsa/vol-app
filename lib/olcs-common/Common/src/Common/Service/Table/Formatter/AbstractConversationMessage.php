<?php

declare(strict_types=1);

namespace Common\Service\Table\Formatter;

use DateTimeImmutable;
use DateTimeInterface;

abstract class AbstractConversationMessage implements FormatterPluginManagerInterface
{
    protected string $rowTemplate;

    protected string $defaultSenderName = "Sent by user now deleted";

    /**
     * status
     * @inheritdoc
     */
    #[\Override]
    public function format(array $data, array $column = []): string
    {
        $senderName = $this->getSenderName($data);

        // $data["createdOn"] already contains a timezone so createFromFormat will ignore any timezone passed as the
        // third parameter. to override it we need to force set the timezone to the default one
        $latestMessageCreatedAt = DateTimeImmutable::createFromFormat(
            DateTimeInterface::ATOM,
            $data["createdOn"]
        )->setTimezone(new \DateTimeZone(date_default_timezone_get()));

        $date = $latestMessageCreatedAt->format('l j F Y \a\t H:ia');

        $fileList = $this->getFileList($data);

        $firstReadBy = $this->getFirstReadBy($data);

        // If createdBy (User) has a Team, they are an internal user.
        $internalCaseworkerTeam = (empty($data['createdBy']['team'])) ? '' : '<p class="govuk-caption-m">' . $senderName . '<br/>Caseworker Team</p>';

        return strtr($this->rowTemplate, [
            '{senderName}' => $senderName,
            '{messageDate}' => $date,
            '{messageBody}' => nl2br($data['messagingContent']['text']),
            '{caseworkerFooter}' => $internalCaseworkerTeam,
            '{fileList}' => $fileList,
            '{firstReadBy}' => $firstReadBy,
        ]);
    }

    protected function isInternalUser($row): bool
    {
        return !empty($row['createdBy']['team']);
    }

    /**
     * From https://stackoverflow.com/questions/2510434/format-bytes-to-kilobytes-megabytes-gigabytes
     * originally from Chris Jester-Young.
     */
    protected function readableBytes(int $bytes): string
    {
        $base = log($bytes) / log(1024);
        $suffixes = ['B', 'KB', 'MB', 'GB', 'TB'];

        return round(1024 ** ($base - floor($base)), 2) . $suffixes[floor($base)];
    }

    /**
     * Returns HTML - The first user to read the given message
     */
    protected function getFirstReadBy(array $row): string
    {
        if (
            !isset($row['userMessageReads'])
            || !is_array($row['userMessageReads'])
            || count($row['userMessageReads']) === 0
        ) {
            return '';
        }

        $firstRead = null;
        while ($firstRead = array_pop($row['userMessageReads'])) {
            if ($firstRead === null || $row['createdBy']['id'] !== $firstRead['user']['id']) {
                break;
            }
        }

        if ($firstRead === null) {
            return '';
        }

        $firstReadOn = DateTimeImmutable::createFromFormat(DateTimeInterface::ATOM, $firstRead["createdOn"]);

        if (isset($firstRead['user']['contactDetails']['person'])) {
            $firstReadBy = $firstRead['user']['contactDetails']['person']['forename'] . ' ' .
                           $firstRead['user']['contactDetails']['person']['familyName'];
        } elseif (isset($firstRead['user']['contactDetails']['emailAddress'])) {
            $firstReadBy = $firstRead['user']['contactDetails']['emailAddress'];
        } else {
            $firstReadBy = $firstRead['user']['loginId'];
        }

        return sprintf(
            '<hr/><p><em>First read by %s on %s</em></p>',
            $firstReadBy,
            $firstReadOn->format('l j F Y \a\t H:ia'),
        );
    }

    /**
     * Returns HTML - File/Attachments list for given message
     */
    protected function getFileList(array $row): string
    {
        $fileList = array_map(
            fn($doc) => sprintf(
                '<li class="file"><a href="/file/%s" class="govuk-link">%s</a> <span>%s</span></li>',
                $doc['id'],
                $doc['description'],
                $this->readableBytes($doc['size']),
            ),
            $row['documents'],
        );
        if ($fileList !== []) {
            return '
                <h3 class="file__heading">Attachments</h3>
                <div class="file-uploader">
                    <ul>' . implode('', $fileList) . '</ul>
                </div>
            ';
        }

        return '';
    }

    protected function getSenderName(array $row): string
    {
        $senderName = $this->defaultSenderName;

        if (!empty($row['createdBy'])) {
            if (!empty($row['createdBy']['contactDetails']['person'])) {
                $person = $row['createdBy']['contactDetails']['person'];
                $senderName = $person['forename'] . " " . $person['familyName'];
            } else {
                $senderName = $row['createdBy']['loginId'];
            }
        }

        return $senderName;
    }
}
