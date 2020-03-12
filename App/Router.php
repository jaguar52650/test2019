<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 29.03.2019
 * Time: 21:03
 */

class Router
{
    private static $_instance = null;

    public static function getInstance()
    {
        if (self::$_instance != null) {
            return self::$_instance;
        }

        return new self;
    }

    private function __clone() { }

    private function __wakeup() { }

    private function __construct()
    {
        self::$url = self::getUrl();
    }

    private static $url;
    private static $param;
    private static $params;

    public static function route()
    {
        $arRoute = ['', 'Default', 'index', ''];
        list($root, $controllerName, $actionName, self::$param) = self::getArPath(self::$url) + $arRoute;
        $controllerName .= 'Controller';
        $actionName .= 'Action';

        self::$params = $_REQUEST;

        if (class_exists($controllerName)) {
            $controller = new $controllerName();
        } else {
            $controller = new DefaultController();
        }
        $controller->setRouter(self::getInstance());
        if (method_exists($controller, $actionName)) {
            $controller->$actionName();
        } else {
            $controller->indexAction();
        }
    }

    public function getParam()
    {
        return self::$param;
    }

    public function getParams()
    {
        return self::$params;
    }

    protected static function getUrl()
    {
        return $_SERVER['REQUEST_URI'];
    }

    protected static function getArPath($url = ''): array
    {
        return array_diff(explode('/', $url), ['']);
    }
}