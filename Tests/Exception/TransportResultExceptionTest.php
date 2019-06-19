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

use Fxp\Component\SmsSender\Exception\TransportResultException;
use Fxp\Component\SmsSender\Mime\Phone;
use Fxp\Component\SmsSender\Transport\ErrorResult;
use Fxp\Component\SmsSender\Transport\Result;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class TransportResultExceptionTest extends TestCase
{
    public function testGetResult(): void
    {
        $result = new Result(\stdClass::class);
        $ex = new TransportResultException($result);

        static::assertSame($result, $ex->getResult());
    }

    public function testBuildMessage(): void
    {
        $result = new Result(\stdClass::class);
        $result->add(new ErrorResult(new Phone('+100'), 'Error message', 'error_code'));

        $ex = new TransportResultException($result);

        $expectedMessage = str_replace(
            "\n",
            PHP_EOL,
            <<<'EOF'
Unable to send an SMS for recipients:
- +100: Error message (error_code)
EOF
        );

        static::assertSame($expectedMessage, $ex->getMessage());
    }
}
