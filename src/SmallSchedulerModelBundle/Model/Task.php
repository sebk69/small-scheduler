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
    public function timeToLaunch()
    {
        // Get current minute, hour, day, month, weekday
        $time = explode(' ', date('i G j n w'));
        // Split crontab by space
        $crontab = explode(' ', $this->getCronString());
        // Foreach part of crontab
        foreach ($crontab as $k => &$v) {
            // Remove leading zeros to prevent octal comparison, but not if number is already 1 digit
            $time[$k] = preg_replace('/^0+(?=\d)/', '', $time[$k]);
            // 5,10,15 each treated as seperate parts
            $v = explode(',', $v);
            // Foreach part we now have
            foreach ($v as &$v1) {
                // Do preg_replace with regular expression to create evaluations from crontab
                $v1 = preg_replace(
                    // Regex
                    array(
                        // *
                        '/^\*$/',
                        // 5
                        '/^\d+$/',
                        // 5-10
                        '/^(\d+)\-(\d+)$/',
                        // */5
                        '/^\*\/(\d+)$/'
                    ),
                    // Evaluations
                    // trim leading 0 to prevent octal comparison
                    array(
                        // * is always true
                        'true',
                        // Check if it is currently that time,
                        $time[$k] . '===\0',
                        // Find if more than or equal lowest and lower or equal than highest
                        '(\1<=' . $time[$k] . ' and ' . $time[$k] . '<=\2)',
                        // Use modulus to find if true
                        $time[$k] . '%\1===0'
                    ),
                    // Subject we are working with
                    $v1
                );
            }
            // Join 5,10,15 with `or` conditional
            $v = '(' . implode(' or ', $v) . ')';
        }
        // Require each part is true with `and` conditional
        $crontab = implode(' and ', $crontab);
        // Evaluate total condition to find if true
        return eval('return ' . $crontab . ';');
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