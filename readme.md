# Two Floats

A static helper class to compare two floating-point numbers for equality.

> 💿 `composer require dakujem/two-floats`


## Comparing floating-point numbers

Let's face it. Comparing floats is a pain in any language. (_Most_, at best.)

If you've ever encountered a WTF moment like the following, you know.
```php
var_dump( 0.1 + 0.2 === 0.3 ); // bool(false) ... wait ... WTF?!
```

Let's fix it:
```php
var_dump( Dakujem\TwoFloats::same(0.1 + 0.2, 0.3) ); // bool(true) ... now we are talking
```


## Usage

```php
$num1 = 0.17;
$num2 = 1 - 0.83; // 0.17

if(Dakujem\TwoFloats::same($num1, $num2)) {
    // ...
}
```

Optionally, a custom _epsilon_ can be used, if desired:
```php
$num1 = 0.0095;
$num2 = 0.0094;

Dakujem\TwoFloats::same($num1, $num2);        // false
Dakujem\TwoFloats::same($num1, $num2, 0.001); // true
```


## Why

I have not found a reliable tested library I could simply plug-in to my solutions.\
I found several algorithms that use string comparisons, but for that,
[BCMath](https://www.php.net/manual/en/book.bc.php) is a better bet, surely.\
Finally, I decided to implement [the algorithm from "The Floating-Point Guide"](https://floating-point-gui.de/errors/comparison/) for PHP.

Still, using this comparison means you do not wish to or can not use
[BCMath](https://www.php.net/manual/en/book.bc.php),
which should still be a more robust solution.


 ## Acknowledgements
 
 This package implements algorithm published at
 [The Floating-Point Guide](https://floating-point-gui.de/errors/comparison/).
 
 
## Contributing

Ideas or contribution is welcome. Please send a PR or file an issue.
