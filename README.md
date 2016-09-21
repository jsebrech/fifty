# fifty
PHP nano-framework live-codeable during a 50 minute talk

## Intro

Fifty is a PHP nano-framework that reduces the key components of a framework down to their essence, a single function.
It is the minix of PHP frameworks. Useful in certain cases, but probably you want linux instead.

The covered components are:

* Routing: _::route
* Authentication: _::authenticate
* Marshalling: _::cast
* Validation: _::validate
* Database: _::query
* Templating: _::render

To try this out:

- In your composer.json, include this

        "repositories": [{
          "type": "git",
          "url": "https://github.com/jsebrech/fifty"
        }],
        "require": {
            "jsebrech/fifty": "dev-master"
        }

- In your code

        use Fifty\_;

To see how to use it in practice, check out the [nano-blog](https://github.com/jsebrech/fiftyblog) built with this framework.

## _::route

Router.
 
Usage:

      echo _::route($_SERVER["REQUEST_URI"], [
        "/" => "Page::index",
        "/logout" => function() { _::authenticate(false); },
        "/post/(.+)" => "Page::view",
        "/edit/(.+)" => "Page::edit",
        "/delete/(.+)" => "Page::delete",
        ".*" => "Page::notFound"
      ]);

It will run the first matching route.
The captured parts from the regex are passed as arguments to the controller.
The controller must return the generated output.

## _::authenticate

Pluggable authentication module.
 
Usage:

      class AuthPlugin {
        function login($arg) {
          // do the login here
          return $user;
        }
        function logout() {
          // logout here
        }
      }
      
      // initialize in app router
      _::$auth = new AuthPlugin();
      
      // authenticate and log in if needed
      // true can be replaced with $arg to pass to plugin login()
      $user = _::authenticate(true);
      
      // check if a user is logged in
      $user = _::authenticate();
      
      // logout the current user
      $user = _::authenticate(false);

## _::cast

Data marshalling layer. Convert from raw $_POST, JSON or DB content to class instance.

Usage:

      class Example {
        /** @var int */
        public $number;
        /** @var string[] */
        public $list;
      }
      $obj = _::cast(["number" => "123foo", "list" => [1, 2]], "Example");
      var_dump($obj);
      
      // output:  
      object(Example)#5 (2) {
        ["number"]=>
        int(123)
        ["list"]=>
        array(2) {
          [0]=>
          string(1) "1"
          [1]=>
          string(1) "2"
        }
      }

Arbitrary nesting is supported. 
Doc comment types can be any JSON-compatible type, any class, or any array of those types.

Tip: you can cast $_POST, $_GET, json_decode($HTTP_RAW_POST_DATA), etc...

## _::validate

Validation utility.

Usage:

      if (!_::validate($url, "url", FILTER_FLAG_HOST_REQUIRED)) {
        // oops
      }

This is a wrapper around [filter_var](http://php.net/manual/en/function.filter-var.php), with the second parameter prefixed with FILTER_VALIDATE_

## _::query

Database layer.

Usage:

      // during init
      _::$pdo = new PDO(...);
      
      // query users with name starting with J
      $rows = _::query(
        "select id, name from users where name like ?",
        ["t" => "J%"]
      )->fetchAll()
      
      // insert a User instance with $id and $name properties
      _::query(
        "insert into users (id, name) values (:id, :name)",
        (array) $user
      )

Tip: this can be combined with _::cast

      $users = array_map(
        function($row) { return _::cast($row, "User"); },
        _::query(...)->fetchAll()
      );

## _::render

Template rendering.

Usage:

- Code:

        echo _::render("views/page.phtml", ["title" => "Test", "say" => "Hello, World!"]);
      
- Template (views/page.phtml):

        <html>
        <head><title><?= htmlentities($title) ?></title></head>
        <body><?= $body ?></body>
        </html>
      
Obviously the templates are PHP files, with the arguments to _::render injected into them.
