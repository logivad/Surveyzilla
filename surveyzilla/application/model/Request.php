<?php
namespace surveyzilla\application\model;
/**
 * Stores request parameters (which are usually taken from $_REQUEST)
 */
class Request
{
    // Request parameters. Are usually taken from $_REQUEST
    private $params = array();
    /**
     * Stores request parameters, which are usually taken from $_REQUEST
     * @param array $params An array (can be $_REQUEST)
     */
    public function __construct(array $params) {
        $this->setParams($params);
    }
    /**
     * Get request parameter
     * @param mixed $key
     * @return mixed
     */
    public function get($key){
        return isset($this->params[$key]) ? $this->params[$key] : null;
    }
    /**
     * Set request parameter
     * @param mixed $key
     * @param mixed $val
     */
    public function set($key,$val){
        $this->params[$key] = $val;
    }
    /**
     * Set all request parameters from an array
     * @param array $params
     */
    private function setParams(array $params){
        foreach ($params as $key => $val)
            $this->params[$key] = $val;
    }
    /**
     * Check whether a given request parameter is set
     * @param mixed $key
     * @return bool
     */
    public function isSetParam($key){
        return isset($this->params[$key]);
    }
    /**
     * Filters email request parameter
     * @return null
     */
    public function filterEmail() {
        if (empty($this->params['email'])) {
            return;
        }
        $this->params['email'] = \filter_var(
            $this->params['email'], 
            FILTER_VALIDATE_EMAIL
        );
    }
    /**
     * Filters password request parameter
     * @return null
     */
    public function filterPassword() {
        if (empty($this->params['password'])) {
            return;
        }
        $this->params['password'] = \filter_var(
            $this->params['password'], 
            FILTER_VALIDATE_REGEXP,
            array('options'=>array('regexp'=>'/[a-zA-Z0-9_!-.]{6,}/'))
        );
    }
    /**
     * Filters request parameters used for poll running
     * @return null
     */
    public function filterPollRunParams() {
        if (isset($this->params['poll'])) {
            $this->params['poll'] = \filter_var($this->params['poll'], FILTER_VALIDATE_INT);
        }
        if (isset($this->params['item'])) {
            $this->params['item'] = \filter_var($this->params['item'], FILTER_VALIDATE_INT);
        }
        if (isset($this->params['custopt'])) {
            $this->params['custopt'] = \filter_var($this->params['custopt'], FILTER_SANITIZE_SPECIAL_CHARS);
        }
        if (isset($this->params['opts'])) {
            $this->params['opts'] = \filter_var_array($this->params['opts'], FILTER_SANITIZE_SPECIAL_CHARS);
        }
    }
}