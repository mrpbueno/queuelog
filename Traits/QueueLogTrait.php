<?php


namespace FreePBX\modules\Queuelog\Traits;

use PDO;

trait QueueLogTrait
{
    private function getAgentStats($post)
    {
        $start = $post['startDate'].' '.$post['startTime'];
        $end = $post['endDate'].' '.$post['endTime'];
        $queuename = $post['queuename'];
        if (!$queuename) return null;
        $sql = "SELECT queuename, agent, COUNT(agent) AS calls, SUM(data1) AS waittime, SUM(data2) AS calltime, ";
        $sql .= "FLOOR(SUM(data1)/COUNT(agent)) AS avgwaittime, FLOOR(SUM(data2)/COUNT(agent)) AS avgcalltime, ";
        $sql .= "MAX(CAST(data1 AS INT)) AS maxwaittime, MAX(CAST(data2 AS INT)) AS maxcalltime ";
        $sql .= "FROM asteriskcdrdb.queuelog WHERE ";
        $sql .= "time >= :start AND time <= :end AND queuename IN (:queuename) AND ";
        $sql .= "event IN ('COMPLETECALLER', 'COMPLETEAGENT') GROUP BY agent ORDER BY agent";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':start',$start,PDO::PARAM_STR);
        $stmt->bindParam(':end',$end,PDO::PARAM_STR);
        $stmt->bindParam(':queuename',$queuename,PDO::PARAM_STR);
        $stmt->execute();
        $calls = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $calls = is_array($calls) ? $calls : null;

        return $calls;
    }

    private function getQueueStats($post)
    {
        $start = $post['startDate'].' '.$post['startTime'];
        $end = $post['endDate'].' '.$post['endTime'];
        $queuename = $post['queuename'];
        if (!$queuename) return null;
        $sql = "SELECT queuename, COUNT(queuename) AS calls, SUM(data1) AS waittime, SUM(data2) AS calltime, ";
        $sql .= "FLOOR(SUM(data1)/COUNT(agent)) AS avgwaittime, FLOOR(SUM(data2)/COUNT(agent)) AS avgcalltime, ";
        $sql .= "MAX(CAST(data1 AS INT)) AS maxwaittime, MAX(CAST(data2 AS INT)) AS maxcalltime ";
        $sql .= "FROM asteriskcdrdb.queuelog WHERE ";
        $sql .= "time >= :start AND time <= :end AND queuename IN (:queuename) AND ";
        $sql .= "event IN ('COMPLETECALLER', 'COMPLETEAGENT') GROUP BY queuename";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':start',$start,PDO::PARAM_STR);
        $stmt->bindParam(':end',$end,PDO::PARAM_STR);
        $stmt->bindParam(':queuename',$queuename,PDO::PARAM_STR);
        $stmt->execute();
        $calls = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $calls = is_array($calls) ? $calls : null;

        return $calls;
    }

    private function getSlaStats($post)
    {
        $start = $post['startDate'].' '.$post['startTime'];
        $end = $post['endDate'].' '.$post['endTime'];
        $queuename = $post['queuename'];
        if (!$queuename) return null;
        $sql = "SELECT data1 FROM asteriskcdrdb.queuelog WHERE event IN ('CONNECT') AND ";
        $sql .= "time >= :start AND time <= :end AND queuename IN (:queuename)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':start',$start,PDO::PARAM_STR);
        $stmt->bindParam(':end',$end,PDO::PARAM_STR);
        $stmt->bindParam(':queuename',$queuename,PDO::PARAM_STR);
        $stmt->execute();
        $calls = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $total = $stmt->rowCount();
        if ($total==0) return null;
        $calls = is_array($calls) ? $calls : null;

        for ($i=15;$i<=300;$i=$i+15) {
            $sla[$i]=0;
        }

        foreach ($calls as $call) {
            for ($i=15;$i<=300;$i=$i+15) {
                if ($call['data1'] <= $i) $sla[$i]++;
            }
        }

        for ($i=15;$i<=300;$i=$i+15) {
            $res[] = [
                'sla' => $i,
                'calls' => $sla[$i],
                'delta' => ($i>15) ? $sla[$i]-$sla[$i-15] : 0,
                'percentage' => number_format($sla[$i]*100/$total,2).' %',
            ];
        }

        return $res;
    }

    private function getHangupStats($post)
    {
        $start = $post['startDate'].' '.$post['startTime'];
        $end = $post['endDate'].' '.$post['endTime'];
        $queuename = $post['queuename'];
        if (!$queuename) return null;
        $sql = "SELECT event, COUNT(event) AS calls ";
        $sql .= "FROM asteriskcdrdb.queuelog WHERE event IN ('COMPLETECALLER', 'COMPLETEAGENT') AND ";
        $sql .= "time >= :start AND time <= :end AND queuename IN (:queuename) GROUP BY event";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':start',$start,PDO::PARAM_STR);
        $stmt->bindParam(':end',$end,PDO::PARAM_STR);
        $stmt->bindParam(':queuename',$queuename,PDO::PARAM_STR);
        $stmt->execute();
        $calls = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $total = $stmt->rowCount();
        if ($total==0) return null;
        $calls = is_array($calls) ? $calls : null;
        $hangup['COMPLETECALLER'] = 0;
        $hangup['COMPLETEAGENT'] = 0;
        $hangup['calls'] = 0;

        foreach ($calls as $call) {
            $hangup[$call['event']] = $call['calls'];
            $hangup['calls'] += $call['calls'];
        }

        $res[] = [
            'event' => _('Agente desligou'),
            'calls' => $hangup['COMPLETEAGENT'],
            'percentage' => number_format($hangup['COMPLETEAGENT']*100/$hangup['calls']).' %'
        ];
        $res[] = [
            'event' => _('Cliente desligou'),
            'calls' => $hangup['COMPLETECALLER'],
            'percentage' => number_format($hangup['COMPLETECALLER']*100/$hangup['calls']).' %'
        ];

        return $res;
    }

    private function getAbandonedStats($post)
    {
        $start = $post['startDate'].' '.$post['startTime'];
        $end = $post['endDate'].' '.$post['endTime'];
        $queuename = $post['queuename'];
        if (!$queuename) return null;
        $sql = "SELECT event, COUNT(event) AS calls, SUM(data2)/COUNT(event) AS origposition, SUM(data1)/COUNT(event) AS position, ";
        $sql .= "SUM(data3)/COUNT(event) AS waittime, MAX(CAST(data3 AS INT)) AS maxwaittime FROM asteriskcdrdb.queuelog WHERE ";
        $sql .= "time >= :start AND time <= :end AND queuename IN (:queuename) AND ";
        $sql .= "event IN ('ABANDON', 'EXITWITHTIMEOUT', 'EXITEMPTY') GROUP BY event";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':start',$start,PDO::PARAM_STR);
        $stmt->bindParam(':end',$end,PDO::PARAM_STR);
        $stmt->bindParam(':queuename',$queuename,PDO::PARAM_STR);
        $stmt->execute();
        $calls = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $calls = is_array($calls) ? $calls : null;

        foreach ($calls as $key=>$value) {
            if ($calls[$key]['event'] == "ABANDON") $calls[$key]['event'] = _('Abandonado');
            if ($calls[$key]['event'] == "EXITWITHTIMEOUT") $calls[$key]['event'] = _('Sem resposta');
            if ($calls[$key]['event'] == "EXITEMPTY") $calls[$key]['event'] = _('Sem agente');
            $calls[$key]['position'] = round($value['position']);
            $calls[$key]['origposition'] = round($value['origposition'],1);
            $calls[$key]['waittime'] = round($value['waittime'],1);
        }

        return $calls;
    }

    private function getCallsDetail($post)
    {
        $start = $post['startDate'].' '.$post['startTime'];
        $end = $post['endDate'].' '.$post['endTime'];
        $queuename = $post['queuename'];
        if (!$queuename) return null;
        $sql = "SELECT time,callid,queuename,agent,event,data1,data2,data3 FROM asteriskcdrdb.queuelog WHERE  ";
        $sql .= "event IN('COMPLETECALLER','COMPLETEAGENT','CONNECT','ABANDON','ENTERQUEUE','EXITEMPTY','EXITWITHTIMEOUT') ";
        $sql .= "AND time >= :start AND time <= :end AND queuename IN (:queuename) ";
        $sql .= "ORDER BY callid, time ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':start',$start,PDO::PARAM_STR);
        $stmt->bindParam(':end',$end,PDO::PARAM_STR);
        $stmt->bindParam(':queuename',$queuename,PDO::PARAM_STR);
        $stmt->execute();
        $calls = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $calls = is_array($calls) ? $calls : null;

        $i = 0;
        foreach ($calls as $key=>$value) {
            $event = $this->event($value['event']);
            if ($i>0 && $calls[$key]['callid']==$calls[$key-1]['callid']) {
                $res[$i-1]['agent'] = ($value['agent']!='NONE')?$value['agent']:'';
                $res[$i-1]['queuename'] = $value['queuename'];
                $res[$i-1]['event'] = $event['event'];
                $res[$i-1][$event['data1']] = $value['data1'];
                $res[$i-1][$event['data2']] = $value['data2'];
                $res[$i-1][$event['data3']] = $value['data3'];
            } else {
                $res[$i] = [
                    'time' => $value['time'],
                    'agent' => ($value['agent']!='NONE')?$value['agent']:'',
                    'queuename' => $value['queuename'],
                    'event' => $event['event'],
                    $event['data1'] => $value['data1'],
                    $event['data2'] => $value['data2'],
                    $event['data3'] => $value['data3'],
                ];
                $i++;
            }
        }

        return $res;
    }

    private function event($value)
    {
        $event = [
            'ABANDON' => [
                'data1' => 'position',
                'data2' => 'origposition',
                'data3' => 'waittime',
                'event' => _('Abandonou'),
                ],
            'COMPLETEAGENT' => [
                'data1' => 'waittime',
                'data2' => 'calltime',
                'data3' => 'origposition',
                'event' => _('Agente desligou'),
            ],
            'COMPLETECALLER' => [
                'data1' => 'waittime',
                'data2' => 'calltime',
                'data3' => 'origposition',
                'event' => _('Cliente desligou'),
            ],
            'CONNECT' => [
                'data1' => 'waittime',
                'data2' => 'bridgedchanneluniqueid',
                'data3' => 'ringtime',
                'event' => _('Agente atendeu'),
            ],
            'ENTERQUEUE' => [
                'data1' => 'null',
                'data2' => 'callerid',
                'data3' => 'position',
                'event' => _('Entrou na fila'),
            ],
            'EXITEMPTY' => [
                'data1' => 'position',
                'data2' => 'origposition',
                'data3' => 'waittime',
                'event' => _('Fila sem agentes'),
            ],
            'EXITWITHTIMEOUT' => [
                'data1' => 'position',
                'data2' => 'origposition',
                'data3' => 'waittime',
                'event' => _('Timeout'),
            ],
        ];
        $event = array_key_exists($value, $event) ? $event[$value] : null;

        return $event;
    }

    private function getCallsHour($post)
    {
        $start = $post['startDate'].' '.$post['startTime'];
        $end = $post['endDate'].' '.$post['endTime'];
        $queuename = $post['queuename'];
        //if (!$queuename) return null;

        $sql = "SELECT HOUR(time) AS hour, COUNT(event) AS calls FROM asteriskcdrdb.queuelog WHERE  ";
        $sql .= "event IN('CONNECT') ";
        $sql .= "AND time >= :start AND time <= :end AND queuename IN (:queuename) ";
        $sql .= "GROUP BY hour";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':start',$start,PDO::PARAM_STR);
        $stmt->bindParam(':end',$end,PDO::PARAM_STR);
        $stmt->bindParam(':queuename',$queuename,PDO::PARAM_STR);
        $stmt->execute();
        $answered = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $answered = is_array($answered) ? $answered : null;

        $sql = "SELECT HOUR(time) AS hour, COUNT(event) AS calls FROM asteriskcdrdb.queuelog WHERE  ";
        $sql .= "event IN('ABANDON', 'EXITWITHTIMEOUT', 'EXITEMPTY') ";
        $sql .= "AND time >= :start AND time <= :end AND queuename IN (:queuename) ";
        $sql .= "GROUP BY hour";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':start',$start,PDO::PARAM_STR);
        $stmt->bindParam(':end',$end,PDO::PARAM_STR);
        $stmt->bindParam(':queuename',$queuename,PDO::PARAM_STR);
        $stmt->execute();
        $unanswered = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $unanswered = is_array($unanswered) ? $unanswered : null;

        $data = [];
        for ($i=0;$i<24;$i++) {
            $data[] = ['hour' => $i, 'answered' => 0, 'unanswered' => 0];
        }

        foreach ($answered as $a) {
            $data[$a['hour']]['answered'] = $a['calls'];
        }

        foreach ($unanswered as $u) {
            $data[$u['hour']]['unanswered'] = $u['calls'];
        }

        return $data;
    }

    private function getQueues()
    {
        $sql = "SELECT extension, descr FROM queues_config ORDER BY extension ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $data;
    }
}