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
        $this->expectException('TypeError');
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

    public function testGetMandrill()
    {
        $mandrillMailer = new Mailer(['apikey' => 'testing']);
        $this->assertInstanceOf('\MailchimpTransactional\ApiClient', $mandrillMailer->getMailchimp());
    }
}
