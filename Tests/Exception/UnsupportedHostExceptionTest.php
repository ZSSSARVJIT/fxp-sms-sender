<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\SmsSender\Tests\Exception;

use Fxp\Component\SmsSender\Exception\UnsupportedHostException;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class UnsupportedHostExceptionTest extends TestCase
{
    public function testBuildMessage(): void
    {
        $expected = 'The "host" SMS Sender is not supported.';

        static::assertSame($expected, UnsupportedHostException::buildMessage('host', []));
    }

    public function testBuildMessageWithPackage(): void
    {
        $expected = 'Unable to send sms via "host" as the bridge is not installed. Try running "composer require vendor/sms-bridge".';

        static::assertSame($expected, UnsupportedHostException::buildMessage('host', [
            'host' => [
                'class' => 'InvalidClass',
                'package' => 'vendor/sms-bridge',
            ],
        ]));
    }
}
