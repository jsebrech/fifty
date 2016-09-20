<?php

namespace Fifty;

class _ {
  public static $auth = null;

  // based on http://upshots.org/php/php-seriously-simple-router
  public static function route($path, $routes) {
    foreach ($routes as $pattern => $callback) {
      if (preg_match('/^'.str_replace('/','\/',$pattern).'$/', strtok($path, "?"), $params) === 1) {
        return call_user_func_array($callback, array_slice($params, 1));
      }
    }
    return false;
  }

  public static function authenticate($a = null) {
    if (!session_id()) session_start();
    if ($a === false) { // logout
      $_SESSION = [];
      session_regenerate_id(true);
      if (self::$auth) self::$auth->logout();
    } else if (isset($_SESSION["user"])) { // logged in
      return $_SESSION["user"];
    } else if ($a && self::$auth) { // login
      return $_SESSION["user"] = self::$auth->login($a);
    }
    return false;
  }

  public static function cast($that, $as) {
    $result = null;
    if (substr($as, -2, 2) == "[]") {
      $result = is_array($that) ? _::map($that, function($o) use ($as) {
        return _::cast($o, substr($as, 0, -2));
      }) : [];
    } else if (class_exists($as)) {
      if (is_object($that) || is_array($that)) {
        $result = new $as();
        $that = (array) $that;
        foreach ((new \ReflectionClass($result))->getProperties() as $prop) {
          if (@($val = $that[$prop->getName()]) !== null) {
            if (preg_match('/@var[\\s]+([\\S]+)/', $prop->getDocComment(), $matches)) {
              $val = _::cast($val, $matches[1]);
            };
            if (isset($val)) {
              $prop->setAccessible(true);
              $prop->setValue($result, $val);
            }
          }
        };
      } else {
        $result = new $as($that);
      }
    } else {
      $result = $that;
      if (!@settype($result, $as)) $result = null;
    }
    return $result;
  }

  public static function validate($value, $rule, $options = null) {
    return filter_var($value, constant("FILTER_VALIDATE_".strtoupper($rule)), $options);
  }

  public static function query(\PDO $pdo, $sql, $bind = []) {
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($bind) ? $stmt : false;
  }

  public static function map($that, $with) {
    return array_map($with, $that, array_keys($that));
  }

  public static function render($template, $data = []) {
    ob_start();
    extract((array) $data);
    include $template;
    return ob_get_clean();
  }
}
