# Shortcode

[![Build Status](https://travis-ci.org/thunderer/Shortcode.png?branch=master)](https://travis-ci.org/thunderer/Shortcode)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/5235d5e3-d112-48df-bc07-d4555aef293d/mini.png)](https://insight.sensiolabs.com/projects/5235d5e3-d112-48df-bc07-d4555aef293d)
[![License](https://poser.pugx.org/thunderer/shortcode/license.svg)](https://packagist.org/packages/thunderer/shortcode)
[![Latest Stable Version](https://poser.pugx.org/thunderer/shortcode/v/stable.svg)](https://packagist.org/packages/thunderer/shortcode)
[![Dependency Status](https://www.versioneye.com/user/projects/551d5385971f7847ca000002/badge.svg?style=flat)](https://www.versioneye.com/user/projects/551d5385971f7847ca000002)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/thunderer/Shortcode/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/thunderer/Shortcode/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/thunderer/Shortcode/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/thunderer/Shortcode/?branch=master)
[![Code Climate](https://codeclimate.com/github/thunderer/Shortcode/badges/gpa.svg)](https://codeclimate.com/github/thunderer/Shortcode)

Shortcode is a framework and library agnostic engine for interpreting and replacing "shortcodes" (small script-like text fragments) using dynamic callbacks. It can be used to create dynamic content replacement mechanism that is usable even by non-technical people without much training. Usual syntax of shortcodes is as shown in the examples below:

```
[shortcode]
[shortcode argument="value"]
[shortcode novalue argument=simple other="complex value"]
[shortcode]content[/shortcode]
[shortcode argument="value"]content[/shortcode]
```

All those variants (and many more, see the tests) are supported.

## Requirements

No required dependencies, only PHP >=5.4

> PHP 5.3 is marked as minimal version in Composer, but it won't work because parsing mechanism relies on passing context of $this into closures (calling object methods inside them). This can be fixed, but since PHP 5.3 has already reached its EOL months ago I really advise you to upgrade to latest stable version. There are many performance improvements and new features that are extremely useful.

## Installation

This library is registered on Packagist, so you can just execute

```
composer require thunderer/shortcode
```

in your terminal or manually update your `composer.json` with

```
(...)
"require": {
    "thunderer/shortcode": "dev-master"
}
(...)
```

and run `composer install` or `composer update` afterwards. If you're not using Composer, then you can download sources from GitHub and load them as you wish in your project. But really, please use Composer.

## Usage

Create `Shortcode` class instance, register required shortcodes handlers and pass your strings to be parsed.

```php
use Thunder\Shortcode\Shortcode;

$shortcode = new Shortcode();
$shortcode->addCode('sample', function($name, array $args, $content) {
    return json_encode(array(
        'name' => $name,
        'args' => $args,
        'content' => $content,
        ));
    });
    
// this will produce JSON encoded parsed data of given shortcode, eg.:
// {"name":"sample","args":{"argument":"value"},"content":"content"}
echo $shortcode->parse('[sample argument=value]content[/sample]');
```

If parser finds shortcode that is not supported (no registered handler) it will return whole block without any modification. When opening and closing shortcode do not match, parser ignores closing fragment and considers it as a self-closing shortcode.

## License

See LICENSE file in the main directory of this library.
