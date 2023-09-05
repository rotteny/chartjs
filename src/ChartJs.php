<?php

namespace Rotteny\Chartjs;

class ChartJs {
    public $width               = 400; // opt
    public $height              = 400; // opt
    public $backgroundColour    = "white"; // opt
    public $payload             = []; // required

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
    }
    
    /**
     * Verifica se o caminho para a aplicação em node foi informado corretamente
     */
    public function isChartSet() {
        if(file_exists($this->node_path)) {
           // Testa se o index responde a chamada de teste corretamente
           $str_exec   = "node {$this->node_path} teste";
           
           $return     = exec($str_exec);
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
        
        $return     = exec($str_exec);
        $returnObj  = json_decode($return);

        if(!isset($returnObj->status) || !$returnObj->status) {
            if(isset($returnObj->message)) {
                throw new \Exception($returnObj->message);
            }
            throw new \Exception($return);
        }

        return $returnObj->image;
    }

    public static function renderChart($payload, $width = null, $height = null, $backgroundColour = null, $node_path = null) {
        $ChartJs = new ChartJs(compact('payload', 'width', 'height', 'backgroundColour', 'node_path'));
        return $ChartJs->getChart();
    }
}