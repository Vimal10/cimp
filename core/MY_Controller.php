<?php

/**
 * Description of MY_Controller
 *
 * @author admin
 */
class MY_Controller extends CI_Controller {

    protected $_layout = null;
    //
    protected $_kernel = [];
    //
    protected $_cur_method = null;
    //
    protected $before = [];
    //
    protected $after = [];
    //
    protected $filter = [];

    public function __construct()
    {
        parent::__construct();

        log_message('info', 'MY_Controller construct called');
        //init kernal
        $this->loadKernel();

        $this->load->library('session');
    }

    /**
     * Is api called 
     * 
     * @return boolean
     */
    protected function _isApi()
    {
        if (ENVIRONMENT == ('development' || 'testing'))
        {
            if (isset($_GET['call']) && $_GET['call'] == 'mobile')
            {
                return true;
            }
        }

        return isset($_SERVER['HTTP_KEY']);
    }

    /**
     * Check site is undermaintance or not
     * 
     * @return boolean
     */
    public function _down()
    {
        return file_exists('./down');
    }

    /**
     * 
     * @param string $template
     * @param array $data
     */
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
    public function redirect($url, $array = [], $method = 'auto', $code = NULL)
    {
        if ($this->_isApi())
        {
            return $array;
        }

        return redirect($url, $method, $code);
    }

    /**
     * Set Master Layout Template
     * 
     * @param string $layout
     */
    public function setLayout($layout)
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
    public function render($template, $data = [])
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
            return $_output;
        }
        //else append to the master
        $data['content'] = $_output;

        $_layout_tpl = $this->_layout . '.tpl.php';

        //hook for include widgets
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

        log_message('info', 'MY_Controller _rmap called');

        $this->_cur_method = $method;

        //global middleware
        $_m = $this->run_middleware($this->_kernel['global_middlewares']);

        if (!$_m instanceof self)
        {
            return;
        }

        log_message('info', 'BEFORE middlewares initialized');

        //private middleware

        $_m = $this->run_local_middleware($this->before);

        if (!$_m instanceof self)
        {
            return;
        }

        log_message('info', 'Local middlewares initialized');



        //run method
        $this->_run_method($params);

        log_message('info', 'Method called ' . $this->_cur_method);

        $_m = $this->run_local_middleware($this->after);

        if (!$_m instanceof self)
        {
            return;
        }

        $_m = $this->run_middleware($this->_kernel['after_middlewares']);
        if (!$_m instanceof self)
        {
            return;
        }

        log_message('info', 'AFTER middlewares initialized');


//        $this->output->_display();
        log_message('info', 'output display');
    }

    /**
     * Run Controller/Method
     * 
     * @param array $params
     * @return mix
     */
    public function _run_method($params)
    {
        $_output = call_user_func_array([$this, $this->_cur_method], $params);
        $this->output->set_output($this->_mod_output($_output));
    }

    public function _mod_output($_output)
    {
        if ($this->_isApi() || is_array($_output) || is_object($_output))
        {
            $this->output->set_header('Content-Type: application/json');
            $_output = json_encode($_output);
        }

        return $_output;
    }

    /**
     * Run Global Middleware
     * 
     * @param array $middlewares
     * @return boolean
     */
    public function run_middleware($middlewares)
    {
        if (empty($middlewares))
        {
            return $this;
        }
        //global middleware after
        foreach ($middlewares as $file)
        {
            require_once APPPATH . 'middlewares/' . $file . '.php';
            $output = (new $file())->handle($this, function($this) {
                return $this;
            });
            if (!$output instanceof self)
            {
                return $this->output->set_output($this->_mod_output($output));
            }
            return $this;
        }
    }

    /**
     * Run Local Middleware
     * 
     * @param array $middlewares
     * @return \MY_Controller
     */
    public function run_local_middleware($middlewares)
    {
        if (empty($middlewares))
        {
            return $this;
        }
        //private middleware
        foreach ($middlewares as $name => $functions)
        {
            if (in_array('*', $functions) || in_array($this->_cur_method, $functions))
            {
                $_file = $this->_kernel['middlewares'][$name];
                require_once APPPATH . 'middlewares/' . $_file . '.php';
                $output = (new $_file())->handle($this, function($this) {
                    return $this;
                });
            }
            if (!$output instanceof self)
            {
                return $this->output->set_output($this->_mod_output($output));
            }
            return $this;
        }
    }

    /**
     * Get Current Module (HMVC)
     * 
     * @return string
     */
    public function current_module()
    {
// Modular Separation / Modular Extensions has been detected
        if (method_exists($this->router, 'fetch_module'))
        {
            $module = $this->router->fetch_module();
            return (!empty($module)) ? $module : '';
        }

        return '';
    }

    /**
     * Load Kernel Array
     */
    protected function loadKernel()
    {
        $this->_kernel = require_once APPPATH . 'config/kernel.php';
    }

}
