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

use Fxp\Component\SmsSender\Mime\Phone;
use Fxp\Component\SmsSender\SmsEnvelope;
use Fxp\Component\SmsSender\Transport\NullTransport;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mime\Message;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class NullTransportTest extends TestCase
{
    public function testGetName(): void
    {
        $t = new NullTransport();
        static::assertEquals('sms://null', $t->getName());
    }

    public function testSend(): void
    {
        $transport = new NullTransport();

        $transport->send(new Message(), new SmsEnvelope(new Phone('+100'), [new Phone('+2000')]));
        static::assertTrue(true);
    }
}
