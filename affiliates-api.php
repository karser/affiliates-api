<?php
/**
 * Plugin Name: Affiliates Api
 * Description: Affiliates Api
 * Version:     0.0.1
 * Author:      Dmitrii Poddubnyi
 * Author URI:  https://karser.dev
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

if ( ! defined( 'AFFILIATE_WP_API_KEY' ) ) {
    die;
}

add_action('rest_api_init', function () {
    register_rest_route('affiliates-api', '/referrals', [
        'methods' => 'POST',
        'callback' => 'affiliates_api_add_referrals',
        'permission_callback' => '__return_true',
        'args' => [
            'email' => [
                'required' => true,
                'type' => 'string',
                'format' => 'email',
            ],
            'date' => [
                'required' => true,
                'type' => 'string',
                'format' => 'date-time',
            ],
            'baseAmount' => [
                'required' => true,
                'type' => 'string',
            ],
            'currency' => [
                'required' => true,
                'type' => 'string',
            ],
            'refId' => [
                'required' => true,
                'type' => 'string',
            ],
            'hit' => [
                'required' => true,
                'type' => 'string',
            ],
            'reference' => [
                'required' => true,
                'type' => 'string',
            ],
            'ip' => [
                'required' => true,
                'type' => 'string',
                'format' => 'ip',
            ],
        ]
    ]);
});

function affiliates_api_add_referrals(WP_REST_Request $request)
{
    $data = $request->get_json_params();

    if (null === ($apiKey = $request->get_header('X-Api-Key'))
        || $apiKey !== AFFILIATE_WP_API_KEY
    ) {
        return new WP_Error( 'access_denied', 'Api key is wrong', ['status' => 400]);
    }

    $hitId = affiliates_api_get_hit_id($data['hit'], $data['refId']) ;

    if (null === $hitId) {
        return new WP_Error( 'wrong_hash', 'Hash or referrer is wrong', ['status' => 400]);
    }

    $r = new Affiliates_Referral_WordPress();
    $affiliate_ids = [$data['refId']];
    $orderId = 0; // related post ID, order ID
    $description = $data['email'];

    $payload = [
        'date' => [
            'title'  => 'Date',
            'domain' => 'affiliates',
            'value'  => esc_sql( $data['date'] )
        ],
        'email' => [
            'title'  => 'Email',
            'domain' =>  'affiliates',
            'value'  => esc_sql( $data['email'] )
        ],
        'ip' => [
            'title'  => 'IP',
            'domain' =>  'affiliates',
            'value'  => esc_sql( $data['ip'] )
        ],
        'baseAmount' => [
            'title'  => 'Base Amount',
            'domain' =>  'affiliates',
            'value'  => esc_sql( $data['baseAmount'] )
        ],
    ];

    $base_amount = $data['baseAmount'];
    $amount = null;
    $currency_id = $data['currency'];
    $status = null;
    $type = null;
    $reference = $data['reference'];
    $test = false;

    $r->add_referrals($affiliate_ids, $orderId, $description, $payload, $base_amount, $amount, $currency_id, null, null, $reference, $test, $hitId);
}

function affiliates_api_get_hit_id($hash, $affiliate_id) {
    global $wpdb;
    $hits_table = _affiliates_get_tablename( 'hits' );
    $query = "SELECT hit_id, hash, affiliate_id FROM {$hits_table} WHERE affiliate_id = %d AND hash = %s";
    $row = $wpdb->get_row($wpdb->prepare($query, (int) $affiliate_id, $hash));
    if ($row && !empty($row->hit_id)) {
        return $row->hit_id;
    }
    return null;
}
