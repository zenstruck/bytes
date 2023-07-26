<?php

/*
 * This file is part of the zenstruck/bytes package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Tests;

use PHPUnit\Framework\TestCase;
use Zenstruck\Bytes;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class BytesTest extends TestCase
{
    /**
     * @test
     * @dataProvider parseProvider
     */
    public function parse(mixed $value, int $expected): void
    {
        $this->assertSame($expected, Bytes::parse($value)->value());
    }

    public static function parseProvider(): iterable
    {
        yield ['10', 10];
        yield [10, 10];
        yield [0, 0];
        yield [new Bytes(10), 10];
        yield [10.9, 11];
        yield [10.1, 11];
        yield ['10.9', 11];
        yield ['10b', 10];
        yield ['10  b', 10];
        yield ['10  B  ', 10];
        yield ['10.2  b', 11];
        yield ['10.9  B  ', 11];
        yield ['1000  B  ', 1000];
        yield ['1,000  B  ', 1000];
        yield ['1,000,000  B  ', 1000000];
        yield ['1kb', 1000];
        yield ['1kib', 1024];
        yield ['1.21mb', 1210000];
        yield ['1mib', 1048576];
        yield ['1.21mib', 1268777];
        yield ['1.21mb', 1210000];
        yield ['1.13gb', 1130000000];
        yield ['1.13gib', 1213328262];
        yield ['1.42tb', 1420000000000];
        yield ['1.42tib', 1561306511442];
    }

    /**
     * @test
     * @dataProvider toStringProvider
     */
    public function to_string($value, string $expected, bool $asBinary = false): void
    {
        $bytes = Bytes::parse($value);

        if ($asBinary) {
            $bytes = $bytes->asBinary();
        }

        $this->assertSame($expected, (string) $bytes);
    }

    public static function toStringProvider(): iterable
    {
        yield [17, '17 B'];
        yield [0, '0 B'];
        yield [999, '999 B'];
        yield [1000, '1 kB'];
        yield [17000, '17 kB'];
        yield [1420000000000, '1.42 TB'];
        yield [1000000000000, '1 TB'];
        yield ['17 kB', '17 kB'];
        yield ['17.45 kB', '17.45 kB'];
        yield ['17.4567 kB', '17.46 kB'];
        yield ['17 KiB', '17.41 kB'];

        yield [17, '17 B', true];
        yield [999, '999 B', true];
        yield [1000, '1000 B', true];
        yield [1024, '1 KiB', true];
        yield [17000, '16.60 KiB', true];
        yield [1420000000000, '1.29 TiB', true];
        yield [1000000000000, '931.32 GiB', true];
    }

    /**
     * @test
     */
    public function to_decimal(): void
    {
        $bytes = Bytes::parse('1 kB')->asBinary();

        $this->assertSame('1000 B', (string) $bytes);
        $this->assertSame('1 kB', (string) $bytes->asDecimal());
    }

    /**
     * @test
     */
    public function invalid_parse(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Bytes::parse('foo');
    }

    /**
     * @test
     */
    public function invalid_units(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Bytes::parse('42 foo');
    }

    /**
     * @test
     */
    public function comparison(): void
    {
        $this->assertTrue(Bytes::parse(5)->isEqualTo(5));
        $this->assertTrue(Bytes::parse(5)->isLessThan(6));
        $this->assertTrue(Bytes::parse(5)->isLessThanOrEqualTo(6));
        $this->assertTrue(Bytes::parse(5)->isGreaterThan(4));
        $this->assertTrue(Bytes::parse(5)->isGreaterThanOrEqualTo(4));

        $this->assertFalse(Bytes::parse(5)->isEqualTo(6));
        $this->assertFalse(Bytes::parse(5)->isLessThan(4));
        $this->assertFalse(Bytes::parse(5)->isLessThanOrEqualTo(4));
        $this->assertFalse(Bytes::parse(5)->isGreaterThan(5));
        $this->assertFalse(Bytes::parse(5)->isGreaterThanOrEqualTo(6));
    }

    /**
     * @test
     */
    public function arithmetic(): void
    {
        $this->assertSame(11, Bytes::parse(5)->add(6)->value());
        $this->assertSame(1, Bytes::parse(6)->subtract(5)->value());
    }

    /**
     * @test
     */
    public function json_serialize(): void
    {
        $this->assertSame('{"foo":11}', \json_encode(['foo' => new Bytes(11)]));
    }

    /**
     * @test
     * @dataProvider convertToProvider
     */
    public function convert_to($value, $units, $expected): void
    {
        $bytes = Bytes::parse($value);
        $converted = $bytes->to($units);

        $this->assertSame($bytes->value(), $converted->value());
        $this->assertSame($expected, (string) $converted);
    }

    public static function convertToProvider(): iterable
    {
        yield [100000, 'B', '100000 B'];
        yield [100, 'kB', '0.10 kB'];
        yield ['1.29 TiB', 'GB', '1418.37 GB'];
    }

    /**
     * @test
     */
    public function convert_back_to_humanize(): void
    {
        $this->assertSame('1.42 TB', (string) Bytes::parse(1420000000000)->to('GiB')->humanize());
    }

    /**
     * @test
     */
    public function invalid_convert_to(): void
    {
        $bytes = new Bytes(1000);

        $this->expectException(\InvalidArgumentException::class);

        $bytes->to('invalid');
    }

    /**
     * @test
     */
    public function custom_formatter(): void
    {
        $bytes = Bytes::parse('1418.37 GB');

        $this->assertSame('1.4184 TB', $bytes->format('%.4f %s'));
    }
}
