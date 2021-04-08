<?php
/**
 * @package yii2-mandrill
 * @author Simon Karlen <simi.albi@gmail.com>
 */

namespace yiiunit\extensions\mandrill;

use nickcv\mandrill\Mailer;

/**
 * Class MandrillSendTest
 * @package yiiunit\extensions\mandrill
 */
class MandrillSendTest extends TestCase
{
    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        parent::setUp();

        if (!isset($_ENV['MANDRILL_API_KEY'])) {
            $this->markTestSkipped('API Key not set in secrets. Test skipped.');
        }
    }

    public function testSendMessage()
    {
        $mandrill = new Mailer(['apikey' => $_ENV['MANDRILL_API_KEY']]);
        $result = $mandrill->compose('test')
                           ->setTo('test@example.com')
                           ->setSubject('test email')
                           ->embed($this->getTestImagePath())
                           ->attach($this->getTestPdfPath())
                           ->send();

        $this->assertInternalType('array', $mandrill->getLastTransaction());
        $lastTransaction = $mandrill->getLastTransaction()[0];
        $this->assertArrayHasKey('email', $lastTransaction);
        $this->assertEquals('test@example.com', $lastTransaction['email']);
        $this->assertArrayHasKey('status', $lastTransaction);
        $this->assertEquals('queued', $lastTransaction['status']);
        $this->assertArrayHasKey('_id', $lastTransaction);

        $this->assertTrue($result);
    }

    public function testSendMessageUsingMandrillTemplate()
    {
        $mandrill = new Mailer([
            'apikey' => $_ENV['MANDRILL_API_KEY'],
            'useMandrillTemplates' => true,
        ]);
        $result = $mandrill->compose('testTemplate', ['WORD' => 'my word'])
                           ->setTo('test@example.com')
                           ->setSubject('test template email')
                           ->embed($this->getTestImagePath())
                           ->attach($this->getTestPdfPath())
                           ->setGlobalMergeVars(['MERGEVAR' => 'prova'])
                           ->send();

        $this->assertInternalType('array', $mandrill->getLastTransaction());
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
        $mandrill = new Mailer([
            'apikey' => $_ENV['MANDRILL_API_KEY'],
            'useMandrillTemplates' => true,
            'useTemplateDefaults' => false,
            'templateLanguage' => Mailer::LANGUAGE_HANDLEBARS,
        ]);
        $result = $mandrill->compose('testTemplateHandlebars', ['variable' => 'test content'])
                           ->setFrom('testing@creationgears.com')
                           ->setTo('test@example.com')
                           ->setSubject('test handlebars')
                           ->send();

        $this->assertInternalType('array', $mandrill->getLastTransaction());
        $lastTransaction = $mandrill->getLastTransaction()[0];
        $this->assertArrayHasKey('email', $lastTransaction);
        $this->assertEquals('test@example.com', $lastTransaction['email']);
        $this->assertArrayHasKey('status', $lastTransaction);
        $this->assertArrayHasKey('_id', $lastTransaction);

        $this->assertTrue($result);
    }

    public function testCannotSendIfMandrillTemplateNotFound()
    {
        $mandrill = new Mailer([
            'apikey' => $_ENV['MANDRILL_API_KEY'],
            'useMandrillTemplates' => true,
        ]);

        $result = $mandrill->compose('madeupTemplate', ['WORD' => 'my word'])
                           ->setTo('test@example.com')
                           ->setSubject('test template email')
                           ->embed($this->getTestImagePath())
                           ->attach($this->getTestPdfPath())
                           ->send();

        $this->assertInternalType('array', $mandrill->getLastTransaction());
        $this->assertCount(0, $mandrill->getLastTransaction());

        $this->assertFalse($result);
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
