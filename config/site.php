<?php

$supportEmails = array_values(array_filter(array_map(
    'trim',
    explode(',', (string) env('SITE_CONTACT_EMAILS', 'support@khaitriedu.com,info@khaitriedu.com'))
)));

if ($supportEmails === []) {
    $supportEmails = ['support@khaitriedu.com', 'info@khaitriedu.com'];
}

return [
    'contact' => [
        'recipient' => env('SITE_CONTACT_RECIPIENT', env('MAIL_FROM_ADDRESS', 'hello@example.com')),
        'address' => env('SITE_CONTACT_ADDRESS', '123 Đường ABC, Quận 1, TP.HCM, Việt Nam'),
        'phone' => env('SITE_CONTACT_PHONE', '+84 123 456 789'),
        'hours' => env('SITE_CONTACT_HOURS', '8:00 - 18:00 (Thứ 2 - Thứ 6)'),
        'emails' => $supportEmails,
    ],

    'social_links' => [
        'facebook' => env('SITE_FACEBOOK_URL', 'https://www.facebook.com/profile.php?id=61575515763147'),
        'youtube' => env('SITE_YOUTUBE_URL', 'https://www.youtube.com/channel/UCPrE7RBNFZHZAxJzvCxvMSg'),
        'zalo' => env('SITE_ZALO_URL', 'https://zalo.me/84867852853'),
    ],

    'map_embed_url' => env('SITE_MAP_EMBED_URL', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1569.6935022522895!2d105.43735901504439!3d10.367556123108891!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x310a72e6226e4093%3A0xdc2db2a3b1ff6bb4!2zVHJ1bmcgdMsQY2ggRMO0eSBEacOgIEtoaSBUcuG7qyBLaGFpIFTDtGk!5e0!3m2!1svi!2s!4v1700000000000!5m2!1svi!2s'),
];
