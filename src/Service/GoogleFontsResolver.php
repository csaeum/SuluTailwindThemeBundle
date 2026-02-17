<?php

declare(strict_types=1);

namespace ItechWorld\SuluThemeBundle\Service;

/**
 * Resolves typography tokens into a Google Fonts CSS2 API URL.
 *
 * Parses font family configurations from design tokens and generates
 * the appropriate Google Fonts import URL with specified weights.
 */
class GoogleFontsResolver
{
    /**
     * Base URL for the Google Fonts CSS2 API.
     */
    private const GOOGLE_FONTS_BASE_URL = 'https://fonts.googleapis.com/css2';

    /**
     * Resolve typography tokens into a Google Fonts CSS2 URL.
     *
     * Extracts font family names and weights from the typography tokens
     * and builds a Google Fonts URL that imports all required font variations.
     *
     * @param array<string, mixed> $typographyTokens The typography section of design tokens
     *
     * @return string|null The Google Fonts URL, or null if no fonts are configured
     */
    public function resolve(array $typographyTokens): ?string
    {
        $families = $typographyTokens['families'] ?? [];

        if (empty($families)) {
            return null;
        }

        $familyParams = [];

        foreach ($families as $family) {
            $name = $family['name'] ?? null;
            $source = $family['source'] ?? 'google';
            $weights = $family['weights'] ?? [400];

            // Only include Google Fonts (skip local and system fonts)
            if (null === $name || 'google' !== $source) {
                continue;
            }

            // URL-encode the family name (spaces become +)
            $encodedName = str_replace(' ', '+', $name);

            // Sort weights numerically for consistent URLs
            $weightValues = array_map('intval', $weights);
            sort($weightValues);

            $weightString = implode(';', $weightValues);
            $familyParams[] = "family={$encodedName}:wght@{$weightString}";
        }

        if (empty($familyParams)) {
            return null;
        }

        $queryString = implode('&', $familyParams);

        return self::GOOGLE_FONTS_BASE_URL . '?' . $queryString . '&display=swap';
    }
}
