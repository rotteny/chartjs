<?php

namespace Rotteny\Chartjs;

class ChartJs {
    public $width               = 400; // opt
    public $height              = 400; // opt
    public $backgroundColour    = "white"; // opt
    public $payload             = []; // required

    public $PATH_CHARTJS        = "./index.js";

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

        $this->isChartSet();
    }
    
    public function isChartSet() {
        if(file_exists($this->PATH_CHARTJS)) {
           return true; 
        }
        throw new \Exception("O projeto node não foi encontrado no caminho informado.");
    }

    public function setPayload($params) {
        $this->payload = $params['payload'];
    }

    public function getChart() {
        $chart = json_encode([
            'width'             => $this->width,
            'height'            => $this->height,
            'backgroundColour'  => $this->backgroundColour,
            'payload'           => (object)$this->payload
        ]);
        $str_exec   = "node {$this->PATH_CHARTJS} '{$chart}'";
        
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

    public static function renderChart($payload, $width = null, $height = null, $backgroundColour = null) {
        $ChartJs = new self(compact('payload', 'width', 'height', 'backgroundColour'));
        return $ChartJs->getChart();
    }
}