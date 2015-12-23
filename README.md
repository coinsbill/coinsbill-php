# CoinsBill PHP Client

This is a PHP Client for interacting with the [CoinsBill API Developer Docs](https://www.coinsbill.com/developers).

### Example Usage



```php

require 'coinsbill.php';

$api = new CoinsBill;
$api->set_option('access_token
', 'a9esthaVUjOuvzTCSTXsJUCK0lCMCk
');


// Get All Merchant Invoices
$result = $api->getInvoice();

if($result->info->http_code == 200)
    var_dump($result->decode_response());



// Get A Specific Invoice
$result = $api->getInvoice('6ACWR');


if($result->info->http_code == 200)
    var_dump($result['invoice_url']);
    var_dump($result->decode_response());



// Create A New Invoice

$items = array(
    array(
        'name' => 'Shoes', 
        'quantity' => 4,
        'unit_price' => 69.99,
    ),
    array(
        'name' => 'Shirt`', 
        'quantity' => 2,
        'unit_price' => 29.99,
    ),
    array(
        'name' => 'Belt', 
        'quantity' => 3,
        'unit_price' => 5.50,
    )
);
$data = array(
        'email' => 'john@dow.com', 
        'currency' => 'USD',
        'country' => 'US', 
        'billing_first_name' => 'John',
        'items' => $items,
        );


$result = $api->createInvoice($data); 

```