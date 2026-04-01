<?php

declare(strict_types=1);

use Caffeine\Money\Nano;

// -------------------------------------------------------------------------
//  Factory methods
// -------------------------------------------------------------------------

it('creates an instance from a locale', function (): void {
    $nano = Nano::forLocale('en_US');
    expect($nano->getLocale())->toBe('en_US');
});

it('creates an instance from a currency code', function (): void {
    $nano = Nano::forCurrency('USD');
    expect($nano->getCurrency())->toBe('USD');
});

it('creates an instance from a country code', function (): void {
    $nano = Nano::forCountry('US');
    expect($nano->getCountry())->toBe('US');
});

it('throws for an unknown currency code', function (): void {
    Nano::forCurrency('XXX');
})->throws(InvalidArgumentException::class);

it('throws for an unknown country code', function (): void {
    Nano::forCountry('XX');
})->throws(InvalidArgumentException::class);

it('detects locale from accept-language header', function (): void {
    $nano = Nano::detect('en_US', server: ['HTTP_ACCEPT_LANGUAGE' => 'ja-JP']);
    expect($nano->getLocale())->toStartWith('ja');
});

it('falls back to default locale when accept-language header is absent', function (): void {
    $nano = Nano::detect('en_US', server: []);
    expect($nano->getLocale())->toBe('en_US');
});

// -------------------------------------------------------------------------
//  Getters
// -------------------------------------------------------------------------

it('returns the currency code', function (): void {
    expect(Nano::forLocale('en_US')->getCurrency())->toBe('USD');
    expect(Nano::forLocale('ja_JP')->getCurrency())->toBe('JPY');
});

it('returns the country code', function (): void {
    expect(Nano::forLocale('en_US')->getCountry())->toBe('US');
    expect(Nano::forLocale('ja_JP')->getCountry())->toBe('JP');
});

it('returns the correct fraction digits for a two-decimal currency', function (): void {
    expect(Nano::forLocale('en_US')->getFractionDigits())->toBe(2);
});

it('returns zero fraction digits for a zero-decimal currency', function (): void {
    expect(Nano::forLocale('ja_JP')->getFractionDigits())->toBe(0);
});

it('returns 3 fraction digits for a three-decimal currency', function (): void {
    expect(Nano::forCurrency('KWD')->getFractionDigits())->toBe(3);
});

// -------------------------------------------------------------------------
//  fromMajor / toMajor
// -------------------------------------------------------------------------

it('converts a major amount to nanos and back', function (): void {
    $nano = Nano::forLocale('en_US');
    $nanos = $nano->fromMajor(19.99);
    expect($nanos)->toBe(19_990_000_000);
    expect($nano->toMajor($nanos))->toBe(19.99);
});

it('converts a three-decimal currency major amount to nanos and back', function (): void {
    $nano = Nano::forCurrency('KWD');
    $nanos = $nano->fromMajor(1.500);
    expect($nanos)->toBe(1_500_000_000);
    expect($nano->toMajor($nanos))->toBe(1.5);
});

it('converts three-decimal currency minor units (fils) to nanos and back', function (): void {
    $nano = Nano::forCurrency('KWD');
    $nanos = $nano->fromMinor(1500);        // 1500 fils = 1.500 KWD
    expect($nanos)->toBe(1_500_000_000);
    expect($nano->toMinor($nanos))->toBe(1500);
});

it('snaps to three-decimal minor-unit boundary', function (): void {
    $nano = Nano::forCurrency('KWD');
    // 1.5005 KWD — snaps to 1.501 (rounds up at 4th decimal)
    expect($nano->snapToMinor(1_500_500_000))->toBe(1_501_000_000);
});

it('converts a zero-decimal currency major amount to nanos', function (): void {
    $nano = Nano::forLocale('ja_JP');
    expect($nano->fromMajor(2000))->toBe(2_000_000_000_000);
    expect($nano->toMajor(2_000_000_000_000))->toBe(2000.0);
});

it('accepts an integer major amount', function (): void {
    $nano = Nano::forLocale('en_US');
    expect($nano->fromMajor(100))->toBe(100_000_000_000);
});

it('parses a locale-formatted string as a major amount', function (): void {
    $nano = Nano::forLocale('en_US');
    expect($nano->fromMajor('$19.99'))->toBe(19_990_000_000);
});

it('throws when a string major amount cannot be parsed', function (): void {
    Nano::forLocale('en_US')->fromMajor('not-a-number');
})->throws(InvalidArgumentException::class);

// -------------------------------------------------------------------------
//  fromMinor / toMinor
// -------------------------------------------------------------------------

it('converts cents to nanos and back', function (): void {
    $nano = Nano::forLocale('en_US');
    $nanos = $nano->fromMinor(1999);
    expect($nanos)->toBe(19_990_000_000);
    expect($nano->toMinor($nanos))->toBe(1999);
});

// -------------------------------------------------------------------------
//  toNano
// -------------------------------------------------------------------------

it('converts a raw value to nanos without currency snapping', function (): void {
    $nano = Nano::forLocale('en_US');
    expect($nano->toNano(1.5))->toBe(1_500_000_000);
});

// -------------------------------------------------------------------------
//  Snap operations
// -------------------------------------------------------------------------

it('snaps nanos to the nearest minor-unit boundary', function (): void {
    $nano = Nano::forLocale('en_US');
    // 19_995_000_000 = $19.995 — snaps to $20.00
    expect($nano->snapToMinor(19_995_000_000))->toBe(20_000_000_000);
});


it('snaps a float nano value to the nearest integer nano', function (): void {
    $nano = Nano::forLocale('en_US');
    expect($nano->snapToNano(1_000_000_000.6))->toBe(1_000_000_001);
});

// -------------------------------------------------------------------------
//  Formatting
// -------------------------------------------------------------------------

it('formats nanos as a currency string', function (): void {
    expect(Nano::forLocale('en_US')->formatCurrency(19_990_000_000))->toBe('$19.99');
});

it('formats nanos as a decimal string without currency symbol', function (): void {
    expect(Nano::forLocale('en_US')->formatDecimal(19_990_000_000))->toBe('19.99');
});

it('formats a zero-decimal currency correctly', function (): void {
    expect(Nano::forLocale('ja_JP')->formatCurrency(2_000_000_000_000))->toBe('￥2,000');
});
