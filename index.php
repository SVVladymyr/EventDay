<?php
/**
 * Author: Vladimir
 * Date: 06.05.2016
 */

include_once "class\EventDay.class.php";

$eventDay = new EventDay();
print_r($eventDay->getEventDay(124, "10:22", 180));

