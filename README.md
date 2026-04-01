# Nano

Nano-precision currency conversion and locale-aware formatting for PHP.

Converts monetary values to and from integer nanos (10⁹ nanos per major unit), minimising floating-point rounding errors during arithmetic. Wraps PHP's `ext-intl` `NumberFormatter` for locale-aware output. You hold the nano integers — `Nano` just converts and formats them.

> **If your project can support it, use a full money library instead — [`brick/money`](https://github.com/brick/money) or [`moneyphp/money`](https://github.com/moneyphp/money).** This library exists for situations where adopting one of those is not practical right away.

## Requirements

- PHP 8.4+
- `ext-intl`

## Installation

```bash
composer require caffeine/money
```

## Usage

### Creating an instance

```php
use Caffeine\Money\Nano;

// From a locale string
$nano = Nano::forLocale('en_US');

// From an ISO 4217 currency code
$nano = Nano::forCurrency('JPY');

// From an ISO 3166-1 alpha-2 country code
$nano = Nano::forCountry('DE');

// Auto-detect from the HTTP Accept-Language header
$nano = Nano::detect(defaultLocale: 'en_US');
```

### Converting to nanos

```php
$nano = Nano::forLocale('en_US');

$nano->fromMajor(19.99);    // 19_990_000_000  (dollars → nanos)
$nano->fromMajor('$19.99'); // 19_990_000_000  (locale-formatted string)
$nano->fromMinor(1999);     // 19_990_000_000  (cents → nanos)
$nano->toNano(1.5);         // 1_500_000_000   (raw, no currency snapping)
```

### Converting from nanos

```php
$nano->toMajor(19_990_000_000); // 19.99  (float)
$nano->toMinor(19_990_000_000); // 1999   (int)
```

### Formatting

```php
Nano::forLocale('en_US')->formatCurrency(19_990_000_000); // "$19.99"
Nano::forLocale('en_US')->formatDecimal(19_990_000_000);  // "19.99"
Nano::forLocale('ja_JP')->formatCurrency(2_000_000_000_000); // "￥2,000"
```

### Rounding

The default rounding mode is `RoundingMode::HalfAwayFromZero`. Override per-instance or per-call:

```php
use RoundingMode;

$nano = Nano::forLocale('en_US', RoundingMode::HalfEven);

$nano->fromMajor(19.995, RoundingMode::HalfUp);
$nano->snapToMinor(19_995_000_000, RoundingMode::HalfDown);
```

### Snapping

`snapToMinor` aligns a nano value to the nearest minor-unit boundary (e.g. cent for USD, fil for KWD):

```php
$nano = Nano::forLocale('en_US');
$nano->snapToMinor(19_995_000_000); // 20_000_000_000  ($19.995 → $20.00)

$nano = Nano::forCurrency('KWD'); // 3 decimal places
$nano->snapToMinor(1_500_500_000); // 1_501_000_000   (1.5005 → 1.501 KWD)
```

`snapToNano` rounds a float nano value to the nearest integer nano — useful after arithmetic that may produce fractional nanos:

```php
$nano->snapToNano(1_000_000_000.6); // 1_000_000_001
```

### Getters

```php
$nano->getLocale();         // "en_US"
$nano->getCurrency();       // "USD"
$nano->getCountry();        // "US"
$nano->getFractionDigits(); // 2
$nano->getRoundingMode();   // RoundingMode::HalfAwayFromZero
```

## Safe operating range

Nanos are stored as PHP `int`. On 64-bit systems the safe range is ±9,223,372,036 major units (~±9.2 billion). Integer overflow beyond this range will occur silently. A 64-bit runtime is assumed.

## License

[0BSD](https://opensource.org/licenses/0BSD) — do whatever you want, no attribution required.
