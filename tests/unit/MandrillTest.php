<?php


class MandrillTest extends \Codeception\TestCase\Test
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    public function testApikeyIsRequired()
    {
        $this->expectException('yii\base\InvalidConfigException');
        $this->expectExceptionMessage('"nickcv\mandrill\Mailer::apikey" cannot be null.');
        new \nickcv\mandrill\Mailer();
    }

    public function testApikeyMustBeString()
    {
        $this->expectException('yii\base\InvalidConfigException');
        $this->expectExceptionMessage('"nickcv\mandrill\Mailer::apikey" should be a string, "array" given.');
        new \nickcv\mandrill\Mailer(['apikey' => array()]);
    }

    public function testApikeyLengthGreaterThanZero()
    {
        $this->expectException('yii\base\InvalidConfigException');
        $this->expectExceptionMessage('"nickcv\mandrill\Mailer::apikey" length should be greater than 0.');
        new \nickcv\mandrill\Mailer(['apikey' => ' ']);
    }

    public function testTemplateLanguageIsValid()
    {
        $this->expectException('yii\base\InvalidConfigException');
        $this->expectExceptionMessage('"nickcv\mandrill\Mailer::templateLanguage" has an invalid value.');

        new \nickcv\mandrill\Mailer(['templateLanguage' => 'invalid', 'apikey' => 'string']);
    }

    public function testSetUseMandrillTemplates()
    {
        $mandrillWithoutTemplates = new \nickcv\mandrill\Mailer(['apikey' => 'testing']);
        $this->assertFalse($mandrillWithoutTemplates->useMandrillTemplates);
        $mandrillWithTemplates = new \nickcv\mandrill\Mailer(['apikey' => 'testing', 'useMandrillTemplates' => true]);
        $this->assertTrue($mandrillWithTemplates->useMandrillTemplates);
    }

    public function testSetUseTemplateDefaults()
    {
        $mandrillWithoutTemplatesDefaults = new \nickcv\mandrill\Mailer(['apikey' => 'testing']);
        $this->assertTrue($mandrillWithoutTemplatesDefaults->useTemplateDefaults);
        $mandrillWithTemplatesDefaults = new \nickcv\mandrill\Mailer(['apikey' => 'testing', 'useTemplateDefaults' => false]);
        $this->assertFalse($mandrillWithTemplatesDefaults->useTemplateDefaults);
    }

    public function testSendMessage()
    {
        $mandrill = new \nickcv\mandrill\Mailer(['apikey' => 'wq4uhYEddK1K3WXK8-Adsg']);
        $result = $mandrill->compose('test')
            ->setTo('test@example.com')
            ->setSubject('test email')
            ->embed($this->getTestImagePath())
            ->attach($this->getTestPdfPath())
            ->send();

        $this->assertIsArray($mandrill->getLastTransaction());
        $lastTransaction = $mandrill->getLastTransaction()[0];
        $this->assertArrayHasKey('email', $lastTransaction);
        $this->assertEquals('test@example.com', $lastTransaction['email']);
        $this->assertArrayHasKey('status', $lastTransaction);
        $this->assertEquals('queued', $lastTransaction['status']);
        $this->assertArrayHasKey('_id', $lastTransaction);

        $this->assertTrue($result);
    }

    /**
     * @return string
     */
    private function getTestImagePath()
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'test.png';
    }

    /**
     * @return string
     */
    private function getTestPdfPath()
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'test.pdf';
    }

    public function testSendMessageUsingMandrillTemplate()
    {
        $mandrill = new \nickcv\mandrill\Mailer([
            'apikey' => 'wq4uhYEddK1K3WXK8-Adsg',
            'useMandrillTemplates' => true,
        ]);
        $result = $mandrill->compose('testTemplate', ['WORD' => 'my word'])
            ->setTo('test@example.com')
            ->setSubject('test template email')
            ->embed($this->getTestImagePath())
            ->attach($this->getTestPdfPath())
            ->setGlobalMergeVars(['MERGEVAR' => 'prova'])
            ->send();

        $this->assertIsArray($mandrill->getLastTransaction());
        $lastTransaction = $mandrill->getLastTransaction()[0];
        $this->assertArrayHasKey('email', $lastTransaction);
        $this->assertEquals('test@example.com', $lastTransaction['email']);
        $this->assertArrayHasKey('status', $lastTransaction);
        $this->assertEquals('queued', $lastTransaction['status']);
        $this->assertArrayHasKey('_id', $lastTransaction);

        $this->assertTrue($result);
    }

    public function testSendMessageUsingMandrillTemplateHandlebars()
    {
        $mandrill = new \nickcv\mandrill\Mailer([
            'apikey' => 'wq4uhYEddK1K3WXK8-Adsg',
            'useMandrillTemplates' => true,
            'useTemplateDefaults' => false,
            'templateLanguage' => \nickcv\mandrill\Mailer::LANGUAGE_HANDLEBARS,
        ]);
        $result = $mandrill->compose('testTemplateHandlebars', ['variable' => 'test content'])
            ->setFrom('testing@creationgears.com')
            ->setTo('test@example.com')
            ->setSubject('test handlebars')
            ->send();

        $this->assertIsArray($mandrill->getLastTransaction());
        $lastTransaction = $mandrill->getLastTransaction()[0];
        $this->assertArrayHasKey('email', $lastTransaction);
        $this->assertEquals('test@example.com', $lastTransaction['email']);
        $this->assertArrayHasKey('status', $lastTransaction);
        $this->assertArrayHasKey('_id', $lastTransaction);

        $this->assertTrue($result);
    }

    public function testCannotSendIfMandrillTemplateNotFound()
    {
        $mandrill = new \nickcv\mandrill\Mailer([
            'apikey' => 'wq4uhYEddK1K3WXK8-Adsg',
            'useMandrillTemplates' => true,
        ]);

        $result = $mandrill->compose('madeupTemplate', ['WORD' => 'my word'])
            ->setTo('test@example.com')
            ->setSubject('test template email')
            ->embed($this->getTestImagePath())
            ->attach($this->getTestPdfPath())
            ->send();

        $this->assertIsArray($mandrill->getLastTransaction());
        $this->assertCount(0, $mandrill->getLastTransaction());

        $this->assertFalse($result);
    }

    public function testGetMandrill()
    {
        $mandrillMailer = new \nickcv\mandrill\Mailer(['apikey' => 'testing']);
        $this->assertInstanceOf('\Mandrill', $mandrillMailer->getMandrill());
    }

    protected function _before()
    {
    }

    protected function _after()
    {
    }

}