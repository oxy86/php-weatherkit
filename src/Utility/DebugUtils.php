<?php

namespace Oxy86\PhpWeatherKit\Utility;


/**
 * Debuging utilities static class.
 */
class DebugUtils
{

  /**
   * Simple object dump for those large objects with huge blobs of text or deep nested structures
   */
  public static function objectDump($object, $size = 70, $max = 5, $level = 0)
  {

    global $config;

    if (empty($config['debug_messages'])) {
      return;
    }

    if ($level > $max) return 'array(...MAX DEPTH REACHED...)';

    $tab = str_repeat("\t", $level);

    $safe = function ($string) use (&$size) {
      $string = htmlspecialchars($string, ENT_QUOTES, 'utf-8');

      $string = preg_replace("~\n\s*~", ' ', str_replace("\r", '', $string));

      if (\mb_strlen($string) > $size) {
        return \mb_substr($string, 0, $size) . '...';
      }

      return $string;
    };

    $temp = array_flip(array_keys((array) $object));

    foreach ((array) $object as $key => $value) {

      if (!is_scalar($value) and $value) {
        $temp[$key] = self::objectDump($value, $size, $max, $level + 1);
        continue;
      }
      /////////////// Added on Feb 23 //////////////
      // Otherwise it returns 'null' on zeros or empty strings ''
      if ($value == 0) {
        $temp[$key] = 0;
        continue;
      }
      if ($value == '') {
        $temp[$key] = '""';
        continue;
      }
      /////////////////////////////////////////////
      if (empty($value)) {
        $temp[$key] = 'null';
        continue;
      }
      $temp[$key] = ($value === NULL ? 'null' : $safe($value));
    }

    if ($level === 0) {
      self::e(__METHOD__);
      print "<pre>\n" . print_r($temp, true) . "</pre>\n";
      return;
    }

    return $temp;
  }

  /**
   * Echoes a $msg to the std out, if allowed 
   */
  public static function e(string $logger = __METHOD__, string $msg = "")
  {
    global $config;


    if (empty($config['debug_messages'])) {
      return;
    }

    if (PHP_SAPI !== 'cli') {
      echo time() . " -- $logger === $msg" . "<br />";
    } else {
      echo time() . " -- $logger === $msg" . "\n";
    }
  }
}
