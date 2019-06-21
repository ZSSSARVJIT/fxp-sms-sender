<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\SmsSender\Tests\Mime;

use Fxp\Component\SmsSender\Mime\Phone;
use Fxp\Component\SmsSender\Mime\Sms;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mime\Header\UnstructuredHeader;
use Symfony\Component\Mime\Part\TextPart;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class SmsTest extends TestCase
{
    public function testFrom(): void
    {
        $sms = new Sms();
        $sms->from('+1 00');

        static::assertInstanceOf(Phone::class, $sms->getFrom());
    }

    public function testTo(): void
    {
        $sms = new Sms();
        $sms->addTo('+100', '+101');

        static::assertCount(2, $sms->getTo());
        static::assertInstanceOf(Phone::class, $sms->getTo()[0]);
        static::assertInstanceOf(Phone::class, $sms->getTo()[1]);

        $sms->addTo('+102', '+103', '+104');
        static::assertCount(5, $sms->getTo());

        $sms->to('+100');
        static::assertCount(1, $sms->getTo());
    }

    public function testText(): void
    {
        $sms = new Sms();

        static::assertNull($sms->getText());

        $sms->text('Foo');
        static::assertInstanceOf(TextPart::class, $sms->getBody());
        static::assertSame('Foo', $sms->getText());
    }

    public function testAddHeader(): void
    {
        $sms = new Sms();

        static::assertCount(0, $sms->getHeaders()->getAll());

        $sms->addHeader(new UnstructuredHeader('foo', 'Bar'));

        static::assertCount(1, $sms->getHeaders()->getAll());
    }
}
