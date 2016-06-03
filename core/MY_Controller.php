<?php

/**
 * Description of MY_Controller
 *
 * @author admin
 */
class MY_Controller extends MX_Controller {

    protected $_layout = null;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Is api called 
     * 
     * @return boolean
     */
    protected function _isApi()
    {
        return true;
    }

    /**
     * Simple Redirect Wrapper to Handle Api Request
     * 
     * @param string $url
     * @param mix $type
     * @param array $array
     * @return array/null
     */
    protected function redirect($url, $type = null, $array = [])
    {
        if ($this->_isApi())
        {
            return $array;
        }

        redirect($url, $type);
        return;
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
        $_output = $this->load->view($_tpl, $data, true);

        //if no layout return as it
        if (is_null($this->_layout))
        {
            $this->output->append_output($_output);
            return;
        }

        //else append to the master
        $data['content'] = $_output;

        $_layout_tpl = $this->_layout . '.tpl.php';

        $this->load->view($_layout_tpl, $data);
    }

}
