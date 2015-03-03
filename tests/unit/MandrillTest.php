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
    
    public function testSetUseMandrillTemplates()
    {
        $mandrillWithoutTemplates = new \nickcv\mandrill\Mailer(['apikey'=>'testing']);
        $this->assertFalse($mandrillWithoutTemplates->useMandrillTemplates);
        $mandrillWithTemplates = new \nickcv\mandrill\Mailer(['apikey'=>'testing', 'useMandrillTemplates' => true]);
        $this->assertTrue($mandrillWithTemplates->useMandrillTemplates);
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
				->send();
		
		$this->assertTrue($result);
    }
    
    public function testCannotSendIfMandrillTemplateAndViewNotFound()
    {
        $directory = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'mail' . DIRECTORY_SEPARATOR;
        $this->setExpectedException('yii\base\InvalidParamException', 'The view file does not exist: '.$directory.'madeupTemplate.php');
        
        $mandrill = new \nickcv\mandrill\Mailer([
            'apikey'=>'raHz6vHU9J2YxN-F1QryTw',
            'useMandrillTemplates' => true,
        ]);
		$mandrill->compose('madeupTemplate', ['WORD' => 'my word'])
            ->setTo('test@example.com')
            ->setSubject('test template email')
            ->embed($this->getTestImagePath())
            ->attach($this->getTestPdfPath())
            ->send();
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