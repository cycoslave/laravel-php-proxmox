<?php

namespace Cycoslave\Proxmox\Traits;

/**
 * Provides path-segment validation for Proxmox API classes.
 *
 * Proxmox identifiers (node names, storage IDs, realms, VMIDs, etc.)
 * must consist only of word chars, hyphens, and dots.
 * Rejects traversal sequences (../../), null bytes, and other
 * characters that could corrupt the constructed URL.
 */
trait ValidatesPathSegments
{
    /**
     * Assert that a URL path segment is safe to interpolate.
     *
     * Allowed characters: [A-Za-z0-9_\-.]
     * No slashes, no null bytes, no percent-encoded sequences.
     *
     * @param  string  $value  The caller-supplied value.
     * @param  string  $name   Parameter name used in the exception message.
     * @throws \InvalidArgumentException
     */
    private function validateSegment(string $value, string $name): void
    {
        if ($value === '') {
            throw new \InvalidArgumentException(
                "Proxmox path segment [{$name}] must not be empty."
            );
        }

        if (! preg_match('/^[\w\-\.]+$/', $value)) {
            throw new \InvalidArgumentException(
                "Invalid Proxmox path segment for [{$name}]: [{$value}]"
            );
        }
    }
}