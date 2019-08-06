<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\SmsSender\Transport;

use Fxp\Component\SmsSender\Exception\TransportExceptionInterface;
use Fxp\Component\SmsSender\SentMessage;
use Fxp\Component\SmsSender\SmsEnvelope;
use Symfony\Component\Mime\RawMessage;

/**
 * Interface for the transport.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
interface TransportInterface
{
    /**
     * Get the name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Send the message.
     *
     * @param RawMessage       $message  The message
     * @param null|SmsEnvelope $envelope The envelope
     *
     * @throws TransportExceptionInterface
     *
     * @return null|SentMessage
     */
    public function send(RawMessage $message, SmsEnvelope $envelope = null): ?SentMessage;

    /**
     * Check if the from phone is required.
     *
     * @return bool
     */
    public function hasRequiredFrom(): bool;
}
