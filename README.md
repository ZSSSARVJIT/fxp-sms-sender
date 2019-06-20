Fxp SMS Sender
==============

[![Latest Version](https://img.shields.io/packagist/v/fxp/sms-sender.svg)](https://packagist.org/packages/fxp/sms-sender)
[![Build Status](https://img.shields.io/travis/fxpio/fxp-sms-sender/master.svg)](https://travis-ci.org/fxpio/fxp-sms-sender)
[![Coverage Status](https://img.shields.io/coveralls/fxpio/fxp-sms-sender/master.svg)](https://coveralls.io/r/fxpio/fxp-sms-sender?branch=master)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/fxpio/fxp-sms-sender/master.svg)](https://scrutinizer-ci.com/g/fxpio/fxp-sms-sender?branch=master)

The Fxp SMS Sender is a powerful system for creating and sending SMS. Like
[Symfony Mailer](https://symfony.com/doc/current/mailer.html) for emails, this library
use the [Symfony Mime](https://symfony.com/doc/current/components/mime.html) to build the
messages. It uses the same logic to create and send the message via several transports.

Features include:

- Create simply a SMS message like an email for Symfony Mailer
- Available transports:
 - Null transport (to not really send the SMS)
 - Failover transport
 - Round Robin transport
- Available 3rd party transports:
  - Amazon AWS with [Fxp Amazon SMS Sender](https://github.com/fxpio/fxp-amazon-sms-sender)
  - Twilio with [Fxp Twilio SMS Sender](https://github.com/fxpio/fxp-twilio-sms-sender)

Documentation
-------------

The bulk of the documentation is stored in the `Resources/doc/index.md`
file in this library:

[Read the Documentation](Resources/doc/index.md)

Installation
------------

All the installation instructions are located in [documentation](Resources/doc/index.md).

License
-------

This library is under the MIT license. See the complete license in the library:

[LICENSE](LICENSE)

About
-----

Fxp SMS Sender is a [François Pluchino](https://github.com/francoispluchino) initiative.
See also the list of [contributors](https://github.com/fxpio/fxp-sms-sender/graphs/contributors).

Reporting an issue or a feature request
---------------------------------------

Issues and feature requests are tracked in the [Github issue tracker](https://github.com/fxpio/fxp-sms-sender/issues).
