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
use Fxp\Component\SmsSender\Transport\SuccessResult;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class SuccessResultTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $recipient = new Phone('+100');
        $data = [
            'foo' => 'bar',
        ];

        $result = new SuccessResult($recipient, $data);

        static::assertSame($recipient, $result->getRecipient());
        static::assertSame($data, $result->getData());
    }
}
