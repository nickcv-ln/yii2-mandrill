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