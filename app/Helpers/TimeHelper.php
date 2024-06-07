<?php

namespace App\Helpers;

use DateTime;
use DateTimeZone;

class TimeHelper
{
  public static function convertTimezone($date, $timezone)
  {
    $date = new DateTime($date, new DateTimeZone('UTC'));
    $date->setTimezone(new DateTimeZone($timezone));
    return $date;
  }
}