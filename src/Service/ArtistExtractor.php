<?php

declare(strict_types=1);

namespace App\Service;

use function array_map;
use function explode;
use function preg_match;
use function preg_replace;
use function preg_split;
use function str_contains;
use function stripos;
use function strlen;
use function substr;
use function trim;

// TODO: Work in progress
final class ArtistExtractor
{
    private const array DESCRIPTIVE_PREFIXES = [
        'Orijentalni progressive metalci',
        'Symphonic metal velikani',
        'Paganska folk atrakcija',
        'Pioniri sludge metala',
        'Legendarni',
        'Death metal velikani',
    ];

    /**
     * @return string[]
     */
    public function extractArtists(string $name): array
    {
        $nameForParsing = $name;

        // Split by guest indicator FIRST
        $parts = preg_split('/\s+Gosti:\s+/i', $nameForParsing);
        $mainActsString = $parts[0];
        $guestsString = $parts[1] ?? '';

        // Clean main acts
        $mainActsString = (string) preg_replace('/\s+(?:Novi datum|Premijerno).*$/i', '', $mainActsString);
        
        // Remove common venue/location markers like " u ", " in ", " at " followed by some words
        $locationPattern = '/\s+(?:u|in|at|v|premijerno u)\s+[\p{L}\d\s\']+(?=[,!]| i | \+ | \/ | & | and |$)/ui';
        $mainActsString = (string) preg_replace($locationPattern, '', trim($mainActsString));

        // Remove date patterns like 5.5.2026.
        $mainActsString = (string) preg_replace('/\s*,?\s*\d{1,2}\.\d{1,2}\.\d{2,4}\.?\s*/', ' ', $mainActsString);
        
        // Remove trailing short uppercase words (often venues like VIB, K9, etc.) preceded by space or comma
        // Ensure we don't remove the only word left after descriptive prefixes are removed!
        $tempMain = trim($mainActsString);
        foreach (self::DESCRIPTIVE_PREFIXES as $prefix) {
            if (stripos($tempMain, $prefix) === 0) {
                $tempMain = trim(substr($tempMain, strlen($prefix)));
                break;
            }
        }
        
        // Only strip if there's more than one word left in the cleaned-up name
        if (preg_match('/\s+([A-Z\d]{2,10})$/', $tempMain) && preg_match('/\s+/', $tempMain)) {
            $mainActsString = (string) preg_replace('/\s+[A-Z\d]{2,10}$/', '', trim($mainActsString));
        }

        // Split main acts by " i " (and) or "+" or "/" but NOT within words
        $artists = preg_split('/\s+(?:i|\+|&|and)\s+|\s*[\/]\s*/u', trim($mainActsString, '! ,'));

        // Add guests if present
        if ($guestsString !== '') {
            $guestsString = (string) preg_replace('/\s+(?:Novi datum|Premijerno).*$/i', '', $guestsString);
            $guestsString = (string) preg_replace($locationPattern, '', trim($guestsString));
            $guests = preg_split('/\s+(?:i|\+|\/|&|and)\s+|\s*,\s*/u', trim($guestsString, '! ,'));
            foreach ($guests as $guest) {
                $guest = trim($guest);
                if ($guest !== '') {
                    $artists[] = $guest;
                }
            }
        }

        return array_map(function (string $artist) {
            $artist = trim($artist, '! ');

            // Handle tribute bands: "SOAD tribute Chop Suey" -> "Chop Suey"
            if (stripos($artist, ' tribute ') !== false) {
                $tributeParts = preg_split('/\s+tribute\s+/i', $artist);

                return trim($tributeParts[1] ?? $artist);
            }

            // Remove descriptive prefixes
            foreach (self::DESCRIPTIVE_PREFIXES as $prefix) {
                if (stripos($artist, $prefix) === 0) {
                    $artist = trim(substr($artist, strlen($prefix)));
                    break;
                }
            }

            // Remove extra info after " - "
            if (str_contains($artist, ' - ')) {
                $artist = trim(explode(' - ', $artist)[0]);
            }

            return $artist;
        }, $artists);
    }
}
