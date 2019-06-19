<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\SmsSender\Tests\Messenger;

use Fxp\Component\SmsSender\Messenger\SendSmsMessage;
use Fxp\Component\SmsSender\Mime\Phone;
use Fxp\Component\SmsSender\SmsEnvelope;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mime\RawMessage;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class SendSmsMessageTest extends TestCase
{
    public function testGetters(): void
    {
        $message = new RawMessage('');
        $envelope = new SmsEnvelope(new Phone('+100'), [new Phone('+2000')]);

        $sentMessage = new SendSmsMessage($message, $envelope);

        static::assertSame($message, $sentMessage->getMessage());
        static::assertSame($envelope, $sentMessage->getEnvelope());
    }
}
