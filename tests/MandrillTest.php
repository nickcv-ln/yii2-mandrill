<?php

namespace yiiunit\extensions\mandrill;

use nickcv\mandrill\Mailer;

/**
 * Class MandrillTest
 * @package yiiunit\extensions\mandrill
 */
class MandrillTest extends TestCase
{
    public function testApikeyIsRequired()
    {
        $this->expectException('yii\base\InvalidConfigException');
        $this->expectExceptionMessage('"nickcv\mandrill\Mailer::apikey" cannot be null.');
        new Mailer();
    }

    public function testApikeyMustBeString()
    {
        $this->expectException('yii\base\InvalidConfigException');
        $this->expectExceptionMessage('"nickcv\mandrill\Mailer::apikey" should be a string, "array" given.');
        new Mailer(['apikey' => []]);
    }

    public function testApikeyLengthGreaterThanZero()
    {
        $this->expectException('yii\base\InvalidConfigException');
        $this->expectExceptionMessage('"nickcv\mandrill\Mailer::apikey" length should be greater than 0.');

        new Mailer(['apikey' => '']);
    }

    public function testTemplateLanguageIsValid()
    {
        $this->expectException('yii\base\InvalidConfigException');
        $this->expectExceptionMessage('"nickcv\mandrill\Mailer::templateLanguage" has an invalid value.');

        new Mailer(['templateLanguage' => 'invalid', 'apikey' => 'string']);
    }

    public function testSetUseMandrillTemplates()
    {
        $mandrillWithoutTemplates = new Mailer(['apikey' => 'testing']);
        $this->assertFalse($mandrillWithoutTemplates->useMandrillTemplates);
        $mandrillWithTemplates = new Mailer(['apikey' => 'testing', 'useMandrillTemplates' => true]);
        $this->assertTrue($mandrillWithTemplates->useMandrillTemplates);
    }

    public function testSetUseTemplateDefaults()
    {
        $mandrillWithoutTemplatesDefaults = new Mailer(['apikey' => 'testing']);
        $this->assertTrue($mandrillWithoutTemplatesDefaults->useTemplateDefaults);
        $mandrillWithTemplatesDefaults = new Mailer(['apikey' => 'testing', 'useTemplateDefaults' => false]);
        $this->assertFalse($mandrillWithTemplatesDefaults->useTemplateDefaults);
    }

//    public function testSendMessage() TODO
//    {
//        $mandrill = new Mailer(['apikey' => 'wq4uhYEddK1K3WXK8-Adsg']);
//        $result = $mandrill->compose('test')
//                           ->setTo('test@example.com')
//                           ->setSubject('test email')
//                           ->embed($this->getTestImagePath())
//                           ->attach($this->getTestPdfPath())
//                           ->send();
//
//        $this->assertInternalType('array', $mandrill->getLastTransaction());
//        $lastTransaction = $mandrill->getLastTransaction()[0];
//        $this->assertArrayHasKey('email', $lastTransaction);
//        $this->assertEquals('test@example.com', $lastTransaction['email']);
//        $this->assertArrayHasKey('status', $lastTransaction);
//        $this->assertEquals('queued', $lastTransaction['status']);
//        $this->assertArrayHasKey('_id', $lastTransaction);
//
//        $this->assertTrue($result);
//    }
//
//    public function testSendMessageUsingMandrillTemplate()
//    {
//        $mandrill = new Mailer([
//            'apikey' => 'wq4uhYEddK1K3WXK8-Adsg',
//            'useMandrillTemplates' => true,
//        ]);
//        $result = $mandrill->compose('testTemplate', ['WORD' => 'my word'])
//                           ->setTo('test@example.com')
//                           ->setSubject('test template email')
//                           ->embed($this->getTestImagePath())
//                           ->attach($this->getTestPdfPath())
//                           ->setGlobalMergeVars(['MERGEVAR' => 'prova'])
//                           ->send();
//
//        $this->assertInternalType('array', $mandrill->getLastTransaction());
//        $lastTransaction = $mandrill->getLastTransaction()[0];
//        $this->assertArrayHasKey('email', $lastTransaction);
//        $this->assertEquals('test@example.com', $lastTransaction['email']);
//        $this->assertArrayHasKey('status', $lastTransaction);
//        $this->assertEquals('queued', $lastTransaction['status']);
//        $this->assertArrayHasKey('_id', $lastTransaction);
//
//        $this->assertTrue($result);
//    }
//
//    public function testSendMessageUsingMandrillTemplateHandlebars()
//    {
//        $mandrill = new Mailer([
//            'apikey' => 'wq4uhYEddK1K3WXK8-Adsg',
//            'useMandrillTemplates' => true,
//            'useTemplateDefaults' => false,
//            'templateLanguage' => Mailer::LANGUAGE_HANDLEBARS,
//        ]);
//        $result = $mandrill->compose('testTemplateHandlebars', ['variable' => 'test content'])
//                           ->setFrom('testing@creationgears.com')
//                           ->setTo('test@example.com')
//                           ->setSubject('test handlebars')
//                           ->send();
//
//        $this->assertInternalType('array', $mandrill->getLastTransaction());
//        $lastTransaction = $mandrill->getLastTransaction()[0];
//        $this->assertArrayHasKey('email', $lastTransaction);
//        $this->assertEquals('test@example.com', $lastTransaction['email']);
//        $this->assertArrayHasKey('status', $lastTransaction);
//        $this->assertArrayHasKey('_id', $lastTransaction);
//
//        $this->assertTrue($result);
//    }
//
//    public function testCannotSendIfMandrillTemplateNotFound()
//    {
//        $mandrill = new Mailer([
//            'apikey' => 'wq4uhYEddK1K3WXK8-Adsg',
//            'useMandrillTemplates' => true,
//        ]);
//
//        $result = $mandrill->compose('madeupTemplate', ['WORD' => 'my word'])
//                           ->setTo('test@example.com')
//                           ->setSubject('test template email')
//                           ->embed($this->getTestImagePath())
//                           ->attach($this->getTestPdfPath())
//                           ->send();
//
//        $this->assertInternalType('array', $mandrill->getLastTransaction());
//        $this->assertCount(0, $mandrill->getLastTransaction());
//
//        $this->assertFalse($result);
//    }

    public function testGetMandrill()
    {
        $mandrillMailer = new Mailer(['apikey' => 'testing']);
        $this->assertInstanceOf('\MailchimpTransactional\ApiClient', $mandrillMailer->getMailchimp());
    }

    /**
     * @return string
     */
    private function getTestImagePath()
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'test.png';
    }

    /**
     * @return string
     */
    private function getTestPdfPath()
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'test.pdf';
    }

}