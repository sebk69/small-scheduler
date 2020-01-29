<?php

namespace App\SmallSchedulerModelBundle\Model;

use Sebk\SmallOrmBundle\Dao\Model;

/**
 * @method getId()
 * @method setId($value)
 * @method getGroupId()
 * @method setGroupId($value)
 * @method getScheduledMinute()
 * @method setScheduledMinute($value)
 * @method getScheduledHour()
 * @method setScheduledHour($value)
 * @method getScheduledDay()
 * @method setScheduledDay($value)
 * @method getScheduledMonth()
 * @method setScheduledMonth($value)
 * @method getScheduledWeekday()
 * @method setScheduledWeekday($value)
 * @method getCommand()
 * @method setCommand($value)
 * @method getQueue()
 * @method setQueue($value)
 * @method getTrash()
 * @method setTrash($value)
 * @method getSentTrace()
 * @method setSentTrace($value)
 * @method getEnabled()
 * @method setEnabled($value)
 * @method \App\SmallSchedulerModelBundle\Model\Group getTaskGroup()
 * @method \App\SmallSchedulerModelBundle\Model\TaskChangeLog[] getTasksChangesLogs()
 * @method \App\SmallSchedulerModelBundle\Model\TaskExecutionLog[] getTasksExecutionsLogs()
 */
class Task extends Model
{
    public function beforeSave()
    {
        $this->normalizeCommand();
    }

    public function normalizeCommand()
    {
        // Remove special chars
        $this->setCommand(str_replace(["\n", "\r", "\t"], "", $this->getCommand()));
    }

    /**
     * Build single string as it must me in crontab
     * @return string
     */
    public function getCronString()
    {
        $fields = [];
        $fields[] = $this->getScheduledMinute();
        $fields[] = $this->getScheduledHour();
        $fields[] = $this->getScheduledDay();
        $fields[] = $this->getScheduledMonth();
        $fields[] = $this->getScheduledWeekday();

        return implode(" ", $fields);
    }

    /**
     * Check cron string syntax
     * @return bool
     */
    public function checkCronSyntax()
    {
        return 1 === preg_match('/(((([*])|(((([0-5])?[0-9])((-(([0-5])?[0-9])))?)))((\/(([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?[0-9])))?))(,(((([*])|(((([0-5])?[0-9])((-(([0-5])?[0-9])))?)))((\/(([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?[0-9])))?)))* (((([*])|(((((([0-1])?[0-9]))|(([2][0-3])))((-(((([0-1])?[0-9]))|(([2][0-3])))))?)))((\/(([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?[0-9])))?))(,(((([*])|(((((([0-1])?[0-9]))|(([2][0-3])))((-(((([0-1])?[0-9]))|(([2][0-3])))))?)))((\/(([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?[0-9])))?)))* (((((((([*])|(((((([1-2])?[0-9]))|(([3][0-1]))|(([1-9])))((-(((([1-2])?[0-9]))|(([3][0-1]))|(([1-9])))))?)))((\/(([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?[0-9])))?))|(L)|(((((([1-2])?[0-9]))|(([3][0-1]))|(([1-9])))W))))(,(((((([*])|(((((([1-2])?[0-9]))|(([3][0-1]))|(([1-9])))((-(((([1-2])?[0-9]))|(([3][0-1]))|(([1-9])))))?)))((\/(([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?[0-9])))?))|(L)|(((((([1-2])?[0-9]))|(([3][0-1]))|(([1-9])))W)))))*)|([?])) (((([*])|((((([1-9]))|(([1][0-2])))((-((([1-9]))|(([1][0-2])))))?))|((((JAN)|(FEB)|(MAR)|(APR)|(MAY)|(JUN)|(JUL)|(AUG)|(SEP)|(OKT)|(NOV)|(DEC))((-((JAN)|(FEB)|(MAR)|(APR)|(MAY)|(JUN)|(JUL)|(AUG)|(SEP)|(OKT)|(NOV)|(DEC))))?)))((\/(([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?[0-9])))?))(,(((([*])|((((([1-9]))|(([1][0-2])))((-((([1-9]))|(([1][0-2])))))?))|((((JAN)|(FEB)|(MAR)|(APR)|(MAY)|(JUN)|(JUL)|(AUG)|(SEP)|(OKT)|(NOV)|(DEC))((-((JAN)|(FEB)|(MAR)|(APR)|(MAY)|(JUN)|(JUL)|(AUG)|(SEP)|(OKT)|(NOV)|(DEC))))?)))((\/(([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?[0-9])))?)))* (((((((([*])|((([0-6])((-([0-6])))?))|((((SUN)|(MON)|(TUE)|(WED)|(THU)|(FRI)|(SAT))((-((SUN)|(MON)|(TUE)|(WED)|(THU)|(FRI)|(SAT))))?)))((\/(([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?[0-9])))?))|((([0-6])L))|(W)|(([#][1-5]))))(,(((((([*])|((([0-6])((-([0-6])))?))|((((SUN)|(MON)|(TUE)|(WED)|(THU)|(FRI)|(SAT))((-((SUN)|(MON)|(TUE)|(WED)|(THU)|(FRI)|(SAT))))?)))((\/(([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?[0-9])))?))|((([0-6])L))|(W)|(([#][1-5])))))*)|([?]))((( (((([*])|((([1-2][0-9][0-9][0-9])((-([1-2][0-9][0-9][0-9])))?)))((\/(([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?[0-9])))?))(,(((([*])|((([1-2][0-9][0-9][0-9])((-([1-2][0-9][0-9][0-9])))?)))((\/(([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?([0-9])?[0-9])))?)))*))?)/', $this->getCronString());
    }

    /**
     * Is time to launch task
     * @return bool
     */
    public function timeToLaunch($date)
    {
        // Test minutes
        if($this->getScheduledMinute() != "*") {
            $detail = $this->getElementsToLaunch($this->getScheduledMinute());
            if(!in_array(explode(" ", $date)[0], $detail)) {
                return false;
            }
        }

        // Test hours
        if($this->getScheduledHour() != "*") {
            $detail = $this->getElementsToLaunch($this->getScheduledHour());
            if(!in_array(explode(" ", $date)[1], $detail)) {
                return false;
            }
        }

        // Test days
        if($this->getScheduledDay() != "*") {
            $detail = $this->getElementsToLaunch($this->getScheduledDay());
            if(!in_array(explode(" ", $date)[2], $detail)) {
                return false;
            }
        }

        // Test months
        if($this->getScheduledMonth() != "*") {
            $detail = $this->getElementsToLaunch($this->getScheduledMonth());
            if(!in_array(explode(" ", $date)[3], $detail)) {
                return false;
            }
        }

        // Test weekdays
        if($this->getScheduledWeekday() != "*") {
            $detail = $this->getElementsToLaunch($this->getScheduledWeekday());
            if(!in_array(explode(" ", $date)[4], $detail)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Return time elements to launch
     * @param $timeElement
     */
    public function getElementsToLaunch($timeElement) {
        $result = [];

        $enums = explode(",", $timeElement);
        foreach ($enums as $enum) {
            if(strstr($enum, "-")) {
                $start = explode("-", $enum)[0];
                $end = explode("-", $enum)[1];
                for($i = $start; $i <= $end; $i++) {
                    $result[] = $i;
                }
            } else {
                $result[] = $enum;
            }
        }

        return $result;
    }

    /**
     * Save sent trace
     */
    public function saveSentTrace()
    {
        $this->setSentTrace($this->getCurrentTrace());
        $this->persist();
    }

    /**
     * Get current trace
     * @return string
     */
    public function getCurrentTrace()
    {
        return date('i G j n w');
    }
}