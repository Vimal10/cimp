<?php

/**
 * $this->flash->error('this is error bhai');
 */
class Flash {

    //ci intance
    protected $ci;
    //html to return
    protected $html = null;
    //alias for error types
    protected $alias = [
            'error' => 'danger',
            'success' => 'success',
            'info' => 'info',
            'warning' => 'warning'
    ];

    /**
     * Initialize
     */
    public function __construct()
    {
        $this->ci = &get_instance();
        $this->ci->load->library('session');
    }

    /**
     * Dynamic call a function with magic method
     * 
     * @param string $name
     * @param array $args
     */
    public function __call($name, $args)
    {
        if (isset($this->alias[$name]))
        {
            $this->setMsg($this->alias[$name], $args[0], isset($args[1]) ? true : false);
        }
    }

    /**
     * 
     * @param string $type
     * @param string $message
     * @param boolean $imp
     * @return null
     */
    protected function setMsg($type, $message, $imp = false)
    {
        $msg = $type . '||' . $message;

        if ($imp)
        {
            $msg.='||true';
        }

        if ($this->ci->session->has_userdata('_flash'))
        {
            $data = $this->ci->session->userdata('_flash');
            $data[] = $msg;
            $this->ci->session->set_flashdata('_flash', $data);
            return;
        }

        $this->ci->session->set_flashdata('_flash', [$msg]);
        return;
    }

    /**
     * Return html
     * 
     * @return string/html
     */
    public function display()
    {
        $this->proccess();
        return $this->html;
    }

    /**
     * helper function
     */
    protected function proccess()
    {
        if ($this->ci->session->has_userdata('_flash'))
        {
            foreach ($this->ci->session->userdata('_flash') as $msg)
            {
                $this->set($msg);
            }
        }
    }

    /**
     * Generate Html and set according to given messages
     * 
     * @param string $msg
     */
    protected function set($msg)
    {
        $msg = trim($msg);
        $imp = null;
        $a = explode('||', $msg);
        if (count($a) == 3)
        {
            $imp = 'imp';
        }
        $type = $a[0];
        $message = $a[1];

        $this->html.=""
            . "<div class=\"alert alert-$type $imp\">$message</div>";
    }

}
