<?php

namespace App\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use App\Service\Service;
use Twig\Environment;
use IntlDateFormatter;
use Morilog\Jalali\Jalalian;

class TwigExtension extends AbstractExtension
{
    private $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    public function getFilters()
    {
        return [
            new TwigFilter('localizeddatetr', [$this, 'persiandatetimeFilter'], ['needs_environment' => true]),
        ];
    }

    public function getFunctions()
    {
        return [
            new \Twig\TwigFunction('findEntitiesWithCriteria', [$this->service, 'findEntitiesWithCriteria']),
        ];
    }

    public function persiandatetimeFilter(Environment $env, $date, $dateFormat = 'medium', $timeFormat = 'medium', $locale = null, $timezone = null, $format = null)
    {
        // Convert the date to a DateTime object based on the timezone
        $date = twig_date_converter($env, $date, $timezone);

        // Define IntlDateFormatter format options for date and time
        $formatValues = [
            'none'   => IntlDateFormatter::NONE,
            'short'  => IntlDateFormatter::SHORT,
            'medium' => IntlDateFormatter::MEDIUM,
            'long'   => IntlDateFormatter::LONG,
            'full'   => IntlDateFormatter::FULL,
        ];

        // Resolve the date and time formats
        $resolvedDateFormat = $formatValues[$dateFormat] ?? IntlDateFormatter::MEDIUM; // Default to 'medium' if not set
        $resolvedTimeFormat = $formatValues[$timeFormat] ?? IntlDateFormatter::MEDIUM; // Default to 'medium' if not set

        // Try to use IntlDateFormatter for localization
        try {
            $formatter = IntlDateFormatter::create(
                $locale ?? 'fa_IR',                        // Default to Persian locale
                $resolvedDateFormat,                       // Use resolved date format
                $resolvedTimeFormat,                       // Use resolved time format
                $date->getTimezone()->getName(),           // Use the provided timezone
                IntlDateFormatter::TRADITIONAL,            // Traditional calendar (Persian for 'fa_IR')
                $format                                   // Optional custom format
            );

            // Format and return the date using IntlDateFormatter
            return $formatter->format($date->getTimestamp());
        } catch (\Exception $e) {
            // If IntlDateFormatter fails, fallback to manual Jalali date conversion
            $jalaliDate = Jalalian::fromDateTime($date);

            // Use custom formats if provided
            if ($format === 'yyyy/MM/dd') {
                return $jalaliDate->format('Y/m/d');
            } elseif ($format === 'HH:mm') {
                return $jalaliDate->format('H:i');
            }

            // Return the Jalali date as a fallback (full date and time)
            return $jalaliDate->format('Y/m/d H:i');
        }
    }
}
