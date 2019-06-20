<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\SmsSender;

use Fxp\Component\SmsSender\Exception\LogicException;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class TransportUtil
{
    /**
     * Validate the installation of the bridge transport.
     *
     * @param string $class   The class name of the transport
     * @param string $name    The name of transport
     * @param string $package The composer package name
     */
    public static function validateInstall(string $class, string $name, string $package): void
    {
        if (!class_exists($class)) {
            throw new LogicException(sprintf(
                'Unable to send SMS via %s as the bridge is not installed. Try running "composer require %s".',
                $name,
                $package
            ));
        }
    }
}
