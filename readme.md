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

TwoFloats::same($num1, $num2);                         // false
TwoFloats::same($num1, $num2, TwoFloats::epsilon(3));  // true, precision limited to 3 frac. digits
```

For `< = >` comparison:
```php
$c = TwoFloats::compare($num1, $num2);
if( $c > 0 ) {
    // $num1 > $num2
} elseif( $c < 0 ) {
    // $num1 < $num2
} else {
    // $num1 == $num2
}
// or with limited precision...
$c = TwoFloats::compare($num1, $num2, TwoFloats::epsilon($decimals) );
```

The method `TwoFloats::epsilon` can be used to calculate _epsilon_ (ε)
used by the comparison methods
from the number of desired fraction digits.


## Two comparison algorithms

Internally, there are two distinct comparison algorithms.
- one is used for maximum precision
- other one is used for convenience when intentionally limiting precision

### Relative epsilon algorithm

This algorithm yields **maximum possible platform precision**
when using floating-point numeric computations.

It is used by default by `TwoFloats::same` and `Twofloats::compare` methods
when no epsilon is explicitly passed.

The algorithm is described in detail on ["The Floating-Point Guide"](https://floating-point-gui.de/errors/comparison/) site.

### Fixed epsilon

The second algorithm is more practical
when the precision is intentionally limited to a fixed number of fraction digits.

It is used by `TwoFloats::same` and `Twofloats::compare` methods
when an epsilon is explicitly passed.


🚧 TODO

| Comparison | Epsilon (ε) | Relative ε algo | Absolute ε algo |
|------------|-------------|:----------------|:----------------|
| `0.1 == 0.1` | `1` | `true` | `true` |


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
    - has multiple caveats (scale, works with string only, hard not to lose precision)
- [PHP Decimal](https://php-decimal.io)
    - only installable as PECL extension


## Acknowledgements
 
 One of the algorithms implemented in this package is published at:
 - [The Floating-Point Guide webpage](https://floating-point-gui.de/errors/comparison/)
 - [brazzy/floating-point-gui.de GitHub repository](https://github.com/brazzy/floating-point-gui.de)
 
 
## Contributing

Ideas or contribution is welcome. Please send a PR or file an issue.
