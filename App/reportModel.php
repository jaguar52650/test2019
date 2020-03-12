<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 30.03.2019
 * Time: 9:52
 */

class reportModel
{
    protected $result;
    protected $queryParam = [];

    public function makeReport(): reportModel
    {
        $this->result = DB::run(
            self::getQuery($this->queryParam)
        )->fetchAll(PDO::FETCH_UNIQUE);
        return $this;
    }

    public function getReport(): ?array
    {
        return $this->result;
    }

    public function setParam($param): reportModel
    {
        $this->queryParam = $param;
        return $this;
    }

    public static function filterEmail($email)
    {
        trigger_error('Method ' . __METHOD__ . ' is deprecated', E_USER_DEPRECATED);
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * @param $email
     * @return false|int
     * источник https://goo.gl/rK2DaT
     * Добавил домены кириллицей
     */
    public static function validateEmail($email)
    {
//      return self::filterEmail($email);
        return preg_match('/^((([0-9A-Za-z]{1}[-0-9A-z\.]{1,}[0-9A-Za-z]{1})|([0-9А-Яа-я]{1}[-0-9А-я\.]{1,}[0-9А-Яа-я]{1}))@([-A-Za-zА-Яа-я]{1,}\.){1,2}[-A-Za-zА-Яа-я]{2,})$/u', $email);
    }

    public function validateColEmail($col = 'Email'): reportModel
    {
        foreach ($this->result as $k => &$row) {
            if ($row[$col]) {
                $row[$col] = [
                    'val'   => $row[$col],
                    'valid' => self::validateEmail($row[$col]),
                ];
            }
        }
        unset($row);
        return $this;
    }

    /**
     * @return string
     * используем mysql8
     * todo выходные и праздники, считать время для каждого
     */
    public static function getQuery($param)
    {
        $query = '            
	
                WITH RECURSIVE company_path (id, Employer, path) AS
                (
                  SELECT id, Employer, COALESCE(Name,\'no boss\') as path
                    FROM employer
                    WHERE Employer IS NULL
                  UNION ALL
                  SELECT c.id, c.Employer,  CONCAT(cp.path, \' => \',c.Name)
                    FROM company_path AS cp JOIN employer AS c
                      ON cp.id = c.Employer
                )
            
            
            SELECT i.id,i.path,j.low_work_days,j.Email,i.Employer pid,worked_sec,j.Info,j.DD FROM company_path i
            
            LEFT JOIN (
            
                SELECT x.id,
                    x.NAME, 
                    x.Email,
                    x.Info,
                    SEC_TO_TIME(SUM(x.dayWorkSec)) AS worked,
                    SUM(x.dayWorkSec) AS worked_sec,
                    SUM(D) low_work_days,
                    GROUP_CONCAT(x.dayWorkSec),
                    GROUP_CONCAT( 
									 	IF(
											 x.dayWorkSec<8*3600,
											 CONCAT(x.dt,"=",x.dayWorkSec),
											 ""
										 )
									) AS DD
                     FROM
                    
                    (
                    
                        select 
                            ed.id, 
                            ed.dt,
                            ed.Name,
                            ed.Email,
                            ed.Info,
                            SUM(TIME_TO_SEC(COALESCE(CAST(t.Time AS TIME),0))) AS dayWorkSec, 
                            SUM(TIME_TO_SEC(COALESCE(CAST(t.Time AS TIME),0)))<8*3600 AS D
                            
                        FROM 
                            (
                                select emp.id, days.dt,emp.Name,emp.Email,emp.Info
                        
                                    from
                                        employer AS emp
                                        
                                    inner join 
                                        (
                                            WITH RECURSIVE t as (
                                                select \'#date_from#\' as dt
                                              UNION
                                                SELECT DATE_ADD(t.dt, INTERVAL 1 DAY) FROM t WHERE DATE_ADD(t.dt, INTERVAL 1 DAY) <= \'#date_to#\' 
                                            )
                                            select * FROM t
                                        ) AS days
                        
                                group by emp.id, days.dt
                            ) AS ed
                            left join timesheet t ON t.EmployeedId = ed.id and t.Date = ed.dt
                            
                        group by ed.id, ed.dt
                        
                    ) AS x
                    
                    GROUP BY x.id
                
                ) j 
            ON i.id = j.id
            ORDER BY i.path;

        ';
        //        $queryParam
        foreach ($param as $code => $val) {
            $query = str_replace($code, $val, $query);
        }
//        echo $query;

        return $query;
    }

    /**
     * @return reportModel
     * Подчиненный всегда после начальника
     */
    public function countMap(): reportModel
    {
        foreach (array_reverse($this->result, true) as $k => $row) {
            if (!isset($this->result[$k]['d']))
                $this->result[$k]['d'] = 0;
            $this->result[$k]['d'] += $row['worked_sec'];
            if (
            $row['pid']
//                && $row['worked_sec'] > 0
            ) {
                if (!isset($this->result[$row['pid']]['d']))
                    $this->result[$row['pid']]['d'] = 0;
                $this->result[$row['pid']]['d'] += $this->result[$k]['d'];
            }
        }
        foreach ($this->result as $k => $row) {
            $this->result[$k]['d'] = self::seconds_to_time($row['d']);
            $this->result[$k]['worked_sec'] = self::seconds_to_time($row['worked_sec']);

        }
//        var_export($this->result);
        return $this;
    }

    public static function seconds_to_time($secs)
    {
        $dt = new DateTime('@' . $secs, new DateTimeZone('UTC'));
        $res = '';
        $ar = [
            'д' => 'z',
            'ч' => 'G',
            'м' => 'i',
            'с' => 's', //..
        ];
        foreach ($ar as $k => $v) {
            if (
                $dt->format($v) > 0
                || !empty($res)
            ) {
                $res .= $dt->format($v) . ' ' . $k . ' ';
            }
        }
        if (empty($res))
            $res = 0;
        return $res;
    }
}