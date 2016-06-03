<?php

/**
 * Description of MY_Controller
 *
 * @author admin
 */
class MY_Controller extends CI_Controller {

    protected $_layout = null;

    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->helper('url');
    }

    /**
     * Is api called 
     * 
     * @return boolean
     */
    protected function _isApi()
    {
        return false;
    }

    protected function beforeView(&$template, &$data = [])
    {
        $data['__run'] = 'su yar';
        $data['sidebar'] = 'this is sidebar';
    }

    /**
     * Simple Redirect Wrapper to Handle Api Request
     * 
     * @param string $url
     * @param array $array
     * @param mix $type
     * @return array/null
     */
    protected function redirect($url, $array = [], $type = null)
    {
        if ($this->_isApi())
        {
            return $array;
        }

        return redirect($url, $type);
    }

    /**
     * Set Master Layout Template
     * 
     * @param string $layout
     */
    protected function setLayout($layout)
    {
        $this->_layout = $layout;
    }

    /**
     * Render Final Output According Request type
     * 
     * @param string $template
     * @param array $data
     * @return mix
     */
    protected function render($template, $data = [])
    {

        //if api return data
        if ($this->_isApi())
        {
            return $data;
        }

        $_tpl = $template . '.tpl.php';

        //render template

        $this->beforeView($template, $data);

        $_output = $this->load->view($_tpl, $data, true);

        //if no layout return as it
        if (is_null($this->_layout))
        {
//            $this->output->append_output($_output);
            return $_output;
        }

        //else append to the master
        $data['content'] = $_output;

        $_layout_tpl = $this->_layout . '.tpl.php';

        $this->beforeView($this->_layout, $data);

        return $this->load->view($_layout_tpl, $data, true);
    }

    /**
     * Process before calling controller function
     * 
     * @param type $method
     * @param type $params
     */
    public function _remap($method, $params = [])
    {
//        $this->output->set_header('X-Powered-By: vimal');
        $_output = call_user_func_array([$this, $method], $params);
        if ($this->_isApi())
        {
            $this->output->set_header('Content-Type: application/json');
            $_output = json_encode($_output);
        }


        $this->output->append_output($_output);
    }

}
