<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\SmsSender\Tests\Event;

use Fxp\Component\SmsSender\Event\MessageResultEvent;
use Fxp\Component\SmsSender\Mime\Phone;
use Fxp\Component\SmsSender\SmsEnvelope;
use Fxp\Component\SmsSender\Transport\Result;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mime\RawMessage;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class MessageResultEventTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $message = new RawMessage('');
        $envelope = new SmsEnvelope(new Phone('+100'), [new Phone('+2000')]);
        $result = new Result(\stdClass::class);

        $event = new MessageResultEvent($message, $envelope, $result);

        static::assertSame($message, $event->getMessage());
        static::assertSame($envelope, $event->getEnvelope());
        static::assertSame($result, $event->getResult());

        $event->setMessage(clone $message);
        $event->setEnvelope(clone $envelope);

        static::assertNotSame($message, $event->getMessage());
        static::assertNotSame($envelope, $event->getEnvelope());
    }
}
