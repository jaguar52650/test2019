<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 29.03.2019
 * Time: 20:07
 */


class ReportController extends Controller
{
    public $alias = array('report'); // todo alias routes

    public function indexAction()
    {
        $params = $this->router->getParams();

        $start = new DateTime();
        if (
            isset($params['start'])
            && strtotime($params['start']) > 0
        ) {
            $start->setTimestamp(strtotime($params['start']));
        } else {
            $start->setDate(2017, 02, 10);
        }
        $finish = clone $start;
        if (
            isset($params['finish'])
            && strtotime($params['finish']) > 0
        ) {

            $finish->setTimestamp(strtotime($params['finish']));
        } else {
            $finish->add(new DateInterval('P3D'));
        }


        $report = new reportModel();
        $result = $report
            ->setParam([
                '#date_from#' => $start->format('Y-m-d'),
                '#date_to#'   => $finish->format('Y-m-d'),
            ])
            ->makeReport()
            ->validateColEmail()
            ->countMap()
            ->getReport();

        include_once 'ReportView.php';
    }

    public function detailAction()
    {
        echo 'detail';
        echo $this->router->getParam();
    }


}