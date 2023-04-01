<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle\Message;

final class FightMessage implements FightMessageInterface
{
    /**
     * @var array<string>
     */
    private array $msg = [];

    private int $senderId;

    private ?int $recipientId;

    /**
     * @param array<string>|null $msg
     */
    public function __construct(
        int $senderId,
        ?int $recipientId,
        ?array $msg = null
    ) {
        $this->senderId = $senderId;
        $this->recipientId = $recipientId;
        if ($msg !== null) {
            $this->msg = $msg;
        }
    }

    public function getSenderId(): int
    {
        return $this->senderId;
    }

    public function getRecipientId(): ?int
    {
        return $this->recipientId;
    }

    public function getMessage(): array
    {
        return $this->msg;
    }

    public function add(?string $msg): void
    {
        if ($msg === null) {
            return;
        }

        $this->msg[] = $msg;
    }

    public function addMessageMerge(array $msg): void
    {
        if (empty($msg)) {
            return;
        }

        $this->msg = array_merge($this->msg, $msg);
    }
}