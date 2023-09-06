<?php

namespace Rotteny\Chartjs;

class ChartJs {
    public $width               = 400; // opt
    public $height              = 400; // opt
    public $backgroundColour    = "white"; // opt
    public $payload             = []; // required

    /**
     * Requisição API
     */
    public $api_url             = 'https://sistema.safetydocs.com.br/nodechartjs/';
    public $api_user            = 'safetydocs';
    public $api_pass            = '33e3PY26zfXuKB4efQHGtW#V3BI8p1NMT0w1O3Ql1P9Oec@kB7Pg2MZGBtqFQjiVCQ';

    /**
     * Localização da aplicação node que irá executar a geração do grafico
     */
    public $node_path           = "index.js";

    public function __construct($params = []) {
        if(isset($params['width'])) {
            $this->width = $params['width'];
        }
        if(isset($params['height'])) {
            $this->height = $params['height'];
        }
        if(isset($params['backgroundColour'])) {
            $this->backgroundColour = $params['backgroundColour'];
        }
        if(isset($params['payload'])) {
            $this->payload = $params['payload'];
        }
        if(isset($params['node_path'])) {
            $this->node_path = $params['node_path'];
        }
        if(isset($params['api_url'])) {
            $this->api_url = $params['api_url'];
        }
        if(isset($params['api_user'])) {
            $this->api_user = $params['api_user'];
        }
        if(isset($params['api_pass'])) {
            $this->api_pass = $params['api_pass'];
        }
    }
    
    /**
     * Verifica se o caminho para a aplicação em node foi informado corretamente
     */
    public function isChartSet() {
        if(file_exists($this->node_path)) {
           // Testa se o index responde a chamada de teste corretamente
           $str_exec   = "node {$this->node_path} teste";
           
           $return     = shell_exec($str_exec);
           $returnObj  = json_decode($return);
   
           if(isset($returnObj->status) && $returnObj->status) {
               return true;
           }
        }

        throw new \Exception("O projeto node não foi encontrado no caminho informado.");
    }

    public function setNodePath($node_path) {
        $this->node_path = $node_path;
    }

    public function setPayload($payload) {
        $this->payload = $payload;
    }

    public function getChart() {
        $this->isChartSet();

        $chart = json_encode([
            'width'             => $this->width,
            'height'            => $this->height,
            'backgroundColour'  => $this->backgroundColour,
            'payload'           => (object)$this->payload
        ]);
        $str_exec   = "node {$this->node_path} '{$chart}'";
        
        $return     = shell_exec($str_exec);
        $returnObj  = $this->validateObject($return);

        return $returnObj->image;
    }

    public function getHttpChart() {
        $chart = urlencode(json_encode([
            'width'             => $this->width,
            'height'            => $this->height,
            'backgroundColour'  => $this->backgroundColour,
            'payload'           => (object)$this->payload
        ]));
        $query  = "chart={$chart}";
        $url    = "{$this->api_url}?{$query}";

        if(!function_exists("curl_init")) {
            throw new \Exception("Biblioteca CURL não está habilitada");
        }

        $ch     = curl_init( $url );
        curl_setopt( $ch
                    , CURLOPT_HTTPHEADER
                    , [
                        'Authorization: Basic ' . base64_encode( $this->api_user . ':' . $this->api_pass ),
                        'Content-Type: application/json',
                        'Content-Length: ' . strlen($query)
                    ]);
        $return     = curl_exec($ch);
        $httpCode   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close( $ch );
        if($httpCode != "200") {
            throw new \Exception("Error ({$httpCode})");
        }

        $returnObj  = $this->validateObject($return);
        return $returnObj->image;
    }

    public function validateObject($return) {
        $returnObj  = json_decode($return);
        if(!isset($returnObj->status) || !$returnObj->status) {
            if(isset($returnObj->message)) {
                throw new \Exception($returnObj->message);
            }
            throw new \Exception($return);
        }
        return $returnObj;
    }

    public static function renderChart($payload, $width = null, $height = null, $backgroundColour = null, $node_path = null) {
        $ChartJs = new ChartJs(compact('payload', 'width', 'height', 'backgroundColour', 'node_path'));
        return $ChartJs->getChart();
    }

    public static function renderHttpChart($payload, $width = null, $height = null, $backgroundColour = null, $api_url = null, $api_user = null, $api_pass = null) {
        $ChartJs = new ChartJs(compact('payload', 'width', 'height', 'backgroundColour', 'api_url', 'api_user', 'api_pass'));
        return $ChartJs->getHttpChart();
    }
}