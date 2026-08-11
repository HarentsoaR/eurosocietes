<?php

namespace Tests\Unit\Import;

use App\Import\QualityChecker;
use PHPUnit\Framework\TestCase;

class QualityCheckerTest extends TestCase
{
    private QualityChecker $checker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->checker = new QualityChecker();
    }

    public function test_siren_valide(): void
    {
        $this->assertTrue($this->checker->validerSiren('356000000'));
    }

    public function test_siren_invalide(): void
    {
        $this->assertFalse($this->checker->validerSiren('123456789'));
        $this->assertFalse($this->checker->validerSiren('35600000'));
        $this->assertFalse($this->checker->validerSiren('35600000A'));
    }

    public function test_siret_valide_et_invalide(): void
    {
        $this->assertTrue($this->checker->validerSiret('35600000000006'));
        $this->assertFalse($this->checker->validerSiret('35600000000007'));
    }

    public function test_est_radiee(): void
    {
        $this->assertTrue($this->checker->estRadiee('C'));
        $this->assertFalse($this->checker->estRadiee('A'));
    }
}
