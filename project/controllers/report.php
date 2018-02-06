<?php
/**
 * Elly Framework
 * http://333.kg
 *
 * @author CS-SOFT <www.333.kg>
 * @copyright CS-SOFT 2014
 */
if (!defined('CS-SOFT')) die('access denited!');

class Report extends Controller
{

    function index()
    {
        $this->loadTheme();

        $this->theme->date_1 = date('01.m.Y'); //date('d.m.Y', strtotime('-1 month'));
        $this->theme->date_2 = date('d.m.Y', strtotime('+1 day'));
    }

    function excel()
    {
        header("Content-Type: application/vnd.ms-excel; charset=utf-8");
        header("Content-type: application/x-msexcel; charset=utf-8");
        header("Content-Disposition: attachment; filename=kassa.xsl");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private",false);

        $_POST = $_GET;

        $this->show();

        die( '<style>
                table{ border-collapse: collapse; border-spacing: 0; border: 1px solid #ddd;}
                td, th{ border: 1px solid #ddd; }
            </style>'.
            $this->theme->result() );
    }

    function show()
    {
        global $lang;

        $months = explode(',',$lang['months']);

        $date_report = DATE_REPORT ? 'date_report' : 'date_system';

        switch($_POST['form'])
        {

            case 1:

                $this->loadTheme('', 'report_1');

                $dt_1 = $this->core->formatDate($_POST['date_11'], 'Y-m-d'); //!датапикер глючит если имена одинаковые
                $dt_2 = $this->core->formatDate($_POST['date_22'], 'Y-m-d'); //!датапикер глючит если имена одинаковые

                $tb = $this->core->table('view_report_1')
                                 //->where("status > -1 AND date_system BETWEEN '$dt_1' AND '$dt_2'")
                                 ->where("status > -1 AND date_end > '$dt_1' AND date_start < '$dt_2'")
                                 ->group('struct, fio, code_struct, code_user, is_photo')
                                 ->range('struct, fio, code_struct, code_user, is_photo,
                                        SUM(price) as price,
                                        COUNT(*) as count,
                                        SUM(CASE status WHEN 1 THEN price ELSE 0 END) AS price_1,
                                        SUM(CASE status WHEN 2 THEN price ELSE 0 END) AS price_2,
                                        SUM(CASE status WHEN 3 THEN price ELSE 0 END) AS price_3,
                                        SUM(CASE status WHEN 1 THEN 1 ELSE 0 END) AS count_1,
                                        SUM(CASE status WHEN 2 THEN 1 ELSE 0 END) AS count_2,
                                        SUM(CASE status WHEN 3 THEN 1 ELSE 0 END) AS count_3');

                foreach($tb as $k => $v)
                {
                    $tb[$k]['proc_1']   = round(($v['price_1'] / $v['price'])*100, 0);
                    $tb[$k]['proc_2']   = round(($v['price_2'] / $v['price'])*100, 0);
                    $tb[$k]['proc_3']   = round(($v['price_3'] / $v['price'])*100, 0);

                    $tb[$k]['avatar']   = elly::avatar($v['is_photo'], $v['code_user']);
                }
                $this->theme->table('report', $tb);
                break;


        }

        //$this->json($tb);
    }

    function detal()
    {
        $id     = intval($_POST['code']);
        $dt_1   = $this->core->formatDate($_POST['date_11'], 'Y-m-d'); //!датапикер глючит если имена одинаковые
        $dt_2   = $this->core->formatDate($_POST['date_22'], 'Y-m-d'); //!датапикер глючит если имена одинаковые

        $tb     = $this->core->table('view_report_1')
                            //->where("code_struct = ".$id." AND status > -1 AND date_system BETWEEN '$dt_1' AND '$dt_2'")
                            ->where("code_struct = ".$id." AND status > -1 AND date_end > '$dt_1' AND date_start < '$dt_2'")
                            ->sort('status, date_end, title')
                            ->range();
        $this->json($tb);
    }
}

?>