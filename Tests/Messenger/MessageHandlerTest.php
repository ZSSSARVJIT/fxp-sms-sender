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

use Fxp\Component\SmsSender\Messenger\MessageHandler;
use Fxp\Component\SmsSender\Messenger\SendSmsMessage;
use Fxp\Component\SmsSender\Mime\Phone;
use Fxp\Component\SmsSender\SmsEnvelope;
use Fxp\Component\SmsSender\Transport\TransportInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mime\RawMessage;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class MessageHandlerTest extends TestCase
{
    public function testInvoke(): void
    {
        $message = new RawMessage('');
        $envelope = new SmsEnvelope(new Phone('+100'), [new Phone('+2000')]);
        $sendMessage = new SendSmsMessage($message, $envelope);

        /** @var MockObject|TransportInterface $transport */
        $transport = $this->getMockBuilder(TransportInterface::class)->getMock();
        $transport->expects(static::once())
            ->method('send')
            ->with($message, $envelope)
        ;

        $messageHandler = new MessageHandler($transport);
        $messageHandler($sendMessage);
    }
}
