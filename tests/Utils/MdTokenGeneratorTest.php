<?php

namespace App\Tests\Utils;

use App\Utils\MDTokenGenerator;
use PHPUnit\Framework\TestCase;

class MdTokenGeneratorTest extends TestCase
{
    public function testTokenGenerator()
    {
        $token = (new MDTokenGenerator())->generate();

        $this->assertIsString($token);
        $this->assertNotEmpty($token);
    }

    public function testTokenIsUnique()
    {
        $tokenGenerator = new MDTokenGenerator();

        $firstToken = $tokenGenerator->generate();
        $secondToken = $tokenGenerator->generate();

        $this->assertNotEquals($firstToken, $secondToken);
    }

    public function testIfLengthIsTooBig()
    {
        $this->expectException(\InvalidArgumentException::class);
        (new MDTokenGenerator())->generate(33);
    }

    public function testIfLengthIsTooSmall()
    {
        $this->expectException(\InvalidArgumentException::class);
        (new MDTokenGenerator())->generate(9);
    }

    public function testSafeCharacters()
    {
        $token = (new MDTokenGenerator())->generate();

        $this->assertRegExp('/^.[a-zA-Z0-9_-]*$/m', $token, 'Token should contain only URL safe characters \'a-zA-Z0-9_-\'');
    }

    /**
     * @dataProvider tokenLengthProvider
     */
    public function testTokenLength($length)
    {
        $token = (new MDTokenGenerator())->generate($length);

        $this->assertEquals($length, strlen($token));
    }

    public function tokenLengthProvider()
    {
        return [[32], [10], [15]];
    }
}