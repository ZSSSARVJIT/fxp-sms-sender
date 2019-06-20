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

use Fxp\Component\SmsSender\Exception\LogicException;
use Fxp\Component\SmsSender\TransportUtil;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class TransportUtilTest extends TestCase
{
    public function testValidateInstallWithInvalidClassName(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Unable to send SMS via Foo Transport as the bridge is not installed. Try running "composer require foo/bar-bridge".');

        TransportUtil::validateInstall('Transport\Not\Installed', 'Foo Transport', 'foo/bar-bridge');
    }
}
