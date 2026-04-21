<?php
/**
 * Plugin Name: ALG CRM Webhook
 * Description: Sends Fluent Forms submissions to ALG Marketing CRM
 * Version: 1.0
 * Network: true
 */

add_action('fluentform/submission_inserted', function ($entryId, $formData, $form) {
    // Determine country from current site URL
    $siteUrl = get_site_url();
    $countryCode = '';

    $countryMap = [
        '/sv'  => 'sv',
        '/gt'  => 'gt',
        '/hn'  => 'hn',
        '/nic' => 'ni',
        '/pty' => 'pa',
        '/us'  => 'us',
        '/cr'  => 'cr',
    ];

    foreach ($countryMap as $path => $code) {
        if (strpos($siteUrl, $path) !== false) {
            $countryCode = $code;
            break;
        }
    }

    // Skip if regional/international site (no country match)
    if (empty($countryCode)) {
        return;
    }

    // Build payload
    $payload = array_merge($formData, [
        'country_code' => $countryCode,
        'form_id'      => $form->id,
        'form_title'   => $form->title,
        'entry_id'     => $entryId,
        'site_url'     => $siteUrl,
    ]);

    // Send to CRM webhook
    $webhookUrl = 'https://marketing.alg3pl.com/api/v1/webhook/fluent-forms';

    wp_remote_post($webhookUrl, [
        'method'  => 'POST',
        'timeout' => 15,
        'headers' => [
            'Content-Type'     => 'application/json',
            'X-Webhook-Secret' => 'alg3pl-fluent-webhook-2026',
        ],
        'body' => wp_json_encode($payload),
    ]);
}, 20, 3);
