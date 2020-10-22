# Rest API for Itthinx Affiliate WordPress Plugin

## How it works:

### 1. Define the api key in wp-config.php

```
if ( !defined('AFFILIATE_WP_API_KEY') ) {
    define('AFFILIATE_WP_API_KEY', 'define-your-api-key-here');
}
```

### 2. Save the hit hash and referrer id from cookie during registration

This may happen when the user submits the registration form so this code checks for the affiliate cookies and sends them back to the server along with the registration data

```
function getRefTrackingId(): string {
    const affId = CookieStorage.getItem('wp_affiliates');
    const hit = CookieStorage.getItem('_h_affiliates');
    if (isDefined(affId) && isDefined(hit)) {
        return `${affId}|${hit}`;
    }
    return null;
}
```

### 3. Submit a purchase back to the plugin

```
POST /wp-json/affiliates-api/referrals HTTP/1.1
Host: your-host.com
Content-Type: application/json
X-Api-Key: your-key
{
  "email":"joe@email.com",
  "baseAmount":"100",
  "currency":"USD",
  "refId":"5",
  "date":"2012-01-01T10:01:01Z",
  "hit":"c096876aa6517e2c6d5198efa6a4037532dc1f97959963689c8b6bf3bba17c2f",
  "reference":"txn_16A3XQSE2ez0wdde",
  "ip":"158.217.93.151"
}
```

## Troubleshooting

### Share cookie between subdomain and domain
if the wordpress and your app are on different subdomains
(e.g your-domain.com and app.your-domain.com)
try adding a leading dot to the cookie settings:
```
define( 'COOKIE_DOMAIN', '.your-domain.com' );
```
