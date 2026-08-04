<?php

namespace Tests\Unit;

use App\Rules\Cpf;
use PHPUnit\Framework\TestCase;

class CpfTest extends TestCase
{
    /**
     * @dataProvider validCpfProvider
     */
    public function test_accepts_cpf_with_valid_check_digits(string $cpf): void
    {
        $this->assertTrue((new Cpf())->passes('cpf', $cpf));
    }

    /**
     * @dataProvider invalidCpfProvider
     */
    public function test_rejects_invalid_or_repeated_cpf(string $cpf): void
    {
        $this->assertFalse((new Cpf())->passes('cpf', $cpf));
    }

    public function validCpfProvider(): array
    {
        return [
            ['529.982.247-25'],
            ['111.444.777-35'],
            ['168.995.350-09'],
        ];
    }

    public function invalidCpfProvider(): array
    {
        return [
            ['111.111.111-11'],
            ['000.000.000-00'],
            ['529.982.247-24'],
            ['52998224725'],
            ['123.456.789-00'],
        ];
    }
}
