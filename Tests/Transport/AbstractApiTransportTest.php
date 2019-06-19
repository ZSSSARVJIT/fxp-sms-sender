<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\SmsSender\Tests\Transport;

use Fxp\Component\SmsSender\Exception\TransportException;
use Fxp\Component\SmsSender\Exception\TransportResultException;
use Fxp\Component\SmsSender\Mime\Phone;
use Fxp\Component\SmsSender\Mime\Sms;
use Fxp\Component\SmsSender\SmsEnvelope;
use Fxp\Component\SmsSender\Transport\AbstractApiTransport;
use Fxp\Component\SmsSender\Transport\ErrorResult;
use Fxp\Component\SmsSender\Transport\Result;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mime\Message;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class AbstractApiTransportTest extends TestCase
{
    /**
     * @throws
     */
    public function testSend(): void
    {
        $transport = $this->getMockForAbstractClass(AbstractApiTransport::class);

        $transport->expects(static::once())->method('doSendSms');

        $message = new Sms();
        $message->to('+2000');

        $transport->send($message);
    }

    /**
     * @throws
     */
    public function testSendWithInvalidMessage(): void
    {
        $this->expectException(TransportException::class);
        $this->expectExceptionMessageRegExp('/Unable to send message with the "(\w+)" transport: The message must be an instance Fxp\\\Component\\\SmsSender\\\Mime\\\Sms \("Symfony\\\Component\\\Mime\\\Message" given\)./');

        $transport = $this->getMockForAbstractClass(AbstractApiTransport::class);

        $message = new Message();
        $message->getHeaders()->addMailboxListHeader('To', [new Phone('+2000')]);

        $transport->send($message);
    }

    /**
     * @throws
     */
    public function testSendWithResultError(): void
    {
        $this->expectException(TransportResultException::class);

        $message = new Sms();
        $recipient = new Phone('+100');
        $message->to($recipient);

        $transport = $this->getMockForAbstractClass(AbstractApiTransport::class);

        $transport->expects(static::once())
            ->method('doSendSms')
            ->willReturnCallback(static function (Sms $sms, SmsEnvelope $envelope, Result $result) use ($recipient): void {
                $result->add(new ErrorResult($recipient, 'Error message', 'error_code'));
            })
        ;

        $transport->send($message);
    }
}
