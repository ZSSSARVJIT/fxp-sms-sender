<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\SmsSender\Tests;

use Fxp\Component\SmsSender\Exception\InvalidArgumentException;
use Fxp\Component\SmsSender\Mime\Phone;
use Fxp\Component\SmsSender\SmsEnvelope;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class SmsEnvelopeTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $from = new Phone('+100');
        $recipients = [
            new Phone('+2000'),
            new Phone('+2000'),
        ];

        $envelope = new SmsEnvelope($from, $recipients);

        static::assertSame($from, $envelope->getFrom());
        static::assertSame($recipients, $envelope->getRecipients());

        $from2 = clone $from;
        $recipients2 = [clone $recipients[0]];

        $envelope->setFrom($from2);
        $envelope->setRecipients($recipients2);

        static::assertSame($from2, $envelope->getFrom());
        static::assertSame($recipients2, $envelope->getRecipients());
    }

    public function testSetRecipientsWithInvalidRecipient(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A recipient must be an instance of "Fxp\Component\SmsSender\Mime\Phone" (got "stdClass").');

        new SmsEnvelope(new Phone('+100'), [new \stdClass()]);
    }
}
