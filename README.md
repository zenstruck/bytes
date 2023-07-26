# zenstruck/bytes

[![CI](https://github.com/zenstruck/bytes/actions/workflows/ci.yml/badge.svg)](https://github.com/zenstruck/bytes/actions/workflows/ci.yml)
[![codecov](https://codecov.io/gh/zenstruck/bytes/branch/1.x/graph/badge.svg?token=3jVKMegHpD)](https://codecov.io/gh/zenstruck/bytes)

A value object to parse, manipulate, humanize, and format bytes.

# Installation

```bash
composer require zenstruck/bytes
```

# Usage

Create a `Zenstruck\Bytes` object and access its value:

```php
use Zenstruck\Bytes;

$bytes = Bytes::parse(1024); // create from number of bytes as integer
$bytes->value(); // (int) 1024

$bytes = Bytes::parse('1.54kb'); // create from a quantity suffixed by a valid informational unit
$bytes->value(); // (int) 1540

$bytes = Bytes::parse('1.54 KiB'); // can use binary informational units
$bytes->value(); // (int) 1577
```

## Formatting

By default, when a `Zenstruck\Bytes` object is converted to a string, it _humanizes_ the
to make it easier to read.

```php
use Zenstruck\Bytes;

(string) Bytes::parse(389789364783); // "389.79 GB"
```

You can customize the formatting:

```php
use Zenstruck\Bytes;

Bytes::parse(389789364783)->format('%.4f%s'); // "389.7894GB"
```

### Unit Conversion

If you'd like a consistent unit to be used when formatting, use the `to()` method
with a valid informational unit:

```php
use Zenstruck\Bytes;

(string) Bytes::parse(389789364783)->to('mib'); // "371732.11 MiB"
```

## Arithmetic

Perform _add_ and _subtract_ operations:

```php
use Zenstruck\Bytes;

/** @var Bytes $first */
/** @var Bytes $second */
/** @var Bytes $third */

$result = $first // $result instanceof Bytes
    ->add($second) // add another Bytes object
    ->add(500) // add specific amount of bytes
    ->add('2.1 MB') // parse and add
    ->subtract($third) // subtract another Bytes object
    ->subtract(100) // subtract specific amount of bytes
    ->subtract('100 kib') // parse and subtract
;
```

> **Note**: These operations are immutable.

## Comparisons

You can compare `Zenstruck\Byte` objects:

```php
use Zenstruck\Bytes;

/** @var Bytes $bytes */
/** @var Bytes $another */

$bytes->isEqualTo(100); // bool
$bytes->isEqualTo('1.1kb'); // bool
$bytes->isEqualTo($another); // bool

$bytes->isGreaterThan(100); // bool
$bytes->isGreaterThan('1.1kb'); // bool
$bytes->isGreaterThan($another); // bool

$bytes->isLessThan(100); // bool
$bytes->isLessThan('1.1kb'); // bool
$bytes->isLessThan($another); // bool

$bytes->isGreaterThanOrEqualTo(100); // bool
$bytes->isGreaterThanOrEqualTo('1.1kb'); // bool
$bytes->isGreaterThanOrEqualTo($another); // bool

$bytes->isLessThanOrEqualTo(100); // bool
$bytes->isLessThanOrEqualTo('1.1kb'); // bool
$bytes->isLessThanOrEqualTo($another); // bool
```
