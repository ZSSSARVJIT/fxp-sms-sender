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
use Fxp\Component\SmsSender\Transport\ErrorResult;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class ErrorResultTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $recipient = new Phone('+100');
        $message = 'MESSAGE';
        $code = 'code';
        $data = [
            'foo' => 'bar',
        ];
        $exception = new \Exception();

        $result = new ErrorResult($recipient, $message, $code, $data, $exception);

        static::assertSame($recipient, $result->getRecipient());
        static::assertSame($message, $result->getMessage());
        static::assertSame($code, $result->getCode());
        static::assertSame($data, $result->getData());
        static::assertSame($exception, $result->getException());
    }
}
