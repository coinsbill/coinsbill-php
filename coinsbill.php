<?php



class CoinsBillException extends Exception {}

class CoinsBill implements Iterator, ArrayAccess {
    
    public $options;
    public $handle; // cURL resource handle.
    
    // Populated after execution:
    public $response; // Response body.
    public $headers; // Parsed reponse header object.
    public $info; // Response info object.
    public $error; // Response error string.
    
    // Populated as-needed.
    public $decoded_response; // Decoded response body. 
    private $iterator_positon;
    
    public function __construct($options=array()){
        $default_options = array(
            'headers' => array(), 
            'parameters' => array(), 
            'curl_options' => array(), 
            'user_agent' => "PHP RestClient/0.1.4", 
            'base_url' => 'https://www.coinsbill.com/api/', 
            'format' => NULL, 
            'format_regex' => "/(\w+)\/(\w+)(;[.+])?/",
            'decoders' => array(
                'json' => 'json_decode', 
                'php' => 'unserialize'
            ), 
            // 'username' => NULL, 
            'access_token' => NULL
        );
        
        $this->options = array_merge($default_options, $options);
        if(array_key_exists('decoders', $options))
            $this->options['decoders'] = array_merge(
                $default_options['decoders'], $options['decoders']);
    }
    
    public function set_option($key, $value){
        $this->options[$key] = $value;
    }
    
    public function register_decoder($format, $method){
        // Decoder callbacks must adhere to the following pattern:
        //   array my_decoder(string $data)
        $this->options['decoders'][$format] = $method;
    }
    
    // Iterable methods:
    public function rewind(){
        $this->decode_response();
        return reset($this->decoded_response);
    }
    
    public function current(){
        return current($this->decoded_response);
    }
    
    public function key(){
        return key($this->decoded_response);
    }
    
    public function next(){
        return next($this->decoded_response);
    }
    
    public function valid(){
        return is_array($this->decoded_response)
            && (key($this->decoded_response) !== NULL);
    }
    
    // ArrayAccess methods:
    public function offsetExists($key){
        $this->decode_response();
        return is_array($this->decoded_response)?
            isset($this->decoded_response[$key]) : isset($this->decoded_response->{$key});
    }
    
    public function offsetGet($key){
        $this->decode_response();
        if(!$this->offsetExists($key))
            return NULL;
        
        return is_array($this->decoded_response)?
            $this->decoded_response[$key] : $this->decoded_response->{$key};
    }
    
    public function offsetSet($key, $value){
        throw new CoinsBillException("Decoded response data is immutable.");
    }
    
    public function offsetUnset($key){
        throw new CoinsBillException("Decoded response data is immutable.");
    }

    public function getInvoice($id=false, $url='invoice', $parameters=array(), $headers=array()){
        if($id )
            $url = sprintf("%s/%s/", $url, $id);
        return $this->get($url, 'GET', $parameters, $headers);
    }

    public function createInvoice($parameters=array()){
        ;
        $url='invoice/';
        $items = $parameters['items'];
        $invoice =  $this->execute($url, 'POST', $parameters,  array('Content-Type' => 'application/json'));
        echo 'invoice id: '. $invoice['orderId'];
        foreach ($items as $obj_key =>$item)
        {
        echo 'item name: '. $item['name'];
        echo 'item qty: '. $item['quantity'];
        echo 'item price: '. $item['unit_price'];

        $dataItem = array(
            'name' => $item['name'], 
            'quantity' => $item['quantity'],
            'unit_price' => $item['unit_price'], 
            'order' => $invoice['orderId'], 
        );

        $ritem =  $this->execute('orderitem/', 'POST', $dataItem,  array('Content-Type' => 'application/json'));
        // echo $ritem

        foreach($item as $key => $value){
                echo sprintf("%s:%s ", $key, $value);
                //$curlopt[CURLOPT_HTTPHEADER][] = sprintf("%s:%s", $key, $value);
            }
        }

        // return $this->execute($url, 'POST', $parameters,  array('Content-Type' => 'application/json'));
        # return $invoice;

        return $this->getInvoice($invoice['orderId']);
    }

    public function getCustomer($id=false, $url='customer/', $parameters=array(), $headers=array()){
        if($id )
            $url = sprintf("%s/%s/", $url, $id);
        return $this->get($url, 'GET', $parameters, $headers);
    }
    
    public function getSettlement($id=false, $url='settlement/', $parameters=array(), $headers=array()){
        if($id )
            $url = sprintf("%s/%s/", $url, $id);
        return $this->get($url, 'GET', $parameters, $headers);
    }

    public function getRates($id=false, $url='rates/', $parameters=array(), $headers=array()){
        if($id )
            $url = sprintf("%s/%s/", $url, $id);
        return $this->get($url, 'GET', $parameters, $headers);
    }

    // Request methods:
    public function get($url, $parameters=array(), $headers=array()){
        return $this->execute($url, 'GET', $parameters, $headers);
    }
    
    public function post($url, $parameters=array(), $headers=array()){
        return $this->execute($url, 'POST', $parameters, $headers);
    }
    
    public function put($url, $parameters=array(), $headers=array()){
        return $this->execute($url, 'PUT', $parameters, $headers);
    }
    
    public function delete($url, $parameters=array(), $headers=array()){
        return $this->execute($url, 'DELETE', $parameters, $headers);
    }

    public function executem($url, $method='GET', $data = false){

    // $curl = curl_init();

    if($client->options['base_url']){
            if($url[0] != '/' && substr($client->options['base_url'], -1) != '/')
                $url = '/' . $url;
            $url = $client->options['base_url'] . $url;
        }
    echo 'URL: '.$url;

    switch ($method)
    {
        case "POST":
            curl_setopt($curl, CURLOPT_POST, 1);

            if ($data)
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            break;
        case "PUT":
            curl_setopt($curl, CURLOPT_PUT, 1);
            break;
        default:
            if ($data)
                $url = sprintf("%s?%s", $url, http_build_query($data));
    }

    // Optional Authentication:
    // curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    // curl_setopt($curl, CURLOPT_USERPWD, "username:password");
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('authorization: Bearer '.$client->options['access_token']));
    // curl_setopt($curl, CURLOPT_HTTPHEADER, "Authorization: Bearer a9esthaVUjOuvzTCSTXsJUCK0lCMCk");
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    $result = curl_exec($curl);

    curl_close($curl);

    return $result;
    }
    
    public function execute($url, $method='GET', $parameters=array(), $headers=array()){
        $client = clone $this;
        $client->url = $url;
        $client->handle = curl_init();
        $curlopt = array(
            CURLOPT_HEADER => TRUE, 
            CURLOPT_RETURNTRANSFER => TRUE, 
            CURLOPT_USERAGENT => $client->options['user_agent']
        );
        /*
        if($client->options['username'] && $client->options['password'])
            $curlopt[CURLOPT_USERPWD] = sprintf("%s:%s", 
                $client->options['username'], $client->options['password']);*/
        
        if(count($client->options['headers']) || count($headers)){
            $curlopt[CURLOPT_HTTPHEADER] = array();
            $headers = array_merge($client->options['headers'], $headers);
            foreach($headers as $key => $value){
                $curlopt[CURLOPT_HTTPHEADER][] = sprintf("%s:%s", $key, $value);
            }
        }
        // curl_setopt($curl, CURLOPT_HTTPHEADER, array('authorization: Bearer a9esthaVUjOuvzTCSTXsJUCK0lCMCk'));
        // $curlopt[CURLOPT_HTTPHEADER][] = array('authorization: Bearer a9esthaVUjOuvzTCSTXsJUCK0lCMCk');
        
        if($client->options['format'])
            $client->url .= '.'.$client->options['format'];
        
        // Allow passing parameters as a pre-encoded string (or something that
        // allows casting to a string). Parameters passed as strings will not be
        // merged with parameters specified in the default options.
        if(is_array($parameters)){
            $parameters = array_merge($client->options['parameters'], $parameters);
            $parameters_string = $client->format_query($parameters);
        }
        else
            $parameters_string = (string) $parameters;
        echo 'params: '. $parameters_string;
        
        if(strtoupper($method) == 'POST'){
            $curlopt[CURLOPT_POST] = TRUE;
            $curlopt[CURLOPT_POSTFIELDS] = $parameters_string;
        }
        elseif(strtoupper($method) != 'GET'){
            $curlopt[CURLOPT_CUSTOMREQUEST] = strtoupper($method);
            $curlopt[CURLOPT_POSTFIELDS] = $parameters_string;
        }
        elseif($parameters_string){
            $client->url .= strpos($client->url, '?')? '&' : '?';
            $client->url .= $parameters_string;
        }
        
        if($client->options['base_url']){
            if($client->url[0] != '/' && substr($client->options['base_url'], -1) != '/')
                $client->url = '/' . $client->url;
            $client->url = $client->options['base_url'] . $client->url;
        }
        $curlopt[CURLOPT_URL] = $client->url;
        echo $client->url.' ';
        
        if($client->options['curl_options']){
            // array_merge would reset our numeric keys.
            foreach($client->options['curl_options'] as $key => $value){
                $curlopt[$key] = $value;
            }
        }
        curl_setopt_array($client->handle, $curlopt);
        curl_setopt($client->handle, CURLOPT_HTTPHEADER, array('authorization: Bearer '.$client->options['access_token']));
        
        $client->parse_response(curl_exec($client->handle));
        $client->info = (object) curl_getinfo($client->handle);
        $client->error = curl_error($client->handle);
        
        curl_close($client->handle);
        return $client;
    }
    
    public function format_query($parameters, $primary='=', $secondary='&'){
        $query = "";
        foreach($parameters as $key => $value){
            $pair = array(urlencode($key), urlencode($value));
            $query .= implode($primary, $pair) . $secondary;
        }
        return rtrim($query, $secondary);
    }
    
    public function parse_response($response){
        $headers = array();
        $http_ver = strtok($response, "\n");
        
        while($line = strtok("\n")){
            if(strlen(trim($line)) == 0) break;
            
            list($key, $value) = explode(':', $line, 2);
            $key = trim(strtolower(str_replace('-', '_', $key)));
            $value = trim($value);
            if(empty($headers[$key]))
                $headers[$key] = $value;
            elseif(is_array($headers[$key]))
                $headers[$key][] = $value;
            else
                $headers[$key] = array($headers[$key], $value);
        }
        
        $this->headers = (object) $headers;
        $this->response = strtok("");
    }
    
    public function get_response_format(){
        if(!$this->response)
            throw new CoinsBillException(
                "A response must exist before it can be decoded.");
        
        // User-defined format. 
        if(!empty($this->options['format']))
            return $this->options['format'];
        
        // Extract format from response content-type header. 
        if(!empty($this->headers->content_type))
        if(preg_match($this->options['format_regex'], $this->headers->content_type, $matches))
            return $matches[2];
        
        throw new CoinsBillException(
            "Response format could not be determined.");
    }
    
    public function decode_response(){
        if(empty($this->decoded_response)){
            $format = $this->get_response_format();
            if(!array_key_exists($format, $this->options['decoders']))
                throw new CoinsBillException("'${format}' is not a supported ".
                    "format, register a decoder to handle this response.");
            
            $this->decoded_response = call_user_func(
                $this->options['decoders'][$format], $this->response);
        }
        
        return $this->decoded_response;
    }
}