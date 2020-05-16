<?php

namespace App\Tests\Service;

use App\Service\EmailProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mime\Address;

class ConfirmationEmailTest extends TestCase
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
        $confirmationEmail = $emailProvider->getConfirmationEmail(self::$toEmail, self::$token);

        $this->assertNotNull($confirmationEmail);

        return $confirmationEmail;
    }

    /**
     * @depends testNotNull
     */
    public function testFromFieldContainsOnlyOneSender($confirmationEmail)
    {
        $fromArray = $confirmationEmail->getFrom();
        $this->assertCount(1, $fromArray);
        $this->assertArrayHasKey(0, $fromArray);
        $this->assertInstanceOf(Address::class, $fromArray[0]);
        $this->assertEquals(self::$fromEmail, $fromArray[0]->getAddress());
    }

    /**
     * @depends testNotNull
     */
    public function testToFieldContainsOnlyOneReceiver($confirmationEmail)
    {
        $toArray = $confirmationEmail->getTo();
        $this->assertCount(1, $toArray);
        $this->assertArrayHasKey(0, $toArray);
        $this->assertInstanceOf(Address::class, $toArray[0]);
        $this->assertEquals(self::$toEmail, $toArray[0]->getAddress());
    }

    /**
     * @depends testNotNull
     */
    public function testMandatoryFields($confirmationEmail)
    {
        $this->assertEquals('Confirm your account', $confirmationEmail->getSubject());
        $this->assertEquals(self::$adminEmail, $confirmationEmail->getReturnPath()->getAddress());
        $this->assertEquals(3, $confirmationEmail->getPriority());
    }

    /**
     * @depends testNotNull
     */
    public function testTokenIsInsideBodyAndHtml($confirmationEmail)
    {
        $this->assertStringContainsString(self::$token, $confirmationEmail->getHtmlBody());
        $this->assertStringContainsString(self::$token, $confirmationEmail->getTextBody());
    }

    /**
     * @depends testNotNull
     */
    public function testEmptyFields($confirmationEmail)
    {
        $this->assertEmpty($confirmationEmail->getAttachments());
        $this->assertEmpty($confirmationEmail->getReplyTo());
        $this->assertEmpty($confirmationEmail->getCc());
        $this->assertEmpty($confirmationEmail->getBcc());
    }

    public function testInvalidData()
    {
        $emailProvider = new EmailProvider('', '');

        $this->expectException(\LogicException::class);
        $emailProvider->getConfirmationEmail('', '');
    }
}