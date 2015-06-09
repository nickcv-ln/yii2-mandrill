<?php


class MandrillTest extends \Codeception\TestCase\Test
{
   /**
    * @var \UnitTester
    */
    protected $tester;

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    public function testApikeyIsRequired()
    {
        $this->setExpectedException('yii\base\InvalidConfigException', '"nickcv\mandrill\Mailer::apikey" cannot be null.');
        new \nickcv\mandrill\Mailer();
    }

    public function testApikeyMustBeString()
    {
        $this->setExpectedException('yii\base\InvalidConfigException', '"nickcv\mandrill\Mailer::apikey" should be a string, "array" given.');
        new \nickcv\mandrill\Mailer(['apikey'=>array()]);
    }

    public function testApikeyLengthGreaterThanZero()
    {
        $this->setExpectedException('yii\base\InvalidConfigException', '"nickcv\mandrill\Mailer::apikey" length should be greater than 0.');
        new \nickcv\mandrill\Mailer(['apikey'=>' ']);
    }

    public function testTemplateLanguageIsValid()
    {
        $this->setExpectedException('yii\base\InvalidConfigException', '"nickcv\mandrill\Mailer::templateLanguage" has an invalid value.');

        new \nickcv\mandrill\Mailer(['templateLanguage' => 'invalid', 'apikey' => 'string']);
    }

    public function testSetUseMandrillTemplates()
    {
        $mandrillWithoutTemplates = new \nickcv\mandrill\Mailer(['apikey'=>'testing']);
        $this->assertFalse($mandrillWithoutTemplates->useMandrillTemplates);
        $mandrillWithTemplates = new \nickcv\mandrill\Mailer(['apikey'=>'testing', 'useMandrillTemplates' => true]);
        $this->assertTrue($mandrillWithTemplates->useMandrillTemplates);
    }

    public function testSetUseTemplateDefaults()
    {
        $mandrillWithoutTemplatesDefaults = new \nickcv\mandrill\Mailer(['apikey'=>'testing']);
        $this->assertTrue($mandrillWithoutTemplatesDefaults->useTemplateDefaults);
        $mandrillWithTemplatesDefaults = new \nickcv\mandrill\Mailer(['apikey'=>'testing', 'useTemplateDefaults' => false]);
        $this->assertFalse($mandrillWithTemplatesDefaults->useTemplateDefaults);
    }

    public function testSendMessage()
    {
        $mandrill = new \nickcv\mandrill\Mailer(['apikey'=>'raHz6vHU9J2YxN-F1QryTw']);
        $result = $mandrill->compose('test')
                ->setTo('test@example.com')
                ->setSubject('test email')
                ->embed($this->getTestImagePath())
                ->attach($this->getTestPdfPath())
                ->send();

        $this->assertTrue($result);
    }

    public function testSendMessageUsingMandrillTemplate()
    {
        $mandrill = new \nickcv\mandrill\Mailer([
            'apikey'=>'raHz6vHU9J2YxN-F1QryTw',
            'useMandrillTemplates' => true,
        ]);
        $result = $mandrill->compose('testTemplate', ['WORD' => 'my word'])
                ->setTo('test@example.com')
                ->setSubject('test template email')
                ->embed($this->getTestImagePath())
                ->attach($this->getTestPdfPath())
                ->setGlobalMergeVars(['MERGEVAR' => 'prova'])
                ->send();

        $this->assertTrue($result);
    }

    public function testSendMessageUsingMandrillTemplateHandlebars()
    {
        $mandrill = new \nickcv\mandrill\Mailer([
            'apikey'=>'raHz6vHU9J2YxN-F1QryTw',
            'useMandrillTemplates' => true,
			'useTemplateDefaults' => false,
			'templateLanguage' => \nickcv\mandrill\Mailer::LANGUAGE_HANDLEBARS,
        ]);
        $result = $mandrill->compose('testTemplateHandlebars', ['variable' => 'test content'])
            ->setTo('test@example.com')
            ->setSubject('test handlebars')
            ->send();

        $this->assertTrue($result);
    }

    public function testCannotSendIfMandrillTemplateNotFound()
    {
        $mandrill = new \nickcv\mandrill\Mailer([
            'apikey'=>'raHz6vHU9J2YxN-F1QryTw',
            'useMandrillTemplates' => true,
        ]);

        $result = $mandrill->compose('madeupTemplate', ['WORD' => 'my word'])
            ->setTo('test@example.com')
            ->setSubject('test template email')
            ->embed($this->getTestImagePath())
            ->attach($this->getTestPdfPath())
            ->send();

        $this->assertFalse($result);
    }

    /**
     * @return string
     */
    private function getTestImagePath()
    {
        return __DIR__.DIRECTORY_SEPARATOR.'test.png';
    }

    /**
     * @return string
     */
    private function getTestPdfPath()
    {
        return __DIR__.DIRECTORY_SEPARATOR.'test.pdf';
    }

}