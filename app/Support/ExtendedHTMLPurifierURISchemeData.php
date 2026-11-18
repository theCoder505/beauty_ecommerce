<?php

namespace App\Support;

/**
 * Extended HTMLPurifier data URI scheme supporting modern image formats (AVIF, WebP, PNG, JPEG, GIF).
 */
class ExtendedHTMLPurifierURISchemeData extends \HTMLPurifier_URIScheme_data
{
    /**
     * Allowed MIME types for data URIs.
     *
     * @var array<string, bool>
     */
    public $allowed_types = [
        'image/jpeg' => true,
        'image/jpg'  => true,
        'image/gif'  => true,
        'image/png'  => true,
        'image/webp' => true,
        'image/avif' => true,
    ];
}
