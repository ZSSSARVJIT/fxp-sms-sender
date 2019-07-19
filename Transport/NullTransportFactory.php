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

use Fxp\Component\SmsSender\Exception\UnsupportedSchemeException;

/**
 * Pretends messages have been sent, but just ignores them.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class NullTransportFactory extends AbstractTransportFactory
{
    /**
     * {@inheritdoc}
     */
    public function create(Dsn $dsn): TransportInterface
    {
        if ('sms' === $dsn->getScheme()) {
            return new NullTransport($this->dispatcher, $this->logger);
        }

        throw new UnsupportedSchemeException($dsn, ['sms']);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Dsn $dsn): bool
    {
        return 'null' === $dsn->getHost();
    }
}
