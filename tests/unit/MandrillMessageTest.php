<?php


class MandrillMessageTest extends \Codeception\TestCase\Test
{
   /**
    * @var \CodeGuy
    */
    protected $tester;

    /**
     * @var \nickcv\mandrill\Message
     */
    private $_message;

    /**
     * @var string
     */
    private $_testImageBinary;
    /**
     * @var string
     */
    private $_testPdfBinary;

    protected function _before()
    {
        $this->_message = new \nickcv\mandrill\Message;
    }

    protected function _after()
    {
    }

    public function testMessageSetCharset()
    {
        $this->assertInstanceOf('\nickcv\mandrill\Message', $this->_message->setCharset('utf-8'));
        $this->assertNull($this->_message->getCharset());
    }

    public function testMessageSetTags()
    {
        $this->assertInstanceOf('\nickcv\mandrill\Message', $this->_message->setTags('tag1'));
        $this->assertInstanceOf('\nickcv\mandrill\Message', $this->_message->setTags(array('tag1','tag2','tag3')));
        $this->assertInstanceOf('\nickcv\mandrill\Message', $this->_message->setTags('_tag5'));

        $tags = $this->_message->getTags();
        $this->assertCount(3, $tags);
        $this->assertContains('tag1', $tags);
        $this->assertContains('tag2', $tags);
        $this->assertContains('tag3', $tags);
    }

    public function testChangeMessageAsyncMode()
    {
        $this->assertFalse($this->_message->isAsync());
        $this->assertInstanceOf('\nickcv\mandrill\Message', $this->_message->enableAsync());
        $this->assertTrue($this->_message->isAsync());
        $this->assertInstanceOf('\nickcv\mandrill\Message', $this->_message->disableAsync());
        $this->assertFalse($this->_message->isAsync());
    }

    public function testSetTemplateData()
    {
        // mailchimp
        $this->assertInstanceOf('\nickcv\mandrill\Message', $this->_message->setTemplateData('viewName', [
            'money' => 300,
        ]));

        $this->assertEquals('viewName', $this->_message->getTemplateName());

        $this->assertEquals([
            [
                'name' => 'money',
                'content' => 300,
            ]
        ], $this->_message->getTemplateContent());

        // handlebars
        $this->assertInstanceOf('\nickcv\mandrill\Message', $this->_message->setTemplateData('viewName', [
            'money' => 300,
        ], \nickcv\mandrill\Message::LANGUAGE_HANDLEBARS));

        $this->assertEquals('viewName', $this->_message->getTemplateName());

        $this->assertEquals([
            [
                'name' => 'money',
                'content' => 300,
            ]
        ], $this->_message->getGlobalMergeVars());

        $this->assertEquals(\nickcv\mandrill\Message::LANGUAGE_HANDLEBARS,
            $this->_message->getMandrillMessageArray()['merge_language']);
    }

    public function testMessageSetRecipient()
    {
        $this->assertInstanceOf('\nickcv\mandrill\Message', $this->_message->setTo('email@email.it'));
        $this->assertInstanceOf('\nickcv\mandrill\Message', $this->_message->setTo(['email2@email.it' => 'fakeuser','email@email.it','email3@email.it', 'asdf', 'fakeuser' => 'email4@email.it']));
        $contactList = $this->_message->getTo();
        $this->assertCount(3, $contactList);
        $this->assertContains('email@email.it', $contactList);
        $this->assertArrayHasKey('email2@email.it', $contactList);
        $this->assertContains('email3@email.it', $contactList);
        $this->assertContains('fakeuser', $contactList);
    }

    public function testMessageSetSender()
    {
        $this->assertEquals('My Application<admin@example.com>', $this->_message->getFrom());

        $this->assertInstanceOf('\nickcv\mandrill\Message', $this->_message->setFrom('email@email.it'));
        $this->assertEquals('My Application<email@email.it>', $this->_message->getFrom());

        $this->assertInstanceOf('\nickcv\mandrill\Message', $this->_message->setFrom(['email2@email.it']));
        $this->assertEquals('My Application<email@email.it>', $this->_message->getFrom());

        $this->assertInstanceOf('\nickcv\mandrill\Message', $this->_message->setFrom(['asdf']));
        $this->assertEquals('My Application<email@email.it>', $this->_message->getFrom());

        $this->assertInstanceOf('\nickcv\mandrill\Message', $this->_message->setFrom('asdf'));
        $this->assertEquals('My Application<email@email.it>', $this->_message->getFrom());

        $this->assertInstanceOf('\nickcv\mandrill\Message', $this->_message->setFrom(['fakeuser' => 'email4@email.it']));
        $this->assertEquals('My Application<email@email.it>', $this->_message->getFrom());

        $this->assertInstanceOf('\nickcv\mandrill\Message', $this->_message->setFrom(['email2@email.it' => 'fakeuser']));
        $this->assertEquals('fakeuser<email2@email.it>', $this->_message->getFrom());

        $this->assertInstanceOf('\nickcv\mandrill\Message', $this->_message->setFrom(['email3@email.it' => '']));
        $this->assertEquals('My Application<email3@email.it>', $this->_message->getFrom());

        $this->assertInstanceOf('\nickcv\mandrill\Message', $this->_message->setFrom(['email4@email.it' => []]));
        $this->assertEquals('My Application<email4@email.it>', $this->_message->getFrom());

        $this->assertInstanceOf('\nickcv\mandrill\Message', $this->_message->setFrom('email@email.it'));

        $this->assertEquals('My Application<email@email.it>', $this->_message->getFrom());
    }

    public function testMessageSetReplyTo()
    {
        $this->assertInstanceOf('\nickcv\mandrill\Message', $this->_message->setReplyTo('email@email.it'));
        $this->assertInstanceOf('\nickcv\mandrill\Message', $this->_message->setReplyTo(['email2@email.it' => 'fakeuser','email@email.it','email3@email.it', 'asdf', 'fakeuser' => 'email4@email.it']));
        $contactList = $this->_message->getReplyTo();
        $this->assertCount(3, $contactList);
        $this->assertContains('email@email.it', $contactList);
        $this->assertArrayHasKey('email2@email.it', $contactList);
        $this->assertContains('email3@email.it', $contactList);
        $this->assertContains('fakeuser', $contactList);
    }

    public function testMessageSetCC()
    {
        $this->assertInstanceOf('\nickcv\mandrill\Message', $this->_message->setCc('email@email.it'));
        $this->assertInstanceOf('\nickcv\mandrill\Message', $this->_message->setCc(['email2@email.it' => 'fakeuser','email@email.it','email3@email.it', 'asdf', 'fakeuser' => 'email4@email.it']));
        $contactList = $this->_message->getCc();
        $this->assertCount(3, $contactList);
        $this->assertContains('email@email.it', $contactList);
        $this->assertArrayHasKey('email2@email.it', $contactList);
        $this->assertContains('email3@email.it', $contactList);
        $this->assertContains('fakeuser', $contactList);
    }

    public function testMessageSetBCC()
    {
        $this->assertInstanceOf('\nickcv\mandrill\Message', $this->_message->setBcc('email@email.it'));
        $this->assertInstanceOf('\nickcv\mandrill\Message', $this->_message->setBcc(['email2@email.it'  => 'fakeuser','email@email.it','email3@email.it', 'asdf', 'fakeuser' => 'email4@email.it']));
        $contactList = $this->_message->getBcc();
        $this->assertCount(3, $contactList);
        $this->assertContains('email@email.it', $contactList);
        $this->assertArrayHasKey('email2@email.it', $contactList);
        $this->assertContains('email3@email.it', $contactList);
        $this->assertContains('fakeuser', $contactList);
    }

    public function testMessageSetSubject()
    {
        $this->assertInstanceOf('\nickcv\mandrill\Message', $this->_message->setSubject('    <a>Testo '));
        $this->assertEquals('&lt;a&gt;Testo', $this->_message->getSubject());
    }

    public function testMessageSetTextBody()
    {
        $this->assertInstanceOf('\nickcv\mandrill\Message', $this->_message->setTextBody('testo<script>alert("ciao");</script>'));
        $this->assertEquals('testo', $this->_message->getTextBody());
    }

    public function testMessageSetHtmlBody()
    {
        $this->assertInstanceOf('\nickcv\mandrill\Message', $this->_message->setHtmlBody('<a>testo</a><script>alert("ciao");</script>'));
        $this->assertEquals('<a>testo</a>', $this->_message->getHtmlBody());
    }

    public function testMessageAttach()
    {
        $this->assertInstanceOf('\nickcv\mandrill\Message', $this->_message->attach($this->getTestImagePath()));
        $this->assertInstanceOf('\nickcv\mandrill\Message', $this->_message->attach($this->getTestImagePath(), ['fileName'=>'test2.png','contentType'=>'text/html']));
        $this->assertInstanceOf('\nickcv\mandrill\Message', $this->_message->attach(__DIR__.DIRECTORY_SEPARATOR.'asdf.png'));
        $this->assertInstanceOf('\nickcv\mandrill\Message', $this->_message->attach(__DIR__));
        $this->assertInstanceOf('\nickcv\mandrill\Message', $this->_message->attachContent($this->getTestPdfBinary()));
        $this->assertInstanceOf('\nickcv\mandrill\Message', $this->_message->attachContent($this->getTestPdfBinary(),['fileName'=>'12.txt','contentType'=>'image/png']));

        $attachments = $this->_message->getAttachments();
        $this->assertCount(4, $attachments);

        $this->assertEquals($this->getTestImageBinary(true), $attachments[0]['content']);
        $this->assertEquals('test.png', $attachments[0]['name']);
        $this->assertEquals('image/png', $attachments[0]['type']);

        $this->assertEquals($this->getTestImageBinary(true), $attachments[1]['content']);
        $this->assertEquals('test2.png', $attachments[1]['name']);
        $this->assertEquals('text/html', $attachments[1]['type']);

        $this->assertEquals($this->getTestPdfBinary(true), $attachments[2]['content']);
        $this->assertEquals('file_2', $attachments[2]['name']);
        $this->assertEquals('application/pdf', $attachments[2]['type']);

        $this->assertEquals($this->getTestPdfBinary(true), $attachments[3]['content']);
        $this->assertEquals('12.txt', $attachments[3]['name']);
        $this->assertEquals('image/png', $attachments[3]['type']);
    }

    public function testMessageEmbed()
    {
        $this->assertInstanceOf('\nickcv\mandrill\Message', $this->_message->embed($this->getTestImagePath()));
        $this->assertInstanceOf('\nickcv\mandrill\Message', $this->_message->embed($this->getTestImagePath(), ['fileName'=>'test2.png','contentType'=>'image/jpeg']));
        $this->assertInstanceOf('\nickcv\mandrill\Message', $this->_message->embed(__DIR__.DIRECTORY_SEPARATOR.'asdf.png'));
        $this->assertInstanceOf('\nickcv\mandrill\Message', $this->_message->embed(__DIR__));
        $this->assertInstanceOf('\nickcv\mandrill\Message', $this->_message->embed($this->getTestPdfPath()));
        $this->assertInstanceOf('\nickcv\mandrill\Message', $this->_message->embedContent('ancora un po'));
        $this->assertInstanceOf('\nickcv\mandrill\Message', $this->_message->embedContent($this->getTestImageBinary()));
        $this->assertInstanceOf('\nickcv\mandrill\Message', $this->_message->embedContent($this->getTestImageBinary(),['fileName'=>'12.txt','contentType'=>'text/html']));

        $attachments = $this->_message->getEmbeddedContent();
        $this->assertCount(4, $attachments);

        $this->assertEquals($this->getTestImageBinary(true), $attachments[0]['content']);
        $this->assertEquals('test.png', $attachments[0]['name']);
        $this->assertEquals('image/png', $attachments[0]['type']);

        $this->assertEquals($this->getTestImageBinary(true), $attachments[1]['content']);
        $this->assertEquals('test2.png', $attachments[1]['name']);
        $this->assertEquals('image/jpeg', $attachments[1]['type']);

        $this->assertEquals($this->getTestImageBinary(true), $attachments[2]['content']);
        $this->assertEquals('file_2', $attachments[2]['name']);
        $this->assertEquals('image/png', $attachments[2]['type']);

        $this->assertEquals($this->getTestImageBinary(true), $attachments[3]['content']);
        $this->assertEquals('12.txt', $attachments[3]['name']);
        $this->assertEquals('text/html', $attachments[3]['type']);
    }

    public function testMessageGlobalMergeVars()
    {
        $this->assertInstanceOf('\nickcv\mandrill\Message', $this->_message->setGlobalMergeVars(['var' => 'value']));
        $this->assertInstanceOf('\nickcv\mandrill\Message', $this->_message->setGlobalMergeVars(['_illegal' => 'value']));
        $this->assertInstanceOf('\nickcv\mandrill\Message', $this->_message->setGlobalMergeVars(['var2' => 'value2', 'var3' => 'value3']));

        $this->assertEquals([
            [
                'name' => 'var',
                'content' => 'value',
            ],
            [
                'name' => 'var2',
                'content' => 'value2',
            ],
            [
                'name' => 'var3',
                'content' => 'value3',
            ],
        ], $this->_message->getGlobalMergeVars());
    }

    public function testMessageUseTemplateDefaults()
    {
        $this->assertEquals('My Application<admin@example.com>', $this->_message->getFrom());

        $this->_message->enableTemplateDefaults();

        $this->assertNull($this->_message->getFrom());

        $this->_message->disableTemplateDefaults();

        $this->assertInstanceOf('\nickcv\mandrill\Message', $this->_message->setFrom('email@email.it'));
        $this->assertEquals('My Application<email@email.it>', $this->_message->getFrom());

        $this->_message->enableTemplateDefaults();

        $this->assertEquals('email@email.it', $this->_message->getFrom());

        $this->_message->disableTemplateDefaults();

        $this->assertInstanceOf('\nickcv\mandrill\Message', $this->_message->setFrom(['email2@email.it' => 'fakeuser']));
        $this->assertEquals('fakeuser<email2@email.it>', $this->_message->getFrom());
        $this->_message->enableTemplateDefaults();
        $this->assertEquals('fakeuser<email2@email.it>', $this->_message->getFrom());
    }

    public function testMessageComplete()
    {
        $result = $this->_message
                ->setCharset('utf-8')
                ->setTags('tag1')
                ->setTo('to@email.it')
                ->setFrom('from@email.it')
                ->setReplyTo('reply@email.it')
                ->setCc('cc@email.it')
                ->setBcc('bcc@email.it')
                ->setGlobalMergeVars(['var1' => 'value1'])
                ->setSubject('    <a>Testo ')
                ->setTextBody('testo<script>alert("ciao");</script>')
                ->setHtmlBody('<a>testo</a><script>alert("ciao");</script>')
                ->attachContent($this->getTestPdfBinary(),['fileName'=>'12.txt','contentType'=>'image/png'])
                ->embed($this->getTestImagePath());
        $this->assertInstanceOf('\nickcv\mandrill\Message', $result);

        $this->assertNull($this->_message->getCharset());

        $tags = $this->_message->getTags();
        $this->assertCount(1, $tags);
        $this->assertContains('tag1', $tags);

        $to = $this->_message->getTo();
        $this->assertCount(1, $to);
        $this->assertContains('to@email.it', $to);

        $this->assertEquals('My Application<from@email.it>', $this->_message->getFrom());

        $reply = $this->_message->getReplyTo();
        $this->assertCount(1, $reply);
        $this->assertContains('reply@email.it', $reply);

        $cc = $this->_message->getCc();
        $this->assertCount(1, $cc);
        $this->assertContains('cc@email.it', $cc);

        $bcc = $this->_message->getBcc();
        $this->assertCount(1, $bcc);
        $this->assertContains('bcc@email.it', $bcc);

        $this->assertEquals([['name' => 'var1', 'content' => 'value1']], $this->_message->getGlobalMergeVars());

        $this->assertEquals('&lt;a&gt;Testo', $this->_message->getSubject());
        $this->assertEquals('testo', $this->_message->getTextBody());
        $this->assertEquals('<a>testo</a>', $this->_message->getHtmlBody());

        $attachments = $this->_message->getAttachments();
        $this->assertCount(1, $attachments);
        $this->assertEquals($this->getTestPdfBinary(true), $attachments[0]['content']);
        $this->assertEquals('12.txt', $attachments[0]['name']);
        $this->assertEquals('image/png', $attachments[0]['type']);


        $embeds = $this->_message->getEmbeddedContent();
        $this->assertCount(1, $embeds);
        $this->assertEquals($this->getTestImageBinary(true), $embeds[0]['content']);
        $this->assertEquals('test.png', $embeds[0]['name']);
        $this->assertEquals('image/png', $embeds[0]['type']);
    }

    public function testMessageArray()
    {
        $result = $this->_message
                ->setCharset('utf-8')
                ->setTags('tag1')
                ->setTo('to@email.it')
                ->setFrom('from@email.it')
                ->setReplyTo(['reply@email.it', 'reply2@email.it' => 'user'])
                ->setCc('cc@email.it')
                ->setBcc('bcc@email.it')
                ->setSubject('    <a>Testo ')
                ->setGlobalMergeVars(['var1' => 'value1'])
                ->setTextBody('testo<script>alert("ciao");</script>')
                ->setHtmlBody('<a>testo</a><script>alert("ciao");</script>')
                ->attachContent($this->getTestPdfBinary(),['fileName'=>'12.txt','contentType'=>'image/png'])
                ->embed($this->getTestImagePath());
        $this->assertInstanceOf('\nickcv\mandrill\Message', $result);

        $array = $this->_message->getMandrillMessageArray();
        $this->assertEquals('reply@email.it;user <reply2@email.it>', $array['headers']['Reply-To']);
        $this->assertEquals('<a>testo</a>', $array['html']);
        $this->assertEquals('testo', $array['text']);
        $this->assertEquals('&lt;a&gt;Testo', $array['subject']);
        $this->assertEquals('from@email.it', $array['from_email']);
        $this->assertEquals('My Application', $array['from_name']);

        $this->assertEquals([['name' => 'var1', 'content' => 'value1']], $array['global_merge_vars']);

        $to = $array['to'];
        $this->assertCount(3, $to);

        $this->assertEquals('to@email.it', $to[0]['email']);
        $this->assertNull($to[0]['name']);
        $this->assertEquals('to', $to[0]['type']);

        $this->assertEquals('cc@email.it', $to[1]['email']);
        $this->assertNull($to[1]['name']);
        $this->assertEquals('cc', $to[1]['type']);

        $this->assertEquals('bcc@email.it', $to[2]['email']);
        $this->assertNull($to[2]['name']);
        $this->assertEquals('bcc', $to[2]['type']);

        $this->assertTrue($array['track_opens']);
        $this->assertTrue($array['track_clicks']);
        $this->assertContains('tag1', $array['tags']);

        $attachments = $array['attachments'];
        $this->assertCount(1, $attachments);
        $this->assertEquals($this->getTestPdfBinary(true), $attachments[0]['content']);
        $this->assertEquals('12.txt', $attachments[0]['name']);
        $this->assertEquals('image/png', $attachments[0]['type']);


        $embeds = $array['images'];
        $this->assertCount(1, $embeds);
        $this->assertEquals($this->getTestImageBinary(true), $embeds[0]['content']);
        $this->assertEquals('test.png', $embeds[0]['name']);
        $this->assertEquals('image/png', $embeds[0]['type']);

        $this->_message->enableTemplateDefaults();

        $arrayTemplateDefaults = $this->_message->getMandrillMessageArray();
        $this->assertEquals('from@email.it', $arrayTemplateDefaults['from_email']);
        $this->assertNull($arrayTemplateDefaults['from_name']);
    }

    public function testMessageString()
    {
        $string = $this->_message
                ->setCharset('utf-8')
                ->setTags('tag1')
                ->setTo('to@email.it')
                ->setFrom('from@email.it')
                ->setReplyTo(['reply@email.it', 'reply2@email.it' => 'user'])
                ->setCc('cc@email.it')
                ->setBcc('bcc@email.it')
                ->setSubject('My Message')
                ->toString();

        $this->assertEquals('My Message - Recipients: [TO] to@email.it [CC] cc@email.it [BCC] bcc@email.it', $string);
    }

    /**
     * @return string
     */
    private function getTestImagePath()
    {
        return __DIR__.DIRECTORY_SEPARATOR.'test.png';
    }

    /**
     * @param boolean $encode
     * @return string
     */
    private function getTestImageBinary($encode = false)
    {
        if ($this->_testImageBinary === null)
            $this->_testImageBinary = file_get_contents($this->getTestImagePath());

        return $encode ? base64_encode($this->_testImageBinary) : $this->_testImageBinary;
    }

    /**
     * @return string
     */
    private function getTestPdfPath()
    {
        return __DIR__.DIRECTORY_SEPARATOR.'test.pdf';
    }

    /**
     * @param boolean $encode
     * @return string
     */
    private function getTestPdfBinary($encode = false)
    {
        if ($this->_testPdfBinary === null)
            $this->_testPdfBinary = file_get_contents($this->getTestPdfPath());

        return $encode ? base64_encode($this->_testPdfBinary) : $this->_testPdfBinary;
    }

}