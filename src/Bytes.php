<?php

/*
 * This file is part of the zenstruck/bytes package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @immutable
 */
final class Bytes implements \Stringable
{
    private const DECIMAL = 1000;
    private const BINARY = 1024;
    private const DECIMAL_UNITS = ['b' => 'B', 'kb' => 'kB', 'mb' => 'MB', 'gb' => 'GB', 'tb' => 'TB', 'pb' => 'PB', 'eb' => 'EB', 'zb' => 'ZB', 'yb' => 'YB'];
    private const BINARY_UNITS = ['b' => 'B', 'kib' => 'KiB', 'mib' => 'MiB', 'gib' => 'GiB', 'tib' => 'TiB', 'pib' => 'PiB', 'eib' => 'EiB', 'zib' => 'ZiB', 'yib' => 'YiB'];
    private const UNIT_MAP = [
        'b' => [0, self::DECIMAL],
        'kB' => [1, self::DECIMAL],
        'KiB' => [1, self::BINARY],
        'MB' => [2, self::DECIMAL],
        'MiB' => [2, self::BINARY],
        'GB' => [3, self::DECIMAL],
        'GiB' => [3, self::BINARY],
        'TB' => [4, self::DECIMAL],
        'TiB' => [4, self::BINARY],
        'PB' => [5, self::DECIMAL],
        'PiB' => [5, self::BINARY],
        'EB' => [6, self::DECIMAL],
        'EiB' => [6, self::BINARY],
        'ZB' => [7, self::DECIMAL],
        'ZiB' => [7, self::BINARY],
        'YB' => [8, self::DECIMAL],
        'YiB' => [8, self::BINARY],
    ];
    private const ALTERNATE_MAP = [
        'k' => 'kB',
        'ki' => 'KiB',
        'm' => 'MB',
        'mi' => 'MiB',
        'g' => 'GB',
        'gi' => 'GiB',
        't' => 'TB',
        'ti' => 'TiB',
        'p' => 'PB',
        'pi' => 'PiB',
        'e' => 'EB',
        'ei' => 'EiB',
        'z' => 'ZB',
        'zi' => 'ZiB',
        'y' => 'YB',
        'yi' => 'YiB',
    ];

    private int $system = self::DECIMAL;

    public function __construct(private int $value)
    {
    }

    public function __toString(): string
    {
        $i = 0;
        $units = \array_values(self::DECIMAL === $this->system ? self::DECIMAL_UNITS : self::BINARY_UNITS);
        $quantity = (float) $this->value;

        while (($quantity / $this->system) >= 1 && $i < (\count($units) - 1)) {
            $quantity /= $this->system;
            ++$i;
        }

        return \sprintf($quantity === \floor($quantity) ? '%d %s' : '%.2f %s', $quantity, $units[$i]);
    }

    public static function parse(string|int|float|self $value): self
    {
        if ($value instanceof self) {
            return $value;
        }

        if (\is_numeric($value)) {
            return new self((int) $value);
        }

        if (!\preg_match('#^(-?[\d,]+(.[\d,]+)?)([\s\-_]+)?(.+)$#', \trim($value), $matches)) {
            throw new \InvalidArgumentException(\sprintf('Could not parse "%s" into bytes.', $value));
        }

        return new self(self::toBytes((float) \str_replace(',', '', $matches[1]), $matches[4]));
    }

    public function value(): int
    {
        return $this->value;
    }

    public function asBinary(): self
    {
        $clone = clone $this;
        $clone->system = self::BINARY;

        return $clone;
    }

    public function asDecimal(): self
    {
        $clone = clone $this;
        $clone->system = self::DECIMAL;

        return $clone;
    }

    private static function toBytes(float $value, string $units): int
    {
        $lower = \mb_strtolower($units);
        $units = self::BINARY_UNITS[$lower] ?? self::DECIMAL_UNITS[$lower] ?? self::ALTERNATE_MAP[$lower] ?? throw new \InvalidArgumentException(\sprintf('"%s" is an invalid informational unit. Valid units: %s.', $units, \implode(', ', \array_merge(self::DECIMAL_UNITS, self::BINARY_UNITS))));

        if ('B' === $units) {
            return (int) $value;
        }

        [$multiplier, $system] = self::UNIT_MAP[$units];

        return (int) \ceil($value * $system ** $multiplier);
    }
}
