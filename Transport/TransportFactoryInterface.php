<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\SmsSender\Transport;

use Fxp\Component\SmsSender\Exception\IncompleteDsnException;
use Fxp\Component\SmsSender\Exception\UnsupportedSchemeException;

/**
 * Interface for the transport factory.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
interface TransportFactoryInterface
{
    /**
     * Create the transport.
     *
     * @param Dsn $dsn The dsn instance
     *
     * @throws UnsupportedSchemeException
     * @throws IncompleteDsnException
     *
     * @return TransportInterface
     */
    public function create(Dsn $dsn): TransportInterface;

    /**
     * Check if the dsn is supported by the transport.
     *
     * @param Dsn $dsn The dsn instance
     *
     * @return bool
     */
    public function supports(Dsn $dsn): bool;
}
