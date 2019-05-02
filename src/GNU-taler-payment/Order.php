<?php


class Order{

    public $amount;
    public $summary;
    public $fullfillment_url;

    function __construct($amount, $summary, $fullfillment_url){
        $this->amount = $amount;
        $this->summary = $summary;
        $this->fullfillment_url = $fullfillment_url;
    }


    public function convertToJSON($order){

    }
}