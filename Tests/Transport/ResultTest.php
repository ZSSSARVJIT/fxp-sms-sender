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

use Fxp\Component\SmsSender\Transport\ErrorResult;
use Fxp\Component\SmsSender\Transport\Result;
use Fxp\Component\SmsSender\Transport\SuccessResult;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class ResultTest extends TestCase
{
    public function testGetters(): void
    {
        $result = new Result(\stdClass::class);

        static::assertSame(\stdClass::class, $result->getTransportClassName());
        static::assertCount(0, $result->getSuccesses());
        static::assertCount(0, $result->getErrors());
        static::assertFalse($result->hasErrors());
    }

    public function testAddWithSuccess(): void
    {
        $result = new Result(\stdClass::class);

        /** @var SuccessResult $success */
        $success = $this->getMockBuilder(SuccessResult::class)->disableOriginalConstructor()->getMock();

        static::assertCount(0, $result->getSuccesses());

        $result->add($success);

        static::assertCount(1, $result->getSuccesses());
        static::assertSame($success, $result->getSuccesses()[0]);
        static::assertFalse($result->hasErrors());
    }

    public function testAddWithError(): void
    {
        $result = new Result(\stdClass::class);

        /** @var ErrorResult $success */
        $success = $this->getMockBuilder(ErrorResult::class)->disableOriginalConstructor()->getMock();

        static::assertCount(0, $result->getErrors());
        static::assertFalse($result->hasErrors());

        $result->add($success);

        static::assertCount(1, $result->getErrors());
        static::assertSame($success, $result->getErrors()[0]);
        static::assertTrue($result->hasErrors());
    }
}
