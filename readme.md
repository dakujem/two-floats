# Two Floats

![PHP from Packagist](https://img.shields.io/packagist/php-v/dakujem/cumulus)
[![Build Status](https://travis-ci.org/dakujem/two-floats.svg?branch=main)](https://travis-ci.org/dakujem/two-floats)

**Floating-point number comparison** helper for PHP.\
Framework agnostic. No requirements.

> 💿 `composer require dakujem/two-floats`


## Comparing floating-point numbers

Let's face it. Comparing floats is a pain in any computing language.
(_Most_, at best.)

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
use Dakujem\TwoFloats;
```

Compare for equality:
```php
$num1 = 0.17;
$num2 = 1 - 0.83; // 0.17

if(TwoFloats::same($num1, $num2)) {
    // ...
}
```

Optionally, a custom _precision_ setting can be used, if desired:
```php
$num1 = 0.0095;
$num2 = 0.0094;

TwoFloats::same($num1, $num2);     // false
TwoFloats::same($num1, $num2, 3);  // true
```

Using comparison:
```php
$c = TwoFloats::compare($num1, $num2 /*, $precision */);
if( $c > 0 ) {
    // $num1 > $num2
} elseif( $c < 0 ) {
    // $num1 < $num2
} else {
    // $num1 == $num2
}
```

> Note:
>
> Internally, BC Math is used for comparison by default, but is not required.
> A fallback to a native numeric PHP algorithm is used when BC Math extension is not present.

To force either BC-Math-only or native-only algorithm, methods with `*Bcm` and `*Native` suffixes can be used:

```php
TwoFloats::same( ... );          // BC Math by default, with native fallback
TwoFloats::sameBcm( ... );       // BC Math only
TwoFloats::sameNative( ... );    // native numeric algo only

TwoFloats::compare( ... );       // BC Math by default, with native fallback
TwoFloats::compareBcm( ... );    // BC Math only
TwoFloats::compareNative( ... ); // native numeric algo only
```
A custom precision setting can be used with all the comparison methods above.

The native implementation uses _epsilon_ instead of _scale_ for precision.\
The method `TwoFloats::scaleToEpsilon` can be used to calculate epsilon from scale
and `TwoFloats::epsilonToScale` for the inverse.


## Why

I have not found a reliable tested and simple to use library
I could plug-in to my solutions.\
I found several questionable algorithms that use string comparisons, but for that,
[BC Math](https://www.php.net/manual/en/book.bc.php) must be a better bet,
I had thought.\
I also found several epsilon-based numeric algos
that would not handle edge-cases well.\
And I also found a couple of arbitrary-precision libraries and extensions
that are too heavy to use or too slow.

I wanted to implement the numeric algorithm as a fallback for BC math wrapper,
but using BC Math proved to be a total headache
(no support for scientific notations `1e-8`
and the need to convert to strings properly without loosing precision,
which itself is a problem on its own).
So i dropped the idea.

Finally, I decided to implement [the algorithm from "The Floating-Point Guide"](https://floating-point-gui.de/errors/comparison/) for PHP.
It seemed reasonable performant and precise.
However, it is inconvenient for cases where we want to neglect decimals
after certain fixed (absolute) precision on purpose,
because its epsilon works in relative fashion.
Consequently, I ended up implementing two numeric algorithms instead;
one with relative epsilon for maximum precision,
the other one with fixed precision for better practical usability and convenience.

I would like this to become the final reliable tested open-source
plug-in solution for comparing floating-point numbers in PHP.\
Any type of contribution is welcome.


## Other options

- [BC Math](https://www.php.net/manual/en/book.bc.php)



Using [BC Math](https://www.php.net/manual/en/book.bc.php) has its own caveats.

The default precision for BC Math computations is `0` (for some reason 🤷‍♂️),
which assumes either explicitly passing
[`PHP_FLOAT_DIG` constant](https://www.php.net/manual/en/reserved.constants.php)
as scale for each computation,
or setting it globally by calling [`bcscale`](https://www.php.net/manual/en/function.bcscale.php):
```php
bcscale(PHP_FLOAT_DIG);
```
However, as with most functions with side effects,
this approach will fail if `bcscale` is called again (e.g., unexpectedly).
Note that `bcscale` might affect other running threads as well.

As a result, the correct way to compare two numbers for equality
with maximum precision using BC Math is somewhat verbose:
```php
if ( bccomp($a, $b, PHP_FLOAT_DIG) === 0 ) { ... };
```


## Acknowledgements
 
 This package implements the algorithm published at:
 - [The Floating-Point Guide webpage](https://floating-point-gui.de/errors/comparison/)
 - [brazzy/floating-point-gui.de GitHub repository](https://github.com/brazzy/floating-point-gui.de)
 
 
## Contributing

Ideas or contribution is welcome. Please send a PR or file an issue.
