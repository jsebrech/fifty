<?php
/**
 * To run the tests
 * php test.php
 * Output should be "ok"
 */

  include __DIR__."/../src/_.php";
  use Fifty\_;

  assert_options(ASSERT_BAIL, true);

  class T {
    /** @var int */
    public $i;

    /** @var float */
    private $f;

    function getF() { return $this->f; }

    /** @var T[] */
    public $a;

    /** @var T */
    public $me;
  }

  $data = [
    "i" => "20",
    "f" => "3.14",
    "a" =>[
      ["i" => 2],
      ["i" => 2.2]
    ],
    "me" => ["i" => 3]
  ];

  $obj = _::cast($data, "T");
  assert('$obj->i === 20');
  assert('$obj->getF() === 3.14');
  assert('is_array($obj->a)');
  assert('count($obj->a) === 2');
  assert('$obj->a[0] instanceof T');
  assert('$obj->a[0]->i === 2');
  assert('$obj->a[1] instanceof T');
  assert('$obj->a[1]->i === 2');
  assert('$obj->me instanceof T');
  assert('$obj->me->i === 3');

  $arr = _::cast(["2", "3.5"], "int[]");
  assert('is_array($arr)');
  assert('count($arr) === 2');
  assert('$arr[0] === 2');
  assert('$arr[1] === 3');

  $v = _::cast(2, "invalid");
  assert('$v === null');

  $d = _::cast("2016-02-04T12:00:00+02:00", "DateTime");
  assert('$d instanceof DateTime');
  assert('$d->getOffset() === 7200');
  assert('$d->format("Y-m-d H:i:s") === "2016-02-04 12:00:00"');

  echo "ok";