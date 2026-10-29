<?php

    function if_null($result) {
        if (is_null($result)) {
            return 'NULL';
        } else if (is_string($result) or is_bool($result)) {
            return "'$result'";
        }
        return $result;
    }  
   
    function get_lat_long($address){
     
        $region = 'BR';
        $address = str_replace(" ", "+", $address);
        
        $json = file_get_contents("http://maps.google.com/maps/api/geocode/json?address=$address&sensor=false&region=$region");
        $json = json_decode($json);
        
        if ($json->{'status'} != 'ZERO_RESULTS') {
            $lat = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lat'};
            $lng = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lng'};
            
            return array('lat'=> $lat, 'lng' => $lng);
        } else {
            return null;
        }
    }  

    function GeoCodeAddress($address){
        $geo = new GeocoderRequest();
        $geo->forwardSearch($address);
        return $geo->EnderecoList;
    }

    class Estado{
        public $Descricao = '';
        public $SiglaUF   = '';
    }

    class Cidade{
        public $Descricao = '';
        public $Estado;

        function __construct() {
          $this->Estado = new Estado();
        }        
    }

    class addressComponent{
        private $address_long;

        public  $Id           = 0;
        public  $Logradouro   = '';
        public  $Numero       = '';
        public  $Complemento  = '';
        public  $Latitude     = 0;
        public  $Longitude    = 0;
        public  $Bairro       = '';
        public  $Cep          = 0;
        public  $Cidade;

        function __construct() {
          $this->Cidade = new Cidade();
        }        

        public function getaddress_long() {
            return $this->Logradouro.', '.$this->Numero.' - '.$this->Bairro.', '.$this->Cidade->Descricao.' - '.$this->Cidade->Estado->SiglaUF;
        }        
    }

    class GeocoderRequest {   
        private $addressResponse = '';
        private $response  = '';
        public  $EnderecoList = array();
        
        private function searchAddressComponents($type) {
            foreach($this->addressResponse->address_components as $k=>$found){
                if(in_array($type, $found->types)){
                    return $found;
                } 
            }
            return false;
        }

        public function forwardSearch($address)
        {
             return $this->_sendRequest(urlencode(stripslashes($address)));
        } // end forward        

        private function _sendRequest($search)
        {
            $region = 'BR';        
            $json = file_get_contents("http://maps.google.com/maps/api/geocode/json?address=$search&sensor=false&region=$region");    
            
            $this->response = json_decode($json);

            if ($this->response->status == "OK") {
                for ($i=0; $i<=count($this->response->results)-1; $i++){
                    $this->addressResponse = $this->response->results[$i];
                    $this->_setDefaults();
                }
                return $this->response;
            } else { 
                return false;
            }
        } // end request
        
        private function _setDefaults()    
        {
            $obj = new addressComponent();

            $street_number = $this->searchAddressComponents("street_number");
            $obj->Numero = $street_number != false ? $street_number->short_name : '';
            
            $route = $this->searchAddressComponents("route");
            $obj->Logradouro = $route != false ? $route->long_name : '';

            $state = $this->searchAddressComponents("administrative_area_level_1");
            $obj->Cidade->Estado->Descricao = $state->long_name;
            $obj->Cidade->Estado->SiglaUF = $state->short_name;

            $subLocality = $this->searchAddressComponents("sublocality_level_1");
            $obj->Bairro = $subLocality != false ? $subLocality->long_name : '';

            $city = $this->searchAddressComponents("administrative_area_level_2");
            $obj->Cidade->Descricao = $city != false ? $city->short_name : '';

            $postal = $this->searchAddressComponents("postal_code");
            $obj->Cep = $postal != false ? $postal->long_name : 0;
            $obj->Cep = str_replace("-", "", $obj->Cep);            

            $obj->Latitude = $this->addressResponse->geometry->location->lat;
            $obj->Longitude = $this->addressResponse->geometry->location->lng;

            $this->EnderecoList[] = $obj;

        } // end set
        
    } // end class

?>