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

use Fxp\Component\SmsSender\Exception\IncompleteDsnException;
use Fxp\Component\SmsSender\Transport\AbstractTransportFactory;
use Fxp\Component\SmsSender\Transport\Dsn;
use Fxp\Component\SmsSender\Transport\TransportInterface;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class AbstractTransportFactoryTest extends TestCase
{
    public function testGetUser(): void
    {
        /** @var TransportInterface $transport */
        $transport = $this->getMockBuilder(TransportInterface::class)->getMock();
        $factory = new MockTransportFactory($transport);
        $dsn = new Dsn('scheme', 'host', 'u$er');

        static::assertSame('u$er', $factory->getUserValue($dsn));
    }

    public function testGetUserWithoutValue(): void
    {
        $this->expectException(IncompleteDsnException::class);
        $this->expectExceptionMessage('User is not set');

        /** @var TransportInterface $transport */
        $transport = $this->getMockBuilder(TransportInterface::class)->getMock();
        $factory = new MockTransportFactory($transport);
        $dsn = new Dsn('scheme', 'host');

        $factory->getUserValue($dsn);
    }

    public function testGetPassword(): void
    {
        /** @var TransportInterface $transport */
        $transport = $this->getMockBuilder(TransportInterface::class)->getMock();
        $factory = new MockTransportFactory($transport);
        $dsn = new Dsn('scheme', 'host', null, 'pa$s');

        static::assertSame('pa$s', $factory->getPasswordValue($dsn));
    }

    public function testGetPasswordWithoutValue(): void
    {
        $this->expectException(IncompleteDsnException::class);
        $this->expectExceptionMessage('Password is not set');

        /** @var TransportInterface $transport */
        $transport = $this->getMockBuilder(TransportInterface::class)->getMock();
        $factory = new MockTransportFactory($transport);
        $dsn = new Dsn('scheme', 'host');

        $factory->getPasswordValue($dsn);
    }
}

class MockTransportFactory extends AbstractTransportFactory
{
    private $transport;

    public function __construct(TransportInterface $transport)
    {
        parent::__construct();

        $this->transport = $transport;
    }

    public function create(Dsn $dsn): TransportInterface
    {
        return $this->transport;
    }

    public function supports(Dsn $dsn): bool
    {
        return true;
    }

    public function getUserValue(Dsn $dsn): string
    {
        return $this->getUser($dsn);
    }

    public function getPasswordValue(Dsn $dsn): string
    {
        return $this->getPassword($dsn);
    }
}
