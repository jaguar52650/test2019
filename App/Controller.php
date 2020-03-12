<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 29.03.2019
 * Time: 20:07
 */

interface ControllerInterface
{
    public function setRouter(Router $router);
}

class Controller implements ControllerInterface
{
    protected $router;

    public function setRouter(Router $router)
    {
        $this->router = $router;
        return $this;
    }

}