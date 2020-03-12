<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 29.03.2019
 * Time: 20:07
 */


class DefaultController extends Controller
{
    public $alias = array('default'); // todo alias routes

    public function indexAction()
    {
        echo '<a href="/report/">Отчет</a>';
    }
}