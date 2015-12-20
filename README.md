# CoinsBill PHP Client

This is a PHP Client for interacting with the [CoinsBill API](https://www.coinsbill.com/developers).

### Example Usage



```php

require 'coinsbill.php';

$api = new CoinsBill;
$api->set_option('access_token
', 'a9esthaVUjOuvzTCSTXsJUCK0lCMCk
');

$result = $api->getInvoice();

if($result->info->http_code == 200)
    var_dump($result->decode_response());

```