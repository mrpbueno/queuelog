<?php


namespace FreePBX\modules\Queuelog\Traits;

use PDO;
use PDOException;

/**
 * Trait QueueLogTrait
 *
 * This trait provides methods for retrieving various statistics from the asteriskcdrdb.queuelog table.
 * It includes functionalities to get agent statistics, queue statistics, SLA statistics,
 * hangup statistics, abandoned call statistics, call details, and calls per hour.
 *
 * @package FreePBX\modules\Queuelog\Traits
 */
trait QueueLogTrait
{    
    /**
     * Retrieves agent statistics from the queuelog.
     *
     * @param array $post An associative array containing:
     *                    - 'startDate': The start date for the query (e.g., 'YYYY-MM-DD').
     *                    - 'startTime': The start time for the query (e.g., 'HH:MM:SS').
     *                    - 'endDate': The end date for the query (e.g., 'YYYY-MM-DD').
     *                    - 'endTime': The end time for the query (e.g., 'HH:MM:SS').
     *                    - 'queuename': The name of the queue.
     * @return array An array of agent statistics, or array null if no queuename is provided or no results are found.
     *                    Each element in the array contains:
     *                    - 'queuename': The name of the queue.
     *                    - 'agent': The agent identifier.
     *                    - 'calls': Total calls handled by the agent.
     *                    - 'waittime': Total wait time for calls handled by the agent.
     *                    - 'calltime': Total call time for calls handled by the agent.
     *                    - 'avgwaittime': Average wait time per call for the agent.
     *                    - 'avgcalltime': Average call time per call for the agent.
     *                    - 'maxwaittime': Maximum wait time for a single call for the agent.
     *                    - 'maxcalltime': Maximum call time for a single call for the agent. 
     */
    private function getAgentStats($post)
    {
        $startDate = isset($post['startDate']) ? $post['startDate'] : date('Y-m-d');
        $startTime = isset($post['startTime']) ? $post['startTime'] : '00:00:00';
        $endDate   = isset($post['endDate']) ? $post['endDate'] : date('Y-m-d');
        $endTime   = isset($post['endTime']) ? $post['endTime'] : '23:59:59';
        $start = "$startDate $startTime";
        $end   = "$endDate $endTime";
        $queuename = isset($post['queuename']) ? trim($post['queuename']) : '';
    
        if (empty($queuename)) {
            return [];
        }
    
        $sql = "SELECT queuename, agent, COUNT(agent) AS calls, SUM(data1) AS waittime, SUM(data2) AS calltime, 
                FLOOR(SUM(data1)/COUNT(agent)) AS avgwaittime, FLOOR(SUM(data2)/COUNT(agent)) AS avgcalltime, 
                MAX(CAST(data1 AS INT)) AS maxwaittime, MAX(CAST(data2 AS INT)) AS maxcalltime 
                FROM asteriskcdrdb.queuelog 
                WHERE time >= :start AND time <= :end AND queuename = :queuename AND 
                event IN ('COMPLETECALLER', 'COMPLETEAGENT') 
                GROUP BY agent ORDER BY agent";
    
        try {
            $stmt = $this->db->prepare($sql);      
            $stmt->bindValue(':start', $start, PDO::PARAM_STR);
            $stmt->bindValue(':end', $end, PDO::PARAM_STR);
            $stmt->bindValue(':queuename', $queuename, PDO::PARAM_STR);

            $stmt->execute();
        
            $calls = $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
        
            error_log("QueueLog => Erro ao buscar estatísticas de agentes: " . $e->getMessage());
            return [];
        }

        return $calls;
    }

    /**
     * Retrieves queue statistics from the queuelog.
     *
     * @param array $post An associative array containing:
     *                    - 'startDate': The start date for the query (e.g., 'YYYY-MM-DD').
     *                    - 'startTime': The start time for the query (e.g., 'HH:MM:SS').
     *                    - 'endDate': The end date for the query (e.g., 'YYYY-MM-DD').
     *                    - 'endTime': The end time for the query (e.g., 'HH:MM:SS').
     *                    - 'queuename': The name of the queue.
     * @return array An array of queue statistics, or array null if no queuename is provided or no results are found.
     *                    Each element in the array contains:
     *                    - 'queuename': The name of the queue.
     *                    - 'calls': Total calls for the queue.
     *                    - 'waittime': Total wait time for calls in the queue.
     *                    - 'calltime': Total call time for calls in the queue.
     *                    - 'avgwaittime': Average wait time per call for the queue.
     *                    - 'avgcalltime': Average call time per call for the queue.
     *                    - 'maxwaittime': Maximum wait time for a single call in the queue.
     *                    - 'maxcalltime': Maximum call time for a single call in the queue.     
     */
    private function getQueueStats($post)
    {
        $startDate = isset($post['startDate']) ? $post['startDate'] : date('Y-m-d');
        $startTime = isset($post['startTime']) ? $post['startTime'] : '00:00:00';
        $endDate   = isset($post['endDate']) ? $post['endDate'] : date('Y-m-d');
        $endTime   = isset($post['endTime']) ? $post['endTime'] : '23:59:59';
        $start = "$startDate $startTime";
        $end   = "$endDate $endTime";
        $queuename = isset($post['queuename']) ? trim($post['queuename']) : '';
    
        if (empty($queuename)) {
            return [];
        }
        $sql = "SELECT 
                queuename, 
                COUNT(queuename) AS calls, 
                SUM(data1) AS waittime, 
                SUM(data2) AS calltime,
                FLOOR(SUM(data1) / NULLIF(COUNT(agent), 0)) AS avgwaittime, 
                FLOOR(SUM(data2) / NULLIF(COUNT(agent), 0)) AS avgcalltime,
                MAX(CAST(data1 AS INT)) AS maxwaittime, 
                MAX(CAST(data2 AS INT)) AS maxcalltime
            FROM asteriskcdrdb.queuelog 
            WHERE 
                time >= :start AND 
                time <= :end AND 
                queuename = :queuename AND
                event IN ('COMPLETECALLER', 'COMPLETEAGENT') 
            GROUP BY queuename";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':start',$start,PDO::PARAM_STR);
            $stmt->bindValue(':end',$end,PDO::PARAM_STR);
            $stmt->bindValue(':queuename',$queuename,PDO::PARAM_STR);
            $stmt->execute();
            $calls = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $calls = is_array($calls) ? $calls : [];
        } catch (PDOException $e) {
            error_log("QueueLog => Erro ao buscar estatísticas de filas: " . $e->getMessage());
            return [];
        }
        
        return $calls;
    }

    /**
     * Retrieves Service Level Agreement (SLA) statistics based on call wait times.
     *
     * @param array $post An associative array containing:
     *                    - 'startDate': The start date for the query (e.g., 'YYYY-MM-DD').
     *                    - 'startTime': The start time for the query (e.g., 'HH:MM:SS').
     *                    - 'endDate': The end date for the query (e.g., 'YYYY-MM-DD').
     *                    - 'endTime': The end time for the query (e.g., 'HH:MM:SS').
     *                    - 'queuename': The name of the queue.
     * @return array An array of SLA statistics, or array null if no queuename is provided or no calls are found.
     *                    Each element in the array contains:
     *                    - 'sla': The SLA threshold in seconds.
     *                    - 'calls': The number of calls that met or exceeded this SLA threshold.
     *                    - 'delta': The difference in calls from the previous SLA threshold.
     *                    - 'percentage': The percentage of calls that met or exceeded this SLA threshold. 
     */
    private function getSlaStats($post)
    {
        $startDate = isset($post['startDate']) ? $post['startDate'] : date('Y-m-d');
        $startTime = isset($post['startTime']) ? $post['startTime'] : '00:00:00';
        $endDate   = isset($post['endDate']) ? $post['endDate'] : date('Y-m-d');
        $endTime   = isset($post['endTime']) ? $post['endTime'] : '23:59:59';
        $start = "$startDate $startTime";
        $end   = "$endDate $endTime";
        $queuename = isset($post['queuename']) ? trim($post['queuename']) : '';
    
        if (empty($queuename)) {
            return [];
        }
        $sql = "SELECT data1 FROM asteriskcdrdb.queuelog WHERE event IN ('CONNECT') AND ";
        $sql .= "time >= :start AND time <= :end AND queuename IN (:queuename)";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':start',$start,PDO::PARAM_STR);
            $stmt->bindValue(':end',$end,PDO::PARAM_STR);
            $stmt->bindValue(':queuename',$queuename,PDO::PARAM_STR);
            $stmt->execute();
            $calls = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $total = $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("QueueLog => Erro ao buscar estatísticas de SLA: " . $e->getMessage());
            return [];
        }
        if ($total == 0) {
            return [];
        }

        $sla_buckets = array_fill_keys(range(15, 300, 15), 0);
    
        foreach ($calls as $waitTime) {        
            $bucket = ceil((int)$waitTime / 15) * 15;        
            if ($bucket > 300) {
                $bucket = 300;
            }        
            if ($bucket > 0 && isset($sla_buckets[$bucket])) {
                $sla_buckets[$bucket]++;
            }
        }
    
        $res = [];
        $cumulative_calls = 0;
        $previous_cumulative = 0;
        foreach ($sla_buckets as $sla_level => $count_in_bucket) {
            $cumulative_calls += $count_in_bucket;
        
            $res[] = [
                'sla'        => $sla_level,
                'calls'      => $cumulative_calls,
                'delta'      => $cumulative_calls - $previous_cumulative,
                'percentage' => number_format(($total > 0 ? ($cumulative_calls * 100 / $total) : 0), 2) . ' %',
            ];
        
            $previous_cumulative = $cumulative_calls;
        }

        $res = is_array($res) ? $res : null;
        
        return $res;
    }

    /**
     * Retrieves hangup statistics, categorizing calls by who hung up (agent or caller).
     *
     * @param array $post An associative array containing:
     *                    - 'startDate': The start date for the query (e.g., 'YYYY-MM-DD').
     *                    - 'startTime': The start time for the query (e.g., 'HH:MM:SS').
     *                    - 'endDate': The end date for the query (e.g., 'YYYY-MM-DD').
     *                    - 'endTime': The end time for the query (e.g., 'HH:MM:SS').
     *                    - 'queuename': The name of the queue.
     * @return array An array of hangup statistics, or array null if no queuename is provided or no results are found.
     *                    Each element in the array contains:
     *                    - 'event': Description of the hangup event (e.g., 'Agente desligou', 'Cliente desligou').
     *                    - 'calls': Number of calls for that hangup event.
     *                    - 'percentage': Percentage of calls for that hangup event relative to total hangup calls.
     */
    private function getHangupStats($post)
    {
        $startDate = isset($post['startDate']) ? $post['startDate'] : date('Y-m-d');
        $startTime = isset($post['startTime']) ? $post['startTime'] : '00:00:00';
        $endDate   = isset($post['endDate']) ? $post['endDate'] : date('Y-m-d');
        $endTime   = isset($post['endTime']) ? $post['endTime'] : '23:59:59';
        $start = "$startDate $startTime";
        $end   = "$endDate $endTime";
        $queuename = isset($post['queuename']) ? trim($post['queuename']) : '';
    
        if (empty($queuename)) {
            return [];
        }
        $sql = "SELECT event, COUNT(event) AS calls ";
        $sql .= "FROM asteriskcdrdb.queuelog WHERE event IN ('COMPLETECALLER', 'COMPLETEAGENT') AND ";
        $sql .= "time >= :start AND time <= :end AND queuename IN (:queuename) GROUP BY event";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':start',$start,PDO::PARAM_STR);
            $stmt->bindValue(':end',$end,PDO::PARAM_STR);
            $stmt->bindValue(':queuename',$queuename,PDO::PARAM_STR);
            $stmt->execute();
            $calls = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $total = $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("QueueLog => Erro ao buscar estatísticas de hangup: " . $e->getMessage());
            return [];
        }
        if ($total==0) return [];
        $hangup_stats = [
        'COMPLETECALLER' => 0,
        'COMPLETEAGENT'  => 0,
        'total_calls'    => 0
        ];

        foreach ($calls as $call) {
            if (isset($hangup_stats[$call['event']])) {
                $hangup_stats[$call['event']] = (int)$call['calls'];
                $hangup_stats['total_calls'] += (int)$call['calls'];
            }
        }    
    
        $res = [];
        $total_calls = $hangup_stats['total_calls'];

        $res[] = [
            'event'      => _('Agente desligou'),
            'calls'      => $hangup_stats['COMPLETEAGENT'],
            'percentage' => ($total_calls > 0) 
                ? number_format($hangup_stats['COMPLETEAGENT'] * 100 / $total_calls, 2) . ' %'
                : '0.00 %'
        ];
        $res[] = [
            'event'      => _('Cliente desligou'),
            'calls'      => $hangup_stats['COMPLETECALLER'],
            'percentage' => ($total_calls > 0) 
                ? number_format($hangup_stats['COMPLETECALLER'] * 100 / $total_calls, 2) . ' %'
                : '0.00 %'
        ];

        $res = is_array($res) ? $res : null;
        
        return $res;
    }

    /**
     * Retrieves statistics for abandoned calls, including original position, final position, and wait time.
     *
     * @param array $post An associative array containing:
     *                    - 'startDate': The start date for the query (e.g., 'YYYY-MM-DD').
     *                    - 'startTime': The start time for the query (e.g., 'HH:MM:SS').
     *                    - 'endDate': The end date for the query (e.g., 'YYYY-MM-DD').
     *                    - 'endTime': The end time for the query (e.g., 'HH:MM:SS').
     *                    - 'queuename': The name of the queue.
     * @return array An array of abandoned call statistics, or array null if no queuename is provided or no results are found.
     *                    Each element in the array contains:
     *                    - 'event': Description of the abandonment event (e.g., 'Abandonado', 'Sem resposta', 'Sem agente').
     *                    - 'calls': Number of calls for that abandonment event.
     *                    - 'origposition': Average original position in the queue.
     *                    - 'position': Average position in the queue at the time of abandonment.
     *                    - 'waittime': Average wait time before abandonment.
     *                    - 'maxwaittime': Maximum wait time before abandonment.
     */
    private function getAbandonedStats($post)
    {
        $startDate = isset($post['startDate']) ? $post['startDate'] : date('Y-m-d');
        $startTime = isset($post['startTime']) ? $post['startTime'] : '00:00:00';
        $endDate   = isset($post['endDate']) ? $post['endDate'] : date('Y-m-d');
        $endTime   = isset($post['endTime']) ? $post['endTime'] : '23:59:59';
        $start = "$startDate $startTime";
        $end   = "$endDate $endTime";
        $queuename = isset($post['queuename']) ? trim($post['queuename']) : ''; 
    
        if (empty($queuename)) {
            return [];
        }
        $sql = "SELECT event, COUNT(event) AS calls, SUM(data2)/COUNT(event) AS origposition, SUM(data1)/COUNT(event) AS position, ";
        $sql .= "SUM(data3)/COUNT(event) AS waittime, MAX(CAST(data3 AS INT)) AS maxwaittime FROM asteriskcdrdb.queuelog WHERE ";
        $sql .= "time >= :start AND time <= :end AND queuename IN (:queuename) AND ";
        $sql .= "event IN ('ABANDON', 'EXITWITHTIMEOUT', 'EXITEMPTY') GROUP BY event";
        try {
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':start',$start,PDO::PARAM_STR);
        $stmt->bindValue(':end',$end,PDO::PARAM_STR);
        $stmt->bindValue(':queuename',$queuename,PDO::PARAM_STR);
        $stmt->execute();
        $calls = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("QueueLog => Erro ao buscar estatísticas de abandonos: " . $e->getMessage());
            return [];
        }    
        $calls = is_array($calls) ? $calls : null;

        $eventTranslations = [
        'ABANDON'         => _('Abandonado'),
        'EXITWITHTIMEOUT' => _('Sem resposta'),
        'EXITEMPTY'       => _('Sem agente')
        ];

        foreach ($calls as $key => $value) {        
            $calls[$key]['event'] = isset($eventTranslations[$value['event']]) ? $eventTranslations[$value['event']] : $value['event'];        
            $calls[$key]['position']     = round($value['position']);
            $calls[$key]['origposition'] = round($value['origposition'], 1);
            $calls[$key]['waittime']     = round($value['waittime'], 1);
        }

        return $calls;
    }

    /**
     * Retrieves detailed call records from the queuelog.
     *
     * @param array $post An associative array containing:
     *                    - 'startDate': The start date for the query (e.g., 'YYYY-MM-DD').
     *                    - 'startTime': The start time for the query (e.g., 'HH:MM:SS').
     *                    - 'endDate': The end date for the query (e.g., 'YYYY-MM-DD').
     *                    - 'endTime': The end time for the query (e.g., 'HH:MM:SS').
     *                    - 'queuename': The name of the queue. queuename is a string forever
     * @return array An array of detailed call records, or array null if no queuename is provided or no results are found.
     *                    Each element in the array contains:
     *                    - 'time': Timestamp of the event.
     *                    - 'agent': Agent identifier (empty string if 'NONE').
     *                    - 'queuename': The name of the queue.
     *                    - 'event': Translated event description (e.g., 'Agente atendeu', 'Abandonou').
     *                    - 'data1', 'data2', 'data3': Event-specific data, mapped to descriptive keys.
     */
    private function getCallsDetail($post)
    {
        $startDate = isset($post['startDate']) ? $post['startDate'] : date('Y-m-d');
        $startTime = isset($post['startTime']) ? $post['startTime'] : '00:00:00';
        $endDate   = isset($post['endDate']) ? $post['endDate'] : date('Y-m-d');
        $endTime   = isset($post['endTime']) ? $post['endTime'] : '23:59:59';
        $start = "$startDate $startTime";
        $end   = "$endDate $endTime";
        $queuename = isset($post['queuename']) ? trim($post['queuename']) : ''; 
    
        if (empty($queuename)) {
            return [];
        }
        $sql = "SELECT time,callid,queuename,agent,event,data1,data2,data3 FROM asteriskcdrdb.queuelog WHERE  ";
        $sql .= "event IN('COMPLETECALLER','COMPLETEAGENT','CONNECT','ABANDON','ENTERQUEUE','EXITEMPTY','EXITWITHTIMEOUT') ";
        $sql .= "AND time >= :start AND time <= :end AND queuename IN (:queuename) ";
        $sql .= "ORDER BY callid, time ASC";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':start',$start,PDO::PARAM_STR);
            $stmt->bindValue(':end',$end,PDO::PARAM_STR);
            $stmt->bindValue(':queuename',$queuename,PDO::PARAM_STR);
            $stmt->execute();            
        } catch (PDOException $e) {
            error_log("QueueLog => Erro ao buscar detalhes de chamadas: " . $e->getMessage());
            return [];
        }
        
        $res = is_array($res) ? $res : null;

        $results = [];
        $currentCall = [];
        $lastCallId = null;
    
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $eventMap = $this->event($row['event']);
            if ($eventMap === null) {
                continue;
            }
        
            if ($row['callid'] !== $lastCallId) {            
                if ($lastCallId !== null) {
                    $results[] = $currentCall;
                }
                        
                $currentCall = [
                    'time'      => $row['time'],
                    'agent'     => ($row['agent'] !== 'NONE') ? $row['agent'] : '',
                    'queuename' => $row['queuename'],
                    'event'     => $eventMap['event']
                ];
                $lastCallId = $row['callid'];
            }
                
            $currentCall[$eventMap['data1']] = $row['data1'];
            $currentCall[$eventMap['data2']] = $row['data2'];
            $currentCall[$eventMap['data3']] = $row['data3'];              
            $currentCall['event'] = $eventMap['event'];
            if ($row['agent'] !== 'NONE') {
                $currentCall['agent'] = $row['agent'];
            }
        }
    
        if ($lastCallId !== null) {
            $results[] = $currentCall;
        }

        return $results;
    }

    /**
     * Maps a raw event string from the queuelog to a more descriptive array,
     * including translated event names and data field mappings.
     *
     * @param string $value The raw event string (e.g., 'ABANDON', 'COMPLETEAGENT').
     * @return array|null An associative array containing:
     *                    - 'data1': Key for the first data field.
     *                    - 'data2': Key for the second data field.
     *                    - 'data3': Key for the third data field.
     *                    - 'event': Translated event name.
     *                    Returns null if the event is not recognized.
     */
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

    /**
     * Retrieves call statistics grouped by hour, differentiating between answered and unanswered calls.
     *
     * @param array $post An associative array containing:
     *                    - 'startDate': The start date for the query (e.g., 'YYYY-MM-DD').
     *                    - 'startTime': The start time for the query (e.g., 'HH:MM:SS').
     *                    - 'endDate': The end date for the query (e.g., 'YYYY-MM-DD').
     *                    - 'endTime': The end time for the query (e.g., 'HH:MM:SS').
     *                    - 'queuename': The name of the queue. queuename is a string forever.
     * @return array An array of call statistics per hour.
     *               Each element in the array contains:
     *               - 'hour': The hour of the day (0-23).
     *               - 'answered': Number of answered calls during that hour.
     *               - 'unanswered': Number of unanswered calls during that hour.
     */
    private function getCallsHour($post)
    {
        $startDate = isset($post['startDate']) ? $post['startDate'] : date('Y-m-d');
        $startTime = isset($post['startTime']) ? $post['startTime'] : '00:00:00';
        $endDate   = isset($post['endDate']) ? $post['endDate'] : date('Y-m-d');
        $endTime   = isset($post['endTime']) ? $post['endTime'] : '23:59:59';
        $start = "$startDate $startTime";
        $end   = "$endDate $endTime";
        $queuename = isset($post['queuename']) ? trim($post['queuename']) : '';
    
        if (empty($queuename)) {
            return [];
        }

        $sql = "SELECT
                HOUR(time) AS hour,
                COUNT(CASE WHEN event = 'CONNECT' THEN 1 END) AS answered,
                COUNT(CASE WHEN event IN ('ABANDON', 'EXITWITHTIMEOUT', 'EXITEMPTY') THEN 1 END) AS unanswered
            FROM 
                asteriskcdrdb.queuelog
            WHERE
                time >= :start 
                AND time <= :end 
                AND queuename = :queuename
                AND event IN ('CONNECT', 'ABANDON', 'EXITWITHTIMEOUT', 'EXITEMPTY')
            GROUP BY 
                hour
            ORDER BY 
                hour ASC";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':start', $start, PDO::PARAM_STR);
            $stmt->bindValue(':end', $end, PDO::PARAM_STR);
            $stmt->bindValue(':queuename', $queuename, PDO::PARAM_STR);
            $stmt->execute();        
            $db_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("QueueLog => Erro ao buscar estatísticas de chamadas por hora: " . $e->getMessage());
            return [];
        }
    
        $data_by_hour = [];
        for ($i = 0; $i < 24; $i++) {
            $data_by_hour[$i] = ['hour' => $i, 'answered' => 0, 'unanswered' => 0];
        }    
    
        foreach ($db_results as $row) {
            $hour = (int)$row['hour'];
            if (isset($data_by_hour[$hour])) {
                $data_by_hour[$hour]['answered'] = (int)$row['answered'];
                $data_by_hour[$hour]['unanswered'] = (int)$row['unanswered'];
            }
        }
        
        return array_values($data_by_hour);
    }

    /**
     * Retrieves a list of configured queues from the queues_config table.
     *
     * @return array An array of queue configurations, each containing 'extension' and 'descr'.
     */
    private function getQueues()
    {
        $sql = "SELECT extension, descr FROM queues_config ORDER BY extension ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $data;
    }
}
