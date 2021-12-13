<?php
/**
 * @package yii2-mandrill
 * @author Simon Karlen <simi.albi@gmail.com>
 */

namespace yiiunit\extensions\mandrill;

use nickcv\mandrill\Mailer;
use Yii;

/**
 * Class MandrillSendTest
 * @package yiiunit\extensions\mandrill
 */
class MandrillSendTest extends TestCase
{
    /**
     * @var string
     */
    private $_apiKey;

    /**
     * @var string
     */
    private $_fromAddress;

    /**
     * @var string
     */
    private $_toAddress;

    public function testSendMessage()
    {
        $mandrill = new Mailer(['apikey' => $this->_apiKey]);
        $result = $mandrill->compose('test')
            ->setFrom($this->_fromAddress)
            ->setTo($this->_toAddress)
            ->setSubject('test email')
            ->embed($this->getTestImagePath())
            ->attach($this->getTestPdfPath())
            ->send();

        $this->assertInternalType('array', $mandrill->getLastTransaction());
        $lastTransaction = $mandrill->getLastTransaction()[0];
        $this->assertArrayHasKey('email', $lastTransaction);
        $this->assertEquals($this->_toAddress, $lastTransaction['email']);
        $this->assertArrayHasKey('status', $lastTransaction);
        $this->assertEquals('queued', $lastTransaction['status']);
        $this->assertArrayHasKey('_id', $lastTransaction);

        $this->assertTrue($result);
    }

    public function testSendMessageUsingMandrillTemplate()
    {
        $mandrill = new Mailer([
            'apikey' => $this->_apiKey,
            'useMandrillTemplates' => true,
        ]);
        $result = $mandrill->compose('testTemplate', ['WORD' => 'my word'])
            ->setFrom($this->_fromAddress)
            ->setTo($this->_toAddress)
            ->setSubject('test template email')
            ->embed($this->getTestImagePath())
            ->attach($this->getTestPdfPath())
            ->setGlobalMergeVars(['MERGEVAR' => 'prova'])
            ->send();

        $this->assertInternalType('array', $mandrill->getLastTransaction());
        $lastTransaction = $mandrill->getLastTransaction()[0];
        $this->assertArrayHasKey('email', $lastTransaction);
        $this->assertEquals($this->_toAddress, $lastTransaction['email']);
        $this->assertArrayHasKey('status', $lastTransaction);
        $this->assertEquals('queued', $lastTransaction['status']);
        $this->assertArrayHasKey('_id', $lastTransaction);

        $this->assertTrue($result);
    }

    public function testSendMessageUsingMandrillTemplateHandlebars()
    {
        $mandrill = new Mailer([
            'apikey' => $this->_apiKey,
            'useMandrillTemplates' => true,
            'useTemplateDefaults' => false,
            'templateLanguage' => Mailer::LANGUAGE_HANDLEBARS,
        ]);
        $result = $mandrill->compose('testTemplateHandlebars', ['variable' => 'test content'])
            ->setFrom($this->_fromAddress)
            ->setTo($this->_toAddress)
            ->setSubject('test handlebars')
            ->send();

        $this->assertInternalType('array', $mandrill->getLastTransaction());
        $lastTransaction = $mandrill->getLastTransaction()[0];
        $this->assertArrayHasKey('email', $lastTransaction);
        $this->assertEquals($this->_toAddress, $lastTransaction['email']);
        $this->assertArrayHasKey('status', $lastTransaction);
        $this->assertArrayHasKey('_id', $lastTransaction);

        $this->assertTrue($result);
    }

    public function testCannotSendIfMandrillTemplateNotFound()
    {
        $mandrill = new Mailer([
            'apikey' => $this->_apiKey,
            'useMandrillTemplates' => true,
        ]);

        $result = $mandrill->compose('madeupTemplate', ['WORD' => 'my word'])
            ->setFrom($this->_fromAddress)
            ->setTo($this->_toAddress)
            ->setSubject('test template email')
            ->embed($this->getTestImagePath())
            ->attach($this->getTestPdfPath())
            ->send();

        $this->assertInternalType('array', $mandrill->getLastTransaction());
        $this->assertArrayHasKey('status', $mandrill->getLastTransaction());
        $this->assertEquals('error', $mandrill->getLastTransaction()['status']);

        $this->assertFalse($result);
    }

    /**
     * @depends testSendMessage
     */
    public function testSendAt()
    {
        $mandrill = new Mailer(['apikey' => $this->_apiKey]);
        $result = $mandrill->compose('test')
            ->setFrom($this->_fromAddress)
            ->setTo($this->_toAddress)
            ->setSubject('test send at email')
            ->setSendAt(Yii::$app->formatter->asDate('+5min', 'yyyy-MM-dd HH:mm:ss'))
            ->send();

        $this->assertInternalType('array', $mandrill->getLastTransaction());
        $lastTransaction = $mandrill->getLastTransaction()[0];
        $this->assertArrayHasKey('email', $lastTransaction);
        $this->assertEquals($this->_toAddress, $lastTransaction['email']);
        $this->assertArrayHasKey('status', $lastTransaction);
        $this->assertEquals('scheduled', $lastTransaction['status']);
        $this->assertArrayHasKey('_id', $lastTransaction);

        $this->assertTrue($result);
    }

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->_apiKey = getenv('MANDRILL_API_KEY');
        $this->_fromAddress = getenv('MANDRILL_FROM_ADDRESS');
        $this->_toAddress = getenv('MANDRILL_TO_ADDRESS');

        if (!$this->_apiKey || !$this->_fromAddress || !$this->_toAddress) {
            $this->markTestSkipped('One of "API key", "from address" or "to address" not set in secrets. Test skipped.');
        }
    }

    /**
     * @return string
     */
    private function getTestImagePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'test.png';
    }

    /**
     * @return string
     */
    private function getTestPdfPath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'test.pdf';
    }
}
