<?php
namespace surveyzilla\application\model;
class Request
{
    private $request = array();
    public function getParam($key){
        return $this->request[$key];
    }
    public function setParam($key,$val){
        $this->request[$key] = $val;
    }
    public function setParams($paramsArray){
        foreach ($paramsArray as $key => $val)
            $this->request[$key] = $val;
    }
    public function isSetParam($key){
        return !empty($this->request[$key]);
    }
}