# Slender Micro Framework
This is intend to be a very light framework.
Its first purpose is to understand the principles of MVC structure and easly create a site based on this structure.
So, it is helpfull to study and improvement the concepts of a framework.

## Inspiration
This framework is inspired on [Laravel framework](https://laravel.com/).
Many aspects are similar, like folder structure and some blade anotations.
Also, part of this work is inspired on a great article of how to implement a route system, writen by Alexandre Barbosa on [this](https://alexandrebbarbosa.wordpress.com/2019/04/17/phpconstruir-um-sistema-de-rotas-para-mvc-primeira-parte/) post (in portuguese).

## Principles
The principle of this framework is to build small pieces of code and join them as packages.
For this, some repos are related:
- [lcloss/view](https://github.com/lcloss/php-view) to handle Views and templates. Also available through [composer](https://packagist.org/packages/lcloss/view).
- [lcloss/route](https://github.com/lcloss/php-route) to handle Routes and requests. Also available through [composer](https://packagist.org/packages/lcloss/route).
- [lcloss/db](https://github.com/lcloss/php-db) to handle Database abstraction and queries. Also available through [composer](https://packagist.org/packages/lcloss/db).
- [lcloss/env](https://github.com/lcloss/php-env) to handle environment configuration. also available through [composer](https://packagist.org/packages/lcloss/env).

Each of these packages can be used separately, in any project.

## Features
This folder structure is used:
```
+- app
|  +- controller        // Where controllers are
|  +- model             // Where models are
+- bootstrap            // To bootstrap application (includes and constants)
+- helpers              // Functions to help
+- public               // Public folder (where index.php is and css, js, ...)
+- resources            // Where views are
|  +- view              // Views for pages
+- routes               // To manage routes
+- vendor               // Composer packages
```

## Basic usage
Follow these steps to run this framework:
1. Copy or clone this repo to your local folder
2. Copy `.env.example` file on root folder to `.env`
3. Edit `.env` file with your settings
4. Create or customize a view in `resources\views` folder.
4.1. This sample uses `resources\views\layouts\app.tpl.php` as the main view.
4.2. And `resources\views\index.tpl.php` as the main page, wich extends `layouts\app.tpl.php` view.
5. Create or customize routes in `routes\router.php`
6. Create or customize the controller in `app\controller` folder.
6.1. This sample uses `app\controller\AppController.php` as the main controller.

### Routes
Routes are described in this form:
`Route::get(['set' => '/', 'as' => 'home'], 'AppController@index');`

The `set` value is the uri string.
The `as` value is a name setted to this route. You can refer to a route in a view, by name, as `@route('home')`. This will produces a `http://localhost/` address.
Then, set the controller and method for this route. In this case, method `index` will control the main view, in `AppController.php` file.

Example with parameters:
`Route::get(['set' => '/article/{id}/edit', 'as' => 'edit.article'], 'ArticlesController@edit');`

This route is point to `http://localhost/article/1/edit` when you use the `@route('edit.article', ['id' => 1])` in a view. You can also use as `@route('edit.article', 1)`

### Views
The main syntax used in a view are:

`@extends(view)`, `@section()` and `@yield`
---
This will extend the `view` and include the `sections` inside a `yield` notation.
If there is no `section`, the main content will be included on `content` yield. 
For example:
`app.tpl.php`
```
<html>
    <head>
    </head>
    <body>
    @yield(content)
    </body>
</html>
```

`index.tpl.php`
```
@extends(app)
This is the main content
```

Or, you can also have:
`index.tpl.php`
```
@extends(app)
@section(content)
This is the main content
@endsection
```

It will be helpfull to include styles and scripts, like:
`app.tpl.php`
```
<html>
    <head>
    @yield(styles)
    </head>
    <body>
    @yield(content)
    @yield(scripts)
    </body>
</html>
```

`index.tpl.php`
```
@extends(app)
@section(styles)
<style>
.any-format {
    font-size: 0.8em;
    color: gray;
}
</style>
@endsection
@section(content)
This is the main content
@endsection
@section(scripts)
<script>
alert('Say hello!');
</script>
@endsection
```

`@route()`
---
You can easly create routes.
```
<nav>
    <li><a href="@route('index')">Home</a></li>
    <li><a href="@route('blog')">Blog</a></li>
    <li><a href="@route('blog.show', 1)">How to install Slender Micro Framework</a></li>
    <li><a href="@route('blog.create')">New article</a></li>
</nav>
```

`{{ $variables }}`
---
You can easly insert variables in views:
`resources\views\user.tpl.php`
```
<h1>Introduction</h1>
<p>Hello {{ $user }}! You are welcome!</p>
```
And in a controller:
`app\controller\UserController.php`
```
$data = [
    'user' => 'Luciano Closs'
];

$view = new View('resources.views', 'tpl.php');
$view->setBase( env('base_dir') );

echo $view->view('user', $data);
```

## ToDo
There are a lot work to do!
But, in time, the next developments will handle:
- Create tests
- Create docs
- Middlewares
- CSRF protection (will be included on lcloss/view package)
- Remove dependency of lcloss/env in some packages
- Group of routes
- Improve view parser
- Use cache with views
- A lot more
