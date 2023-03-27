# php-svg-path-data

PHP-SVG-PATH-DATA is a PHP library which can help during work with PHP-SVG library. It allows to add SVG path data with object oriented structure.

## Installation

PHP-SVG-PATH-DATA requires PHP version 8.1 or newer. This package is available on [Packagist](https://packagist.org/packages/kaareln/php-svg-path-data) and can be installed with Composer:

```bash
$ composer require kaareln/php-svg-path-data
```

## Usage

SVGPath instructions are represented as a string, which can make it difficult to manipulate. However, by using an object representation, the path can be constructed and parsed more easily. This approach also provides the benefits of object-oriented programming, such as the ability to keep references to different parts of the path and manipulate them as needed. This can lead to more efficient and organized code when working with SVGPaths:

```php
$path = new SVGPath();

$data = new SVGPathData();

$data->addCommand(new Move(0, 0));

$topLine = new Line(20, 0);
$data->addCommand($topLine);

$rightLine = new Line(20, 20);
$data->addCommand($rightLine);

// at this point I have a references to $topLine and $rightLine, which I can use to modify individually, for example I can move them by 20px

$topLine->x += 20;
$rightLine->x += 20;

// convert the instructions to string and save as d param
$path->setArgument('d', $data->__toString());
```
Using a parser, individual objects representing different parts of a path can be created and modified for more precise changes and greater flexibility:

```php
// assume $path is an instanceof SVGPath

$pathData = SVGPathData::fromString($path->getArgument('d'));

// loop for each command in reverse order and possibly change/read parts of the path
foreach ($pathData as $command) {
  if ($command instanceof Line) {
    // move each line coordinates by 20px
    $command->x += 20;
  }
}

// alternatively you can use `transform` method that runs against each command and allows replacing them. In this example I replace all bezier curves with straight lines
$pathData->transform(function (PathDataCommandInterface $command) {
  if ($command instanceof BezierCurve) {
    return new Line($command->x, $command->y);
  }
});

// update the path
$path->setArgument('d', $pathData->__toString());
```

## Contributing

Pull requests are welcome. For major changes, please open an issue first
to discuss what you would like to change.

Please make sure to update tests as appropriate.

## License

[MIT](https://choosealicense.com/licenses/mit/)
