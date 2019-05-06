<?php
/** @var \ORCA\OrcaCallList\OrcaCallList $this */
/**
 * getQueryLogsSql($sql)
 *      Returns the raw SQL that would run if the supplied parameter was passed into queryLogs().
 *
 * log($message[, $parameters])
 *      Inserts a log entry and returns the inserted log_id.
 *
 * queryLogs($sql)
 *      Queries log entries added via the log() method using SQL-like syntax
 *          log_id, timestamp, user, ip, project_id, record, message[, parameters]
 *
 * removeLogs($sql)
 *      Removes log entries matching the current module, current project (if detected), and the specified sql "where" clause.
 *
 */
namespace ORCA\OrcaCallList;

trait ModuleLogUtils {

    /**
     * the default fields in the root log table
     * @var array
     */
    public $default_log_fields = [
        "log_id",
        "timestamp",
        "username",
        "ip",
        "project_id",
        "record",
        "message"
    ];

    /**
     * Numeric prefixes are not supported, so they must be forced to be alphanumeric
     * @var string
     */
    private $parameter_prefix = "log_param_";

    /**
     * @param $message
     * @param array $parameters
     * @return int|bool
     * @throws \Exception
     */
    public function saveLogs($message, $parameters = []) {
        $log_id = false;
        try {
            $parameters_prefixed = [];
            foreach ($parameters as $key => $value) {
                $parameters_prefixed[$this->parameter_prefix . $key] = $value;
            }
            $log_id = $this->log($message, $parameters_prefixed);
        } catch (\Exception $ex) {
            throw $ex;
        }
        return $log_id;
    }

    /**
     * Wrapper for queryLogs to make the process a little easier.
     * No parameters or where statement brings back all data for this module/project.
     * The resulting array will contain all parameters in their own "parameters" array.
     * @param array $parameters
     * @param null $where i.e. "username = 'myusername'"
     * @return array
     */
    public function getLogs($parameters = [], $where = null) {
        $result = [];

        $parameters_prefixed = [];
		foreach ($parameters as $p) {
            array_push($parameters_prefixed, $this->parameter_prefix . $p);
        }
        
		$fields = implode(', ', array_merge($this->default_log_fields, $parameters_prefixed));
        $sql = "select $fields";
        if (!empty($where)) {
            $sql .= " WHERE " . $where;
        }
        $sql .= " order by timestamp desc";
        $sql_result = $this->queryLogs($sql);
        while($row = $sql_result->fetch_assoc()){
            $result_row = [];
            foreach ($row as $key => $value) {
                if (in_array($key, $this->default_log_fields)) {
                    $result_row[$key] = $value;
                } else {
                    $key = preg_replace("/^" . $this->parameter_prefix . "/", '', $key);
                    $result_row["parameters"][$key] = $value;
                }
            }
            $result[$row["log_id"]] = $result_row;
        }
        $sql_result->free_result();
        return $result;
    }
}