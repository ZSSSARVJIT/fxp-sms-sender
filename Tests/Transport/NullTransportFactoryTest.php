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

use Fxp\Component\SmsSender\Tests\TransportFactoryTestCase;
use Fxp\Component\SmsSender\Transport\Dsn;
use Fxp\Component\SmsSender\Transport\NullTransport;
use Fxp\Component\SmsSender\Transport\NullTransportFactory;
use Fxp\Component\SmsSender\Transport\TransportFactoryInterface;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class NullTransportFactoryTest extends TransportFactoryTestCase
{
    public function getFactory(): TransportFactoryInterface
    {
        return new NullTransportFactory($this->getDispatcher(), $this->getClient(), $this->getLogger());
    }

    public function supportsProvider(): iterable
    {
        yield [
            new Dsn('sms', 'null'),
            true,
        ];

        yield [
            new Dsn('sms', 'example.com'),
            false,
        ];
    }

    public function createProvider(): iterable
    {
        yield [
            new Dsn('sms', 'null'),
            new NullTransport($this->getDispatcher(), $this->getLogger()),
        ];
    }

    public function unsupportedSchemeProvider(): iterable
    {
        yield [new Dsn('foo', 'null')];
    }

    public function incompleteDsnProvider(): iterable
    {
        return $this->unsupportedSchemeProvider();
    }

    /**
     * @dataProvider incompleteDsnProvider
     *
     * @param Dsn $dsn
     */
    public function testIncompleteDsnException(Dsn $dsn): void
    {
        static::assertTrue(true);
    }
}
