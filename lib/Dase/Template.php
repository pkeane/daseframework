<?php

class Dase_Template {

    protected $twig;
    protected $vars = array();

    public function __construct($request)
    {
        if ($request->module) {
            $this->template_dir = BASE_PATH.'/modules/'.$request->module.'/templates';
        } else {
            $this->template_dir = BASE_PATH.'/templates';
        }

        if ($request->module) {
            $this->assign('module_root', $request->module_root.'/');
        }

        $this->assign('app_root', $request->app_root);
        $this->assign('request', $request);
        $this->assign('main_title', MAIN_TITLE);
    }

    public function assign($key,$val)
    {
        $this->vars[$key] = $val;

    }

    public function display($resource_name)
    {
        echo $this->fetch($resource_name);
    }

    public function fetch($resource_name)
    {
        $loader = new Twig_Loader_Filesystem($this->template_dir);
        $this->twig = new Twig_Environment($loader, array(
            'cache' => TEMPLATE_CACHE_DIR,
            'auto_reload' => TEMPLATE_AUTO_RELOAD,
        ));
        return $this->twig->render($resource_name,$this->vars);
    }
}
