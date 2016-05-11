<?php

/**
 * Created by Vladimir.
 * User: Vladimir
 * Date: 11.05.2016
 */

Class EventDay
{
    /**
     * @var int Максимальный бит (2^6 = 64 - "Понедельник")
     */
    private $maxBit = 64;

    private $wdays = [
        64 => 'Monday',
        32 => 'Tuesday',
        16 => 'Wednesday',
        8 => 'Thursday',
        4 => 'Friday',
        2 => 'Saturday',
        1 => 'Sunday',
    ];

    /**
     * @param $timezone Валидация введенной временной зоны
     * @return bool
     */
    private function validateTimezone($timezone)
    {
        if (is_numeric($timezone)) {
            if (timezone_name_from_abbr("", $timezone * 60, 0))
                return true;
        }

        return false;
    }

    /**
     * @param $days Валидация введенных дней, по которым проходят мероприятия
     * @return bool
     */
    private function validateDays($days)
    {
        if (is_numeric($days))
            if (($days > 0) && ($days < 128))
                return true;

        return false;
    }

    /**
     * @param $time Валидация введенного времени начала мероприятия
     * @return bool
     */
    private function validateTime($time)
    {
        $arr = $this->getTimeOfString($time);
        if ($arr)
            if (is_numeric($arr[0]) && is_numeric($arr[1]))
                if (($arr[0] >= 0 && $arr[0] < 24) && ($arr[1] >= 0 && $arr[1] < 60))
                    return true;

        return false;
    }

    /**
     * @param $number Дни по которым проходит мероприятие
     * @param $bit Текущий день (нормализированный формат)
     * @return int Возврат следующего дня прохождения мероприятия или 0 - если такого нет
     */
    private function nextBit($number, $bit)
    {
        $nextBit = $bit >> 1;
        do {
            if ($number & $nextBit){
                return $nextBit;
            }

            $nextBit = $nextBit >> 1;
        } while($nextBit != 0);

        return 0;
    }

    /**
     * @param $number Дни по которым проходит мероприятие
     * @return int Возврат дня прохождения мероприятия на следующей недели или 0 - если такого нет
     */
    private function prevBit($number)
    {
        $prevBit = $this->maxBit;
        do {
            if ($number & $prevBit){
                return $prevBit;
            }

            $prevBit = $prevBit >> 1;
        } while($prevBit != 0);

        return 0;
    }

    /**
     * @param $number Дни по которым проходит мероприятие
     * @param $bit Текущий день (нормализированный формат)
     * @param $time - Время начала мероприятия
     * @return bool True - сегодня проходит мероприятия, в противном случае - нет
     */
    private function currentBit($number, $bit, $time)
    {
        $today = $this->getToday();

        if ($number & $bit)
            if ($time[0] > $today["hours"]) {
                return true;
            }elseif (($time[0] == $today["hours"]) && ($time[1] >= $today["minutes"])) {
                return true;
            }
        return false;
    }

    /**
     * @param $time Выделение в строке часов и минут
     * @return array Массив: $arr[0] - часы, $arr[1] - минуты
     */
    protected function getTimeOfString($time)
    {
        return explode(':', $time);
    }

    /**
     * @param $timezone Устанавливаем временную зону
     */
    protected function setTimeZone($timezone)
    {
        date_default_timezone_set(timezone_name_from_abbr("", $timezone * 60, 0));
    }

    /**
     * @param $timezone Устанавливаем временную зону
     */
    protected function setTimeZonetoName($timezone)
    {
        date_default_timezone_set($timezone);
    }

    /**
     * @return string Возвращаем текущую временную зону
     */
    protected function getTimeZone()
    {
        return timezone_name_get(date_timezone_get(date_create()));
    }

    /**
     * @return array Возвращаем массив со значениями текущей даты и времени
     */
    protected function getToday()
    {
        return getdate();
    }

    /**
     * @param $time Время начала мероприятия
     * @return int
     */
    protected function bringingTimeEvent($time)
    {
        $date = date_create();
        date_time_set($date, $time[0], $time[1]);

        return date_timestamp_get($date);
    }

    /**
     * @param $Day Следующая дата начала мероприятия
     * @param $time Время начала мероприятия
     * @return int
     */
    protected function getWeek($Day, $time)
    {
        //return strtotime($Day. " " . $time[0] . " hours" . $time[1] . " minutes");

        return strtotime($Day, $this->bringingTimeEvent($time));
    }

    /**
     * @param $Day Следующая дата начала мероприятия
     * @param $time Время начала мероприятия
     * @return int
     */
    protected function getDay($Day, $time)
    {
        return strtotime($Day. " " . $time[0] . " hours" . $time[1] . " minutes");
    }

    /**
     * @param $day  Текущий день недели в формате возращаемом функцией getdate()
     * @return int Нормализированный день недели в соответствии с массивом wdays
     */
    protected function normalizationDay($day)
    {
        switch ($day) {
            case 0:
                $normDay = 0;
                break;
            case 1:
                $normDay = 64;
                break;
            case 2:
                $normDay = 32;
                break;
            case 3:
                $normDay = 16;
                break;
            case 4:
                $normDay = 8;
                break;
            case 5:
                $normDay = 4;
                break;
            case 6:
                $normDay = 2;
                break;
        }
        return $normDay;
    }

    /**
     * @param $days Дни прохождения мероприятия
     * @param $time Время начала мероприятия
     * @return string Ближайший день прохождения мероприятия
     */
    protected function getDays($days, $time)
    {
        $today = $this->getToday();
        $day = $this->normalizationDay($today["wday"]);

        if ($days < $day){
            return $this->getDay($this->wdays[$this->nextBit($days, $day)], $time);
        }elseif ($days == $day){
            if ($time[0] > $today["hours"]) {
                return $this->bringingTimeEvent($time);
            }elseif (($time[0] == $today["hours"]) && ($time[1] >= $today["minutes"])){
                return $this->bringingTimeEvent($time);
            }else{
                return $this->getWeek("+1 week", $time);
            }
        }elseif ($days > $day){
            if ($this->currentBit($days, $day, $time)){
                return $this->bringingTimeEvent($time);
            }elseif ($this->nextBit($days, $day) != 0) {
                return $this->getDay($this->wdays[$this->nextBit($days, $day)], $time);
            }else {
                return $this->getDay($this->wdays[$this->prevBit($days)], $time);
            }
        }

        return $days;
    }

    /**
     * @param $days Дни, по которым проходят мероприятия
     * @param $time Время начала мероприятия
     * @param $timezoneAdmin Временная зона заданная администратором
      * @param $timezoneUser Временная зона заданная пользователем
     * @return string Ближайший день начала мероприятия или сообщение об ошибке, если входные данные некорректны
     */
    public function getEventDay($days, $time, $timezoneAdmin, $timezoneUser)
    {
        if (($this->validateDays($days)) && ($this->validateTime($time)) && ($this->validateTimezone($timezoneAdmin)) && ($this->validateTimezone($timezoneUser))) {

            // Сохраняем текущую временную зону
            $nametz = $this->getTimeZone();

            // Устанавливаем временную зону, в которой задавалось мероприятие и получаем дату ближайшего мероприятия
            $this->setTimeZone($timezoneAdmin);
            $arr = $this->getTimeOfString($time);
            $upcomingEvent = $this->getDays($days, $arr);

            // Устанавливаем текущую временную зону и возвращаем дату мероприятия
            $this->setTimeZone($timezoneUser);
            return "Дата ближайшего мероприятия: " . date('d\.m\.Y H:i:s', $upcomingEvent);
        }

        return "An error in the input data request!";

    }

}