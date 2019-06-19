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

use Fxp\Component\SmsSender\SentMessage;
use Fxp\Component\SmsSender\SmsEnvelope;
use Fxp\Component\SmsSender\Transport\Result;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mime\Message;
use Symfony\Component\Mime\RawMessage;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class SentMessageTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $message = new RawMessage('CONTENT');

        /** @var SmsEnvelope $envelope */
        $envelope = $this->getMockBuilder(SmsEnvelope::class)->disableOriginalConstructor()->getMock();

        /** @var Result $result */
        $result = $this->getMockBuilder(Result::class)->disableOriginalConstructor()->getMock();

        $sentMessage = new SentMessage($message, $envelope, $result);

        static::assertSame($message, $sentMessage->getMessage());

        static::assertSame($message, $sentMessage->getOriginalMessage());

        static::assertSame($envelope, $sentMessage->getEnvelope());

        static::assertSame($result, $sentMessage->getResult());

        static::assertSame($message->toString(), $sentMessage->toString());

        static::assertCount(1, $sentMessage->toIterable());
    }

    public function testWithMessageClass(): void
    {
        $message = new Message();

        /** @var SmsEnvelope $envelope */
        $envelope = $this->getMockBuilder(SmsEnvelope::class)->disableOriginalConstructor()->getMock();

        /** @var Result $result */
        $result = $this->getMockBuilder(Result::class)->disableOriginalConstructor()->getMock();

        $sentMessage = new SentMessage($message, $envelope, $result);

        static::assertNotSame($message, $sentMessage->getMessage());
        static::assertSame(RawMessage::class, \get_class($sentMessage->getMessage()));

        static::assertSame($message, $sentMessage->getOriginalMessage());
    }
}
