<?php

declare(strict_types=1);

namespace Caffeine\Money;

use InvalidArgumentException;
use NumberFormatter;

/**
 * Safe operating range: ±9,223,372,036 major units (~±9.2 billion) on 64-bit systems.
 */
class Nano
{
    private const NANOS = 1_000_000_000;

    // @mago-format-ignore-start
    /** @var array<string, string> */
    private const ISO_3166_ALPHA_2_MAP = [
        'AD' => 'ca_AD', 'AE' => 'ar_AE', 'AF' => 'fa_AF', 'AG' => 'en_AG', 'AI' => 'en_AI',
        'AL' => 'sq_AL', 'AM' => 'hy_AM', 'AO' => 'pt_AO', 'AR' => 'es_AR', 'AT' => 'de_AT',
        'AU' => 'en_AU', 'AW' => 'nl_AW', 'AZ' => 'az_AZ', 'BA' => 'bs_BA', 'BB' => 'en_BB',
        'BD' => 'bn_BD', 'BE' => 'nl_BE', 'BF' => 'fr_BF', 'BG' => 'bg_BG', 'BH' => 'ar_BH',
        'BI' => 'fr_BI', 'BJ' => 'fr_BJ', 'BN' => 'ms_BN', 'BO' => 'es_BO', 'BR' => 'pt_BR',
        'BS' => 'en_BS', 'BT' => 'dz_BT', 'BW' => 'en_BW', 'BY' => 'be_BY', 'BZ' => 'en_BZ',
        'CA' => 'en_CA', 'CD' => 'fr_CD', 'CF' => 'fr_CF', 'CG' => 'fr_CG', 'CH' => 'de_CH',
        'CI' => 'fr_CI', 'CL' => 'es_CL', 'CM' => 'fr_CM', 'CN' => 'zh_CN', 'CO' => 'es_CO',
        'CR' => 'es_CR', 'CU' => 'es_CU', 'CV' => 'pt_CV', 'CW' => 'nl_CW', 'CY' => 'el_CY',
        'CZ' => 'cs_CZ', 'DE' => 'de_DE', 'DJ' => 'fr_DJ', 'DK' => 'da_DK', 'DM' => 'en_DM',
        'DO' => 'es_DO', 'DZ' => 'ar_DZ', 'EC' => 'es_EC', 'EE' => 'et_EE', 'EG' => 'ar_EG',
        'ER' => 'ti_ER', 'ES' => 'es_ES', 'ET' => 'am_ET', 'FI' => 'fi_FI', 'FJ' => 'en_FJ',
        'FK' => 'en_FK', 'FM' => 'en_FM', 'FR' => 'fr_FR', 'GA' => 'fr_GA', 'GB' => 'en_GB',
        'GD' => 'en_GD', 'GE' => 'ka_GE', 'GH' => 'en_GH', 'GM' => 'en_GM', 'GN' => 'fr_GN',
        'GQ' => 'es_GQ', 'GR' => 'el_GR', 'GT' => 'es_GT', 'GW' => 'pt_GW', 'GY' => 'en_GY',
        'HN' => 'es_HN', 'HR' => 'hr_HR', 'HT' => 'fr_HT', 'HU' => 'hu_HU', 'ID' => 'id_ID',
        'IE' => 'en_IE', 'IL' => 'he_IL', 'IN' => 'en_IN', 'IQ' => 'ar_IQ', 'IR' => 'fa_IR',
        'IS' => 'is_IS', 'IT' => 'it_IT', 'JM' => 'en_JM', 'JO' => 'ar_JO', 'JP' => 'ja_JP',
        'KE' => 'sw_KE', 'KG' => 'ky_KG', 'KH' => 'km_KH', 'KI' => 'en_KI', 'KM' => 'ar_KM',
        'KN' => 'en_KN', 'KP' => 'ko_KP', 'KR' => 'ko_KR', 'KW' => 'ar_KW', 'KY' => 'en_KY',
        'KZ' => 'kk_KZ', 'LA' => 'lo_LA', 'LB' => 'ar_LB', 'LC' => 'en_LC', 'LI' => 'de_LI',
        'LK' => 'si_LK', 'LR' => 'en_LR', 'LS' => 'en_LS', 'LT' => 'lt_LT', 'LU' => 'fr_LU',
        'LV' => 'lv_LV', 'LY' => 'ar_LY', 'MA' => 'ar_MA', 'MC' => 'fr_MC', 'MD' => 'ro_MD',
        'ME' => 'sr_ME', 'MG' => 'mg_MG', 'MH' => 'en_MH', 'MK' => 'mk_MK', 'ML' => 'fr_ML',
        'MM' => 'my_MM', 'MN' => 'mn_MN', 'MO' => 'zh_MO', 'MR' => 'ar_MR', 'MT' => 'mt_MT',
        'MU' => 'en_MU', 'MV' => 'dv_MV', 'MW' => 'en_MW', 'MX' => 'es_MX', 'MY' => 'ms_MY',
        'MZ' => 'pt_MZ', 'NA' => 'en_NA', 'NE' => 'fr_NE', 'NG' => 'en_NG', 'NI' => 'es_NI',
        'NL' => 'nl_NL', 'NO' => 'nb_NO', 'NP' => 'ne_NP', 'NR' => 'en_NR', 'NZ' => 'en_NZ',
        'OM' => 'ar_OM', 'PA' => 'es_PA', 'PE' => 'es_PE', 'PG' => 'en_PG', 'PH' => 'en_PH',
        'PK' => 'ur_PK', 'PL' => 'pl_PL', 'PT' => 'pt_PT', 'PW' => 'en_PW', 'PY' => 'es_PY',
        'QA' => 'ar_QA', 'RO' => 'ro_RO', 'RS' => 'sr_RS', 'RU' => 'ru_RU', 'RW' => 'rw_RW',
        'SA' => 'ar_SA', 'SB' => 'en_SB', 'SC' => 'en_SC', 'SD' => 'ar_SD', 'SE' => 'sv_SE',
        'SG' => 'en_SG', 'SI' => 'sl_SI', 'SK' => 'sk_SK', 'SL' => 'en_SL', 'SM' => 'it_SM',
        'SN' => 'fr_SN', 'SO' => 'so_SO', 'SR' => 'nl_SR', 'SS' => 'en_SS', 'ST' => 'pt_ST',
        'SV' => 'es_SV', 'SY' => 'ar_SY', 'SZ' => 'en_SZ', 'TD' => 'fr_TD', 'TG' => 'fr_TG',
        'TH' => 'th_TH', 'TJ' => 'tg_TJ', 'TL' => 'pt_TL', 'TM' => 'tk_TM', 'TN' => 'ar_TN',
        'TO' => 'to_TO', 'TR' => 'tr_TR', 'TT' => 'en_TT', 'TV' => 'en_TV', 'TZ' => 'sw_TZ',
        'UA' => 'uk_UA', 'UG' => 'sw_UG', 'US' => 'en_US', 'UY' => 'es_UY', 'UZ' => 'uz_UZ',
        'VA' => 'it_VA', 'VC' => 'en_VC', 'VE' => 'es_VE', 'VN' => 'vi_VN', 'VU' => 'bi_VU',
        'WS' => 'sm_WS', 'YE' => 'ar_YE', 'ZA' => 'en_ZA', 'ZM' => 'en_ZM', 'ZW' => 'en_ZW',
    ];
    // @mago-format-ignore-end

    // @mago-format-ignore-start
    /** @var array<string, string> */
    private const ISO_4217_MAP = [
        'AED' => 'ar_AE', 'AFN' => 'fa_AF', 'ALL' => 'sq_AL', 'AMD' => 'hy_AM', 'ANG' => 'nl_CW',
        'AOA' => 'pt_AO', 'ARS' => 'es_AR', 'AUD' => 'en_AU', 'AWG' => 'nl_AW', 'AZN' => 'az_AZ',
        'BAM' => 'bs_BA', 'BBD' => 'en_BB', 'BDT' => 'bn_BD', 'BGN' => 'bg_BG', 'BHD' => 'ar_BH',
        'BIF' => 'fr_BI', 'BMD' => 'en_BM', 'BND' => 'ms_BN', 'BOB' => 'es_BO', 'BRL' => 'pt_BR',
        'BSD' => 'en_BS', 'BTN' => 'dz_BT', 'BWP' => 'en_BW', 'BYN' => 'be_BY', 'BZD' => 'en_BZ',
        'CAD' => 'en_CA', 'CDF' => 'fr_CD', 'CHF' => 'de_CH', 'CLP' => 'es_CL', 'CNY' => 'zh_CN',
        'COP' => 'es_CO', 'CRC' => 'es_CR', 'CUP' => 'es_CU', 'CVE' => 'pt_CV', 'CZK' => 'cs_CZ',
        'DJF' => 'fr_DJ', 'DKK' => 'da_DK', 'DOP' => 'es_DO', 'DZD' => 'ar_DZ', 'EGP' => 'ar_EG',
        'ERN' => 'ti_ER', 'ETB' => 'am_ET', 'EUR' => 'de_DE', 'FJD' => 'en_FJ', 'FKP' => 'en_FK',
        'GBP' => 'en_GB', 'GEL' => 'ka_GE', 'GHS' => 'en_GH', 'GIP' => 'en_GI', 'GMD' => 'en_GM',
        'GNF' => 'fr_GN', 'GTQ' => 'es_GT', 'GYD' => 'en_GY', 'HKD' => 'zh_HK', 'HNL' => 'es_HN',
        'HRK' => 'hr_HR', 'HTG' => 'fr_HT', 'HUF' => 'hu_HU', 'IDR' => 'id_ID', 'ILS' => 'he_IL',
        'INR' => 'en_IN', 'IQD' => 'ar_IQ', 'IRR' => 'fa_IR', 'ISK' => 'is_IS', 'JMD' => 'en_JM',
        'JOD' => 'ar_JO', 'JPY' => 'ja_JP', 'KES' => 'sw_KE', 'KGS' => 'ky_KG', 'KHR' => 'km_KH',
        'KMF' => 'ar_KM', 'KPW' => 'ko_KP', 'KRW' => 'ko_KR', 'KWD' => 'ar_KW', 'KYD' => 'en_KY',
        'KZT' => 'kk_KZ', 'LAK' => 'lo_LA', 'LBP' => 'ar_LB', 'LKR' => 'si_LK', 'LRD' => 'en_LR',
        'LSL' => 'en_LS', 'LYD' => 'ar_LY', 'MAD' => 'ar_MA', 'MDL' => 'ro_MD', 'MGA' => 'mg_MG',
        'MKD' => 'mk_MK', 'MMK' => 'my_MM', 'MNT' => 'mn_MN', 'MOP' => 'zh_MO', 'MRU' => 'ar_MR',
        'MUR' => 'en_MU', 'MVR' => 'dv_MV', 'MWK' => 'en_MW', 'MXN' => 'es_MX', 'MYR' => 'ms_MY',
        'MZN' => 'pt_MZ', 'NAD' => 'en_NA', 'NGN' => 'en_NG', 'NIO' => 'es_NI', 'NOK' => 'nb_NO',
        'NPR' => 'ne_NP', 'NZD' => 'en_NZ', 'OMR' => 'ar_OM', 'PAB' => 'es_PA', 'PEN' => 'es_PE',
        'PGK' => 'en_PG', 'PHP' => 'en_PH', 'PKR' => 'ur_PK', 'PLN' => 'pl_PL', 'PYG' => 'es_PY',
        'QAR' => 'ar_QA', 'RON' => 'ro_RO', 'RSD' => 'sr_RS', 'RUB' => 'ru_RU', 'RWF' => 'rw_RW',
        'SAR' => 'ar_SA', 'SBD' => 'en_SB', 'SCR' => 'en_SC', 'SDG' => 'ar_SD', 'SEK' => 'sv_SE',
        'SGD' => 'en_SG', 'SHP' => 'en_SH', 'SLL' => 'en_SL', 'SOS' => 'so_SO', 'SRD' => 'nl_SR',
        'STN' => 'pt_ST', 'SVC' => 'es_SV', 'SYP' => 'ar_SY', 'SZL' => 'en_SZ', 'THB' => 'th_TH',
        'TJS' => 'tg_TJ', 'TMT' => 'tk_TM', 'TND' => 'ar_TN', 'TOP' => 'to_TO', 'TRY' => 'tr_TR',
        'TTD' => 'en_TT', 'TWD' => 'zh_TW', 'TZS' => 'sw_TZ', 'UAH' => 'uk_UA', 'UGX' => 'sw_UG',
        'USD' => 'en_US', 'UYU' => 'es_UY', 'UZS' => 'uz_UZ', 'VES' => 'es_VE', 'VND' => 'vi_VN',
        'VUV' => 'bi_VU', 'WST' => 'sm_WS', 'XAF' => 'fr_CM', 'XCD' => 'en_AG', 'XOF' => 'fr_SN',
        'XPF' => 'fr_PF', 'YER' => 'ar_YE', 'ZAR' => 'en_ZA', 'ZMW' => 'en_ZM', 'ZWL' => 'en_ZW',
    ];
    // @mago-format-ignore-end

    private NumberFormatter $formatter;
    private ?NumberFormatter $decimalFormatter = null;

    public function __construct(
        private string $locale,
        private int $roundingMode = PHP_ROUND_HALF_UP,
    ) {
        $this->formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);
    }

    // -------------------------------------------------------------------------
    //  Factory methods
    // -------------------------------------------------------------------------

    /** @param array|null $server Defaults to $_SERVER. */
    public static function detect(string $fallbackLocale, int $roundingMode = PHP_ROUND_HALF_UP, ?array $server = null): self
    {
        $server ??= $_SERVER;

        $acceptLanguage = (string) ($server['HTTP_ACCEPT_LANGUAGE'] ?? $fallbackLocale);
        $locale = \Locale::acceptFromHttp($acceptLanguage) ?: $fallbackLocale;

        return new self($locale, $roundingMode);
    }

    /** @throws InvalidArgumentException If the country code is not recognised. */
    public static function forCountry(string $country, int $roundingMode = PHP_ROUND_HALF_UP): self
    {
        $locale = static::ISO_3166_ALPHA_2_MAP[strtoupper($country)] ?? null;

        if ($locale === null) {
            throw new InvalidArgumentException(sprintf("No canonical locale found for country '%s'.", $country));
        }

        return new self($locale, $roundingMode);
    }

    /** @throws InvalidArgumentException If the currency code is not recognised. */
    public static function forCurrency(string $currency, int $roundingMode = PHP_ROUND_HALF_UP): self
    {
        $locale = static::ISO_4217_MAP[strtoupper($currency)] ?? null;

        if ($locale === null) {
            throw new InvalidArgumentException(sprintf("No canonical locale found for currency '%s'.", $currency));
        }

        return new self($locale, $roundingMode);
    }

    public static function forLocale(string $locale, int $roundingMode = PHP_ROUND_HALF_UP): self
    {
        return new self($locale, $roundingMode);
    }

    // -------------------------------------------------------------------------
    //  Getters
    // -------------------------------------------------------------------------

    public function getCountry(): string
    {
        return \Locale::getRegion($this->getLocale()) ?? '';
    }

    public function getCurrency(): string
    {
        return $this->formatter->getTextAttribute(NumberFormatter::CURRENCY_CODE) ?: '';
    }

    public function getFractionDigits(): int
    {
        return (int) $this->formatter->getAttribute(NumberFormatter::FRACTION_DIGITS);
    }

    public function getLocale(): string
    {
        return $this->formatter->getLocale() ?: $this->locale;
    }

    public function getRoundingMode(): int
    {
        return $this->roundingMode;
    }

    // -------------------------------------------------------------------------
    //  Major-unit conversions (e.g. dollars, yen)
    // -------------------------------------------------------------------------

    /** @throws InvalidArgumentException If a string amount cannot be parsed. */
    public function fromMajor(string|float|int $amount, ?int $mode = null): int
    {
        return $this->snapToMinor($this->toFloat($amount) * static::NANOS, $mode);
    }

    public function toMajor(int $nanos, ?int $mode = null): float
    {
        return round($nanos / static::NANOS, $this->getFractionDigits(), $mode ?? $this->roundingMode);
    }

    // -------------------------------------------------------------------------
    //  Minor-unit conversions (e.g. cents)
    // -------------------------------------------------------------------------

    /** @throws InvalidArgumentException If a string amount cannot be parsed. */
    public function fromMinor(string|float|int $amount, ?int $mode = null): int
    {
        return $this->snapToMinor($this->toFloat($amount) * $this->minorFactor(), $mode);
    }

    public function toMinor(int $nanos, ?int $mode = null): int
    {
        return (int) round($nanos / $this->minorFactor(), 0, $mode ?? $this->roundingMode);
    }

    // -------------------------------------------------------------------------
    //  Nano-level operations
    // -------------------------------------------------------------------------

    /** @throws InvalidArgumentException If a string amount cannot be parsed. */
    public function toNano(string|float|int $amount, ?int $mode = null): int
    {
        return (int) round($this->toFloat($amount) * static::NANOS, 0, $mode ?? $this->roundingMode);
    }

    public function snapToMinor(int|float $nanos, ?int $mode = null): int
    {
        $factor = $this->minorFactor();
        return (int) round($nanos / $factor, 0, $mode ?? $this->roundingMode) * $factor;
    }

    public function snapToNano(int|float $nanos, ?int $mode = null): int
    {
        return (int) round($nanos, 0, $mode ?? $this->roundingMode);
    }

    // -------------------------------------------------------------------------
    //  Formatting
    // -------------------------------------------------------------------------

    public function formatCurrency(int $nanos, ?int $mode = null): string
    {
        return $this->formatter->formatCurrency($this->toMajor($nanos, $mode), $this->getCurrency()) ?: '';
    }

    public function formatDecimal(int $nanos, ?int $mode = null): string
    {
        return $this->getDecimalFormatter()->format($this->toMajor($nanos, $mode)) ?: '';
    }

    // -------------------------------------------------------------------------
    //  Internal helpers
    // -------------------------------------------------------------------------

    /** @throws InvalidArgumentException */
    private function toFloat(string|float|int $amount): float
    {
        if (is_numeric($amount)) {
            return (float) $amount;
        }

        $result = $this->formatter->parse($amount);
        if ($result === false) {
            throw new InvalidArgumentException(sprintf("Unable to parse amount '%s'.", $amount));
        }

        return (float) $result;
    }

    private function minorFactor(): int
    {
        return (int) (static::NANOS / (10 ** $this->getFractionDigits()));
    }

    private function getDecimalFormatter(): NumberFormatter
    {
        if ($this->decimalFormatter === null) {
            $this->decimalFormatter = new NumberFormatter($this->getLocale(), NumberFormatter::DECIMAL);
            $this->decimalFormatter->setAttribute(NumberFormatter::FRACTION_DIGITS, $this->getFractionDigits());
        }

        return $this->decimalFormatter;
    }
}
