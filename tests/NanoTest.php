<?php

declare(strict_types=1);

namespace Caffeine\Money\Tests;

use Caffeine\Money\Nano;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class NanoTest extends TestCase
{
    // -------------------------------------------------------------------------
    //  Factory methods
    // -------------------------------------------------------------------------

    public function testCreatesInstanceFromLocale(): void
    {
        $this->assertSame('en_US', Nano::forLocale('en_US')->getLocale());
    }

    public function testCreatesInstanceFromCurrencyCode(): void
    {
        $this->assertSame('USD', Nano::forCurrency('USD')->getCurrency());
    }

    public function testCreatesInstanceFromCountryCode(): void
    {
        $this->assertSame('US', Nano::forCountry('US')->getCountry());
    }

    public function testThrowsForUnknownCurrencyCode(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Nano::forCurrency('XXX');
    }

    public function testThrowsForLocaleWithoutRegion(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Nano::forLocale('en');
    }

    public function testThrowsForLocaleWithUnknownRegion(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Nano::forLocale('en_XX');
    }

    public function testAcceptsLowercaseCountryCode(): void
    {
        $this->assertSame('US', Nano::forCountry('us')->getCountry());
    }

    public function testAcceptsLowercaseCurrencyCode(): void
    {
        $this->assertSame('USD', Nano::forCurrency('usd')->getCurrency());
    }

    public function testThrowsForUnknownCountryCode(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Nano::forCountry('XX');
    }

    public function testDetectsLocaleFromAcceptLanguageHeader(): void
    {
        $nano = Nano::detect('en_US', server: ['HTTP_ACCEPT_LANGUAGE' => 'ja-JP']);
        $this->assertStringStartsWith('ja', $nano->getLocale());
    }

    public function testFallsBackToDefaultLocaleWhenHeaderAbsent(): void
    {
        $nano = Nano::detect('en_US', server: []);
        $this->assertSame('en_US', $nano->getLocale());
    }

    // -------------------------------------------------------------------------
    //  Getters
    // -------------------------------------------------------------------------

    public function testReturnsCurrencyCode(): void
    {
        $this->assertSame('USD', Nano::forLocale('en_US')->getCurrency());
        $this->assertSame('JPY', Nano::forLocale('ja_JP')->getCurrency());
    }

    public function testReturnsCountryCode(): void
    {
        $this->assertSame('US', Nano::forLocale('en_US')->getCountry());
        $this->assertSame('JP', Nano::forLocale('ja_JP')->getCountry());
    }

    public function testReturnsTwoFractionDigitsForTwoDecimalCurrency(): void
    {
        $this->assertSame(2, Nano::forLocale('en_US')->getFractionDigits());
    }

    public function testReturnsZeroFractionDigitsForZeroDecimalCurrency(): void
    {
        $this->assertSame(0, Nano::forLocale('ja_JP')->getFractionDigits());
    }

    public function testReturnsThreeFractionDigitsForThreeDecimalCurrency(): void
    {
        $this->assertSame(3, Nano::forCurrency('KWD')->getFractionDigits());
    }

    // -------------------------------------------------------------------------
    //  fromMajor / toMajor
    // -------------------------------------------------------------------------

    public function testConvertsMajorAmountToNanosAndBack(): void
    {
        $nano = Nano::forLocale('en_US');
        $nanos = $nano->fromMajor(19.99);
        $this->assertSame(19_990_000_000, $nanos);
        $this->assertSame(19.99, $nano->toMajor($nanos));
    }

    public function testConvertsThreeDecimalCurrencyMajorAmountToNanosAndBack(): void
    {
        $nano = Nano::forCurrency('KWD');
        $nanos = $nano->fromMajor(1.500);
        $this->assertSame(1_500_000_000, $nanos);
        $this->assertSame(1.5, $nano->toMajor($nanos));
    }

    public function testConvertsZeroDecimalCurrencyMajorAmountToNanos(): void
    {
        $nano = Nano::forLocale('ja_JP');
        $this->assertSame(2_000_000_000_000, $nano->fromMajor(2000));
        $this->assertSame(2000.0, $nano->toMajor(2_000_000_000_000));
    }

    public function testAcceptsIntegerMajorAmount(): void
    {
        $this->assertSame(100_000_000_000, Nano::forLocale('en_US')->fromMajor(100));
    }

    public function testParsesLocaleFormattedStringAsMajorAmount(): void
    {
        $this->assertSame(19_990_000_000, Nano::forLocale('en_US')->fromMajor('$19.99'));
    }

    public function testThrowsWhenStringMajorAmountCannotBeParsed(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Nano::forLocale('en_US')->fromMajor('not-a-number');
    }

    // -------------------------------------------------------------------------
    //  fromMinor / toMinor
    // -------------------------------------------------------------------------

    public function testConvertsCentsToNanosAndBack(): void
    {
        $nano = Nano::forLocale('en_US');
        $nanos = $nano->fromMinor(1999);
        $this->assertSame(19_990_000_000, $nanos);
        $this->assertSame(1999, $nano->toMinor($nanos));
    }

    public function testConvertsThreeDecimalCurrencyMinorUnitsToNanosAndBack(): void
    {
        $nano = Nano::forCurrency('KWD');
        $nanos = $nano->fromMinor(1500);
        $this->assertSame(1_500_000_000, $nanos);
        $this->assertSame(1500, $nano->toMinor($nanos));
    }

    // -------------------------------------------------------------------------
    //  toNano
    // -------------------------------------------------------------------------

    public function testConvertsRawValueToNanosWithoutCurrencySnapping(): void
    {
        $this->assertSame(1_500_000_000, Nano::forLocale('en_US')->toNano(1.5));
    }

    // -------------------------------------------------------------------------
    //  Snap operations
    // -------------------------------------------------------------------------

    public function testSnapsNanosToNearestMinorUnitBoundary(): void
    {
        $this->assertSame(20_000_000_000, Nano::forLocale('en_US')->snapToMinor(19_995_000_000));
    }

    public function testSnapsToThreeDecimalMinorUnitBoundary(): void
    {
        $this->assertSame(1_501_000_000, Nano::forCurrency('KWD')->snapToMinor(1_500_500_000));
    }

    public function testSnapsFloatNanoValueToNearestIntegerNano(): void
    {
        $this->assertSame(1_000_000_001, Nano::forLocale('en_US')->snapToNano(1_000_000_000.6));
    }

    // -------------------------------------------------------------------------
    //  Formatting
    // -------------------------------------------------------------------------

    public function testFormatsNanosAsCurrencyString(): void
    {
        $this->assertSame('$19.99', Nano::forLocale('en_US')->formatCurrency(19_990_000_000));
    }

    public function testFormatsNanosAsDecimalStringWithoutCurrencySymbol(): void
    {
        $this->assertSame('19.99', Nano::forLocale('en_US')->formatDecimal(19_990_000_000));
    }

    public function testFormatsZeroDecimalCurrencyCorrectly(): void
    {
        $this->assertSame('￥2,000', Nano::forLocale('ja_JP')->formatCurrency(2_000_000_000_000));
    }

    public function testFormatDecimalRespectsZeroDecimalCurrency(): void
    {
        $this->assertSame('2,000', Nano::forLocale('ja_JP')->formatDecimal(2_000_000_000_000));
    }

    // -------------------------------------------------------------------------
    //  Rounding mode overrides
    // -------------------------------------------------------------------------

    public function testFromMajorRespectsPerCallRoundingMode(): void
    {
        $nano = Nano::forLocale('en_US');
        $this->assertSame(20_000_000_000, $nano->fromMajor(19.995, PHP_ROUND_HALF_UP));
        $this->assertSame(19_990_000_000, $nano->fromMajor(19.995, PHP_ROUND_HALF_DOWN));
    }

    public function testToMajorRespectsPerCallRoundingMode(): void
    {
        $nano = Nano::forLocale('en_US');
        $this->assertSame(20.0, $nano->toMajor(19_995_000_000, PHP_ROUND_HALF_UP));
        $this->assertSame(19.99, $nano->toMajor(19_995_000_000, PHP_ROUND_HALF_DOWN));
    }

    public function testToMinorRespectsPerCallRoundingMode(): void
    {
        $nano = Nano::forLocale('en_US');
        $this->assertSame(2000, $nano->toMinor(19_995_000_000, PHP_ROUND_HALF_UP));
        $this->assertSame(1999, $nano->toMinor(19_995_000_000, PHP_ROUND_HALF_DOWN));
    }

    public function testToNanoRespectsPerCallRoundingMode(): void
    {
        $nano = Nano::forLocale('en_US');
        $this->assertSame(2, $nano->toNano(0.0000000015, PHP_ROUND_HALF_UP));
        $this->assertSame(1, $nano->toNano(0.0000000015, PHP_ROUND_HALF_DOWN));
    }

    public function testSnapToMinorRespectsPerCallRoundingMode(): void
    {
        $nano = Nano::forLocale('en_US');
        $this->assertSame(20_000_000_000, $nano->snapToMinor(19_995_000_000, PHP_ROUND_HALF_UP));
        $this->assertSame(19_990_000_000, $nano->snapToMinor(19_995_000_000, PHP_ROUND_HALF_DOWN));
    }

    public function testSnapToNanoRespectsPerCallRoundingMode(): void
    {
        $nano = Nano::forLocale('en_US');
        $this->assertSame(2, $nano->snapToNano(1.5, PHP_ROUND_HALF_UP));
        $this->assertSame(1, $nano->snapToNano(1.5, PHP_ROUND_HALF_DOWN));
    }
}
