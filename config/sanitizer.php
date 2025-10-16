<?php

return [
    /**
     * Allowed HTML Purifier settings.
     * Consumers (buyers) can publish and customize these values.
     */
    'allowed_html' => '<p><br><strong><b><em><i><ul><ol><li><a>',

    // HTMLPurifier specific config options documented at https://htmlpurifier.org/live/configdoc/plain.html
    'htmlpurifier' => [
        'HTML.SafeIframe' => true,
        'URI.SafeIframeRegexp' => '%^(https?:)?//(www.youtube.com/embed/|player.vimeo.com/video/)%',
        'Attr.AllowedFrameTargets' => ['_blank'],
        'HTML.AllowedAttributes' => 'href, title, target',
        'AutoFormat.RemoveEmpty' => true,
    ],
];
