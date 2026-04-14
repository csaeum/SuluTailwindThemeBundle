<?php

declare(strict_types=1);

namespace ItechWorld\SuluTailwindThemeBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Twig extension providing helper functions for article templates.
 *
 * Loaded conditionally only when article_templates.enabled is true.
 * Provides date formatting, reading time estimation, author name
 * resolution, and style selection helpers.
 */
class ArticleExtension extends AbstractExtension
{
    /**
     * Default styles for each article type when no admin config is set.
     */
    private const DEFAULT_ARTICLE_STYLES = [
        'news' => 'classic',
        'event' => 'card_info',
        'blog_post' => 'classic',
    ];

    /**
     * Default listing style when no admin config is set.
     */
    private const DEFAULT_LISTING_STYLE = 'grid';

    /**
     * Average words per minute for reading time calculation.
     */
    private const WORDS_PER_MINUTE = 250;

    public function __construct(
        private readonly ThemeExtension $themeExtension,
    ) {
    }

    /**
     * @return list<TwigFunction>
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('iw_sulu_tailwind_theme_format_date', $this->formatDate(...)),
            new TwigFunction('iw_sulu_tailwind_theme_reading_time', $this->readingTime(...)),
            new TwigFunction('iw_sulu_tailwind_theme_author_name', $this->authorName(...)),
            new TwigFunction('iw_sulu_tailwind_theme_article_style', $this->articleStyle(...)),
            new TwigFunction('iw_sulu_tailwind_theme_listing_style', $this->listingStyle(...)),
        ];
    }

    /**
     * Format a date using ICU date formatting with the current locale.
     *
     * @param \DateTimeInterface|string|null $date   The date to format
     * @param string                        $format  ICU date format ('long', 'medium', 'short', 'full')
     *                                               or a custom ICU pattern (e.g. "d MMMM yyyy, HH:mm")
     *
     * @return string The formatted date, or empty string if date is null
     */
    public function formatDate(\DateTimeInterface|string|null $date, string $format = 'long'): string
    {
        if (null === $date) {
            return '';
        }

        if (\is_string($date)) {
            try {
                $date = new \DateTimeImmutable($date);
            } catch (\Exception) {
                return $date;
            }
        }

        $dateType = match ($format) {
            'full' => \IntlDateFormatter::FULL,
            'long' => \IntlDateFormatter::LONG,
            'medium' => \IntlDateFormatter::MEDIUM,
            'short' => \IntlDateFormatter::SHORT,
            default => \IntlDateFormatter::NONE,
        };

        // Known format keyword → use IntlDateFormatter with date type
        if (\IntlDateFormatter::NONE !== $dateType) {
            $formatter = new \IntlDateFormatter(
                \Locale::getDefault(),
                $dateType,
                \IntlDateFormatter::NONE,
            );

            return $formatter->format($date) ?: '';
        }

        // Custom ICU pattern (e.g. "d MMMM yyyy, HH:mm")
        $formatter = new \IntlDateFormatter(
            \Locale::getDefault(),
            \IntlDateFormatter::FULL,
            \IntlDateFormatter::FULL,
        );
        $formatter->setPattern($format);

        return $formatter->format($date) ?: '';
    }

    /**
     * Estimate reading time from HTML content.
     *
     * Strips HTML tags, counts words, divides by average reading speed
     * (250 words/min). Returns at least 1 minute.
     *
     * @param string|null $content The HTML content to analyze
     *
     * @return int Estimated reading time in minutes (minimum 1)
     */
    public function readingTime(?string $content): int
    {
        if (null === $content || '' === trim($content)) {
            return 1;
        }

        $text = strip_tags($content);
        $wordCount = str_word_count($text);

        return max(1, (int) ceil($wordCount / self::WORDS_PER_MINUTE));
    }

    /**
     * Resolve the display name from an author block entry.
     *
     * Handles the three author types:
     * - custom: returns the "name" field
     * - contact: returns "firstName lastName" from Sulu contact data
     * - organization: returns the organization name from Sulu account data
     *
     * @param array<string, mixed> $authorBlock A single author block entry
     *
     * @return string The resolved author name, or empty string
     */
    public function authorName(array $authorBlock): string
    {
        $type = $authorBlock['type'] ?? '';

        return match ($type) {
            'custom' => (string) ($authorBlock['name'] ?? ''),
            'contact' => $this->resolveContactName($authorBlock),
            'organization' => $this->resolveOrganizationName($authorBlock),
            default => '',
        };
    }

    /**
     * Get the active style for an article type.
     *
     * Reads from the theme's article config (set in Sprint 3 admin tab).
     * Falls back to sensible defaults: news→classic, event→card_info, blog→classic.
     *
     * @param string $type The article type key (news, event, blog_post)
     *
     * @return string The active style key
     */
    public function articleStyle(string $type): string
    {
        $tokens = $this->themeExtension->getTokens();
        $articleConfig = $tokens['articleStyles'] ?? [];

        return (string) ($articleConfig[$type] ?? self::DEFAULT_ARTICLE_STYLES[$type] ?? 'classic');
    }

    /**
     * Get the active listing style.
     *
     * Reads from the theme's article config (set in Sprint 3 admin tab).
     * Falls back to 'grid'.
     *
     * @return string The active listing style key (grid, list, cards)
     */
    public function listingStyle(): string
    {
        $tokens = $this->themeExtension->getTokens();
        $articleConfig = $tokens['articleStyles'] ?? [];

        return (string) ($articleConfig['listing'] ?? self::DEFAULT_LISTING_STYLE);
    }

    /**
     * Resolve a contact name from a Sulu contact selection.
     *
     * @param array<string, mixed> $authorBlock The author block data
     *
     * @return string "firstName lastName"
     */
    private function resolveContactName(array $authorBlock): string
    {
        $contact = $authorBlock['contact'] ?? null;

        if (\is_array($contact)) {
            $firstName = (string) ($contact['firstName'] ?? '');
            $lastName = (string) ($contact['lastName'] ?? '');

            return trim("{$firstName} {$lastName}");
        }

        return '';
    }

    /**
     * Resolve an organization name from a Sulu account selection.
     *
     * @param array<string, mixed> $authorBlock The author block data
     *
     * @return string The organization name
     */
    private function resolveOrganizationName(array $authorBlock): string
    {
        $organization = $authorBlock['organization'] ?? null;

        if (\is_array($organization)) {
            return (string) ($organization['name'] ?? '');
        }

        return '';
    }
}
