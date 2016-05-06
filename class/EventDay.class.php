<?php

/**
 * Created by Vladimir.
 * User: Vladimir
 * Date: 06.05.2016
 */

Class EventDay
{
    /**
     * @var int Максимальный бит (2^6 = 64 - "Понедельник")
     */
    private $maxBit = 64;

    private $wdays = [
        64 => 'Понедельник',
        32 => 'Вторник',
        16 => 'Среда',
        8 => 'Четверг',
        4 => 'Пятница',
        2 => 'Суббота',
        1 => 'Воскресенье',
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
                if (($arr[0] > 0 && $arr[0] < 24) && ($arr[1] > 0 && $arr[1] < 60))
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
     * @return array Возвращаем массив со значениями текущей даты и времени
     */
    protected function getToday()
    {
        return getdate();
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
            return "День ближайшего мероприятия: " . $this->wdays[$this->nextBit($days, $day)];
        }elseif ($days == $day){
            if ($time[0] > $today["hours"]) {
                return "День ближайшего мероприятия: " . $this->wdays[$days];
            }elseif (($time[0] == $today["hours"]) && ($time[1] >= $today["minutes"])){
                return "День ближайшего мероприятия: " . $this->wdays[$days];
            }else{
                return "День ближайшего мероприятие на следующей неделе:  " . $this->wdays[$days];
            }
        }elseif ($days > $day){
            if ($this->nextBit($days, $day) != 0) {
                return "День ближайшего мероприятия: " . $this->wdays[$this->nextBit($days, $day)];
            }else {
                return "День ближайшего мероприятия на следующей неделе: " . $this->wdays[$this->prevBit($days)];
            }
        }

        return $days;
    }

    /**
     * @param $days Дни, по которым проходят мероприятия
     * @param $time Время начала мероприятия
     * @param $timezone Временная зона
     * @return string Ближайший день начала мероприятия или сообщение об ошибке, если входные данные некорректны
     */
    public function getEventDay($days, $time, $timezone)
    {
        if (($this->validateDays($days)) && ($this->validateTime($time)) && ($this->validateTimezone($timezone))) {
            $this->setTimeZone($timezone);
            $arr = $this->getTimeOfString($time);

            return $this->getDays($days, $arr);
        }

        return "An error in the input data request!";

    }

}