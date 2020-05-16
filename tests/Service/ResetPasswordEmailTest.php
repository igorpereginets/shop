<?php

namespace App\Tests\Service;

use App\Service\EmailProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mime\Address;

class ResetPasswordEmailTest extends TestCase
{
    private static $fromEmail;
    private static $adminEmail;
    private static $toEmail;
    private static $token;

    public static function setUpBeforeClass()
    {
        self::$fromEmail = 'noreply@test.com';
        self::$adminEmail = 'testadmin@test.com';
        self::$toEmail = 'test@tedt.com';
        self::$token = '12345';
    }

    public function testNotNull()
    {
        $emailProvider = new EmailProvider(self::$fromEmail, self::$adminEmail);
        $resetEmail = $emailProvider->getResetPasswordEmail(self::$toEmail, self::$token);

        $this->assertNotNull($resetEmail);

        return $resetEmail;
    }

    /**
     * @depends testNotNull
     */
    public function testFromFieldContainsOnlyOneSender($resetEmail)
    {
        $fromArray = $resetEmail->getFrom();

        $this->assertCount(1, $fromArray);
        $this->assertArrayHasKey(0, $fromArray);
        $this->assertInstanceOf(Address::class, $fromArray[0]);
        $this->assertEquals(self::$fromEmail, $fromArray[0]->getAddress());
    }

    /**
     * @depends testNotNull
     */
    public function testToFieldContainsOnlyOneReceiver($resetEmail)
    {
        $toArray = $resetEmail->getTo();

        $this->assertCount(1, $toArray);
        $this->assertArrayHasKey(0, $toArray);
        $this->assertInstanceOf(Address::class, $toArray[0]);
        $this->assertEquals(self::$toEmail, $toArray[0]->getAddress());
    }

    /**
     * @depends testNotNull
     */
    public function testMandatoryFields($resetEmail)
    {
        $this->assertEquals('Reset Password', $resetEmail->getSubject());
        $this->assertEquals(self::$adminEmail, $resetEmail->getReturnPath()->getAddress());
        $this->assertEquals(3, $resetEmail->getPriority());
    }

    /**
     * @depends testNotNull
     */
    public function testTokenIsInsideBodyAndHtml($resetEmail)
    {
        $this->assertStringContainsString(self::$token, $resetEmail->getHtmlBody());
        $this->assertStringContainsString(self::$token, $resetEmail->getTextBody());
    }

    /**
     * @depends testNotNull
     */
    public function testEmptyFields($resetEmail)
    {
        $this->assertEmpty($resetEmail->getAttachments());
        $this->assertEmpty($resetEmail->getReplyTo());
        $this->assertEmpty($resetEmail->getCc());
        $this->assertEmpty($resetEmail->getBcc());
    }

    public function testEmptyInvalidData()
    {
        $emailProvider = new EmailProvider('', '');

        $this->expectException(\LogicException::class);
        $emailProvider->getResetPasswordEmail('', '');
    }
}