<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\SmsSender\Mime;

use Fxp\Component\SmsSender\Exception\E164ComplianceException;
use Fxp\Component\SmsSender\Exception\InvalidArgumentException;
use Fxp\Component\SmsSender\Exception\LogicException;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Encoder\IdnAddressEncoder;

/**
 * Phone.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class Phone extends Address
{
    /**
     * @var string
     */
    public static $encoderClass = PhoneNumberUtil::class;

    /**
     * @var PhoneNumberUtil
     */
    private static $phoneEncoder;

    /**
     * @var null|IdnAddressEncoder
     */
    private static $addressEncoder;

    /**
     * @var string
     */
    private $phone;

    /**
     * Constructor.
     *
     * @param string $phone The phone
     */
    public function __construct(string $phone)
    {
        if (!class_exists(static::$encoderClass)) {
            throw new LogicException(sprintf('The "%s" class cannot be used as it needs "%s"; try running "composer require giggsey/libphonenumber-for-php".', \get_class($this), static::$encoderClass));
        }

        if (null === self::$phoneEncoder) {
            self::$phoneEncoder = PhoneNumberUtil::getInstance();
        }

        try {
            self::$phoneEncoder->parse($phone);
        } catch (NumberParseException $e) {
            throw new E164ComplianceException(sprintf('Phone "%s" does not comply with number-spec of E164.', $phone));
        }

        $this->phone = $phone;
    }

    /**
     * Get the phone.
     *
     * @return string
     */
    public function getPhone(): string
    {
        return $this->phone;
    }

    /**
     * Convert the phone into a string.
     *
     * @return string
     */
    public function toString(): string
    {
        return $this->getEncodedPhone();
    }

    /**
     * Get the encoded phone.
     *
     * @throws
     *
     * @return string
     */
    public function getEncodedPhone(): string
    {
        return self::$phoneEncoder->format(self::$phoneEncoder->parse($this->phone), PhoneNumberFormat::E164);
    }

    /**
     * {@inheritdoc}
     */
    public function getAddress(): string
    {
        return $this->getEncodedPhone().'@carrier';
    }

    /**
     * {@inheritdoc}
     */
    public function getEncodedAddress(): string
    {
        if (null === self::$addressEncoder) {
            self::$addressEncoder = new IdnAddressEncoder();
        }

        return self::$addressEncoder->encodeString($this->getAddress());
    }

    /**
     * Create the phone instance.
     *
     * @param Phone|string $phone The phone
     *
     * @return static
     */
    public static function create($phone): Address
    {
        if ($phone instanceof self) {
            return $phone;
        }

        if (\is_string($phone)) {
            return new self($phone);
        }

        throw new InvalidArgumentException(sprintf(
            'A phone can be an instance of %s or a string ("%s" given).',
            static::class,
            \is_object($phone) ? \get_class($phone) : \gettype($phone)
        ));
    }

    /**
     * Create the phone instances.
     *
     * @param Phone[]|string[] $phones The phones
     *
     * @return static[]
     */
    public static function createArray(array $phones): array
    {
        $res = [];

        foreach ($phones as $phone) {
            $res[] = self::create($phone);
        }

        return $res;
    }
}
