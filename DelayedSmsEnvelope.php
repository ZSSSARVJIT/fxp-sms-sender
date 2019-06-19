<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\SmsSender;

use Fxp\Component\SmsSender\Exception\InvalidArgumentException;
use Fxp\Component\SmsSender\Exception\LogicException;
use Fxp\Component\SmsSender\Mime\Phone;
use Fxp\Component\SmsSender\Mime\Sms;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Header\MailboxListHeader;
use Symfony\Component\Mime\Message;
use Symfony\Component\Mime\RawMessage;

/**
 * Delayed Sms envelope.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class DelayedSmsEnvelope extends SmsEnvelope
{
    /**
     * @var bool
     */
    private $senderSet = false;

    /**
     * @var bool
     */
    private $recipientsSet = false;

    /**
     * @var Message
     */
    private $message;

    /**
     * Constructor.
     *
     * @param Message|RawMessage $message
     */
    public function __construct(RawMessage $message)
    {
        if (!$message instanceof Message) {
            throw new InvalidArgumentException(sprintf(
                'A delayed SMS envelope requires an instance of %s ("%s" given).',
                Message::class,
                \get_class($message)
            ));
        }

        $this->message = $message;
    }

    public function setFrom(Phone $from): void
    {
        parent::setFrom($from);

        $this->senderSet = true;
    }

    public function getFrom(): Phone
    {
        if ($this->senderSet) {
            return parent::getFrom();
        }

        if ($this->message instanceof Sms && null !== $from = $this->message->getFrom()) {
            return $from;
        }

        throw new LogicException('Unable to determine the sender of the message.');
    }

    public function setRecipients(array $recipients): void
    {
        parent::setRecipients($recipients);

        $this->recipientsSet = \count(parent::getRecipients()) > 0;
    }

    /**
     * @return Phone[]
     */
    public function getRecipients(): array
    {
        if ($this->recipientsSet) {
            return parent::getRecipients();
        }

        return self::getRecipientsFromHeaders($this->message->getHeaders());
    }

    private static function getRecipientsFromHeaders(Headers $headers): array
    {
        $recipients = [];

        /** @var MailboxListHeader $header */
        foreach ($headers->getAll('to') as $header) {
            /** @var Phone $phone */
            foreach ($header->getAddresses() as $phone) {
                $recipients[] = new Phone($phone->getPhone());
            }
        }

        return $recipients;
    }
}
