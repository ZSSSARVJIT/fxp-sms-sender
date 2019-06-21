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

use Fxp\Component\SmsSender\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\RawMessage;

/**
 * Interface for the sms sender.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
interface SmsSenderInterface
{
    /**
     * Send the message.
     *
     * @param RawMessage       $message  The message
     * @param null|SmsEnvelope $envelope The envelope
     *
     * @throws TransportExceptionInterface
     */
    public function send(RawMessage $message, SmsEnvelope $envelope = null): void;

    /**
     * Check if the transport has a required from phone.
     *
     * @return bool
     */
    public function hasRequiredFrom(): bool;
}
