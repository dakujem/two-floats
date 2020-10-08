<?php

declare(strict_types=1);

namespace Dakujem\TwoFloats\Tests;

use Dakujem\TwoFloats;
use PHPUnit\Framework\TestCase;

final class TF_NumericTest extends TestCase
{
    public function testBasic(): void
    {
        $same = [
            [1, 1],
            [843994202.303411, 843994202.303411],
            [.1 + .2, .3],
            [0.17, 1 - 0.83],
            [.1e-500, .1e-500],
            [.1e-300 + .2e-300, .3e-300],
        ];
        $notSame = [
            [.1, .3],
            [1e-10, 2e-10],
            [.1e-50, .1e-51],
            [.1e-299, .1e-300],
            [.1000001e-300, .1000002e-300],
        ];
        $this->performTestWithSets($same, $notSame);
    }

    public function testPrecision(): void
    {
        var_dump(0.0001 - 0.0002);
        $same = [
            [0.0001, 0.0002, 1],
            [0.0011, 0.0012, 0.1],
        ];
        $notSame = [
            [0.0095, 0.0094],
        ];
        $this->performTestWithSets($same, $notSame);
    }

    public function testEdgeCases()
    {
        $same = [
            [0, -0],
        ];
        $notSame = [
        ];
        $this->performTestWithSets($same, $notSame);
    }

    private function performTestWithSets(array $same, array $notSame): void
    {
        foreach ($same as $set) {
            $this->assertTrue(TwoFloats::sameNative(...$set), sprintf('Failing: %s IS equal to %s.', ...$set));
        }
        foreach ($notSame as $set) {
            $this->assertFalse(TwoFloats::sameNative(...$set), sprintf('Failing: %s IS NOT equal to %s.', ...$set));
        }
    }
}
