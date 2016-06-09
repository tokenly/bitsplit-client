<?php
namespace Tokenly\BitsplitClient;
use GuzzleHttp\Client as GuzzleClient;
use Exception;
class Client
{
    protected $api_url = false;
    protected $client_key = false;
    protected $client_secret = false;
    
	function __construct($host, $key, $secret)
	{
        $this->api_url = rtrim($host, '/').'/api/v1/';
        $this->client_key = $key;
        $this->client_secret = $secret;
	}

    public function createDistribution($asset, $address_list, $use_fuel = true, $opts = array())
    {
        $params = array();
        $params['asset'] = $asset;
        $params['address_list'] = $address_list;
        $params['use_fuel'] = $use_fuel;
        if(isset($opts['label'])){
            $params['label'] = $opts['label'];
        }
        if(isset($opts['webhook'])){
            $params['webhook'] = $opts['webhook'];
        }
        if(isset($opts['hold']) AND intval($opts['hold']) == 1){
            $params['hold'] = 1;
        }
        if(isset($opts['value_type'])){
            $params['value_type'] = $opts['value_type'];
            if($opts['value_type'] == 'percent'){
                if(!isset($opts['asset_total']) OR floatval($opts['asset_total']) <= 0){
                    throw new Exception('asset_total required when using percentage value type');
                }
                $params['asset_total'] = $opts['asset_total'];
            }
        }
        return $this->call('POST', 'distribute/create', $params, true);
    }
    
    public function getDistribution($id)
    {
        $call = $this->call('GET', 'distribute/'.$id);
        return $call;
    }
    
    public function listDistributions()
    {
        $call = $this->call('GET', 'distribute');
        return $call;
    }
    
    public function updateDistribution($id, $data = array())
    {
        $params = array();
        if(isset($data['label'])){
            $params['label'] = $data['label'];
        }
        if(isset($data['hold'])){
            $params['hold'] = $data['hold'];
        }
        if(isset($data['webhook'])){
            $params['webhook'] = $data['webhook'];
        }
        return $this->call('PATCH', 'distribute/'.$id, $params, true);
    }
    
    public function deleteDistribution($id)
    {
        $call = $this->call('DELETE', 'distribute/'.$id, array(), true);
        return $call;
    }
    
    public function getFuelInfo()
    {
        $call = $this->call('GET', 'self');
        return $call;
    }
	
    
    protected function call($method, $endpoint, $params = array(), $req_signed = false)
    {
        $client = new GuzzleClient(array('base_uri' => $this->api_url));
        $opts = array('query' => 'key='.$this->client_key, 'json' => $params);
        if($req_signed){
            $request_hash = hash('sha256', http_build_query($params).'&'.$opts['query'].$this->client_secret);
            $opts['query'] .= '&request_hash='.$request_hash;
        }
        $response = $client->request($method, $endpoint, $opts);
        $json = json_decode($response->getBody(), true);
        if(!is_array($json) OR !isset($json['result'])){
            throw new Exception('Unknown error calling Bitsplit API');
        }
        if(!$json['result']){
            if(isset($json['error'])){
                throw new Exception($json['error']);
            }
            return false;
        }
        return $json['result'];
    }
    
}
