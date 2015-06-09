<?php
/**
 * Contains the Message class
 *
 * @link http://www.creationgears.com/
 * @copyright Copyright (c) 2014 Nicola Puddu
 * @license http://www.gnu.org/copyleft/gpl.html
 * @package nickcv/yii2-mandrill
 * @author Nicola Puddu <n.puddu@outlook.com>
 */

namespace nickcv\mandrill;

use yii\mail\BaseMessage;
use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;

/**
 * Message is the class that is used to store the data of an email message that
 * will be sent through Mandrill API.
 *
 * @author Nicola Puddu <n.puddu@outlook.com>
 * @version 1.0
 */
class Message extends BaseMessage
{
    const LANGUAGE_MAILCHIMP = 'mailchimp';
    const LANGUAGE_HANDLEBARS = 'handlebars';

    /**
     * Contains the custom from address. If empty the adminEmail param of the
     * application will be used.
     *
     * @see \nickcv\mandrill\Message::setFrom() setter
     * @see \nickcv\mandrill\Message::getFrom() getter
     *
     * @var string
     */
    private $_fromAddress;
    /**
     * Contains the custom from name. If empty the app name will be used.
     *
     * @see \nickcv\mandrill\Message::setFrom() setter
     * @see \nickcv\mandrill\Message::getFrom() getter
     *
     * @var string
     */
    private $_fromName;
    /**
     * Contains the TO address list.
     *
     * @see \nickcv\mandrill\Message::setTo() setter
     * @see \nickcv\mandrill\Message::getTo() getter
     *
     * @var array
     */
    private $_to = [];
    /**
     * Contains the reply-to address list.
     *
     * @see \nickcv\mandrill\Message::setReplyTo() setter
     * @see \nickcv\mandrill\Message::getReplyTo() getter
     *
     * @var array
     */
    private $_replyTo = [];
    /**
     * Contains the CC address list.
     *
     * @see \nickcv\mandrill\Message::setCc() setter
     * @see \nickcv\mandrill\Message::getCc() getter
     *
     * @var array
     */
    private $_cc = [];
    /**
     * Contains the BCC address list.
     *
     * @see \nickcv\mandrill\Message::setBcc() setter
     * @see \nickcv\mandrill\Message::getBcc() getter
     *
     * @var array
     */
    private $_bcc = [];
    /**
     * Contains the tags list.
     *
     * @see \nickcv\mandrill\Message::setTags() setter
     * @see \nickcv\mandrill\Message::getTags() getter
     *
     * @var array
     */
    private $_tags = [];
    /**
     * Contains the html-encoded subject.
     *
     * @see \nickcv\mandrill\Message::setSubject() setter
     * @see \nickcv\mandrill\Message::getSubject() getter
     *
     * @var string
     */
    private $_subject;
    /**
     * Contains the email raw text.
     *
     * @see \nickcv\mandrill\Message::setTextBody() setter
     * @see \nickcv\mandrill\Message::getTextBody() getter
     *
     * @var string
     */
    private $_text;
    /**
     * Contains the email HTML test.
     *
     * @see \nickcv\mandrill\Message::setHtmlBody() setter
     * @see \nickcv\mandrill\Message::getHtmlBody() getter
     *
     * @var string
     */
    private $_html;
    /**
     * Contains the list of attachments already processed to be used by Mandrill.
     * Each entry within the array is an array with the following keys:
     *
     * ~~~
     * [
     *  'name' => 'file.png', //the file name
     *  'type' => 'image/png', //the file mime type
     *  'content' => 'dGhpcyBpcyBzb21lIHRleHQ=' //the base64 encoded binary
     * ]
     * ~~~
     *
     * @see \nickcv\mandrill\Message::attach() setter
     * @see \nickcv\mandrill\Message::getAttachments() getter
     *
     * @var array
     */
    private $_attachments = [];
    /**
     * Contains the list of embedded images already processed to be used by Mandrill.
     * Each entry within the array is an array with the following keys:
     *
     * ~~~
     * [
     *  'name' => 'file.png', //the file name
     *  'type' => 'image/png', //the file mime type
     *  'content' => 'dGhpcyBpcyBzb21lIHRleHQ=' //the base64 encoded binary
     * ]
     * ~~~
     *
     * @see \nickcv\mandrill\Message::embed() setter
     * @see \nickcv\mandrill\Message::getEmbeddedContent() getter
     *
     * @var array
     */
    private $_images = [];

    /**
     * Contains the instance of \finfo used to get mime type.
     *
     * @var \finfo
     */
    private $_finfo;

    /**
     * In async mode, messages/send will immediately return a status of
     * "queued" for every recipient.
     *
     * Mandrill defaults this value to false for messages with no more than
     * 10 recipients; messages with more than 10 recipients are always sent
     * asynchronously, regardless of the value of async.
     *
     * @var boolean
     * @since 1.3.0
     */
    private $_async = false;

    /**
     * The name of the template inside mandrill.
     *
     * @var string
     * @since 1.3.0
     */
    private $_templateName;

    /**
     * The values that will be used to replace the placeholders inside the template.
     *
     * @var array
     * @since 1.3.0
     */
    private $_templateContent;

    /**
     * Value use to decide whether the message should calculate default values
     * for the sender based on the application settings or return nulls to use
     * mandrill template defaults.
     *
     * @var boolean
     * @since 1.4.0
     */
    private $_ = false;

    /**
     * Global merge vars used when sending the message to mandrill.
     *
     * @var array
     * @since 1.4.0
     */
    private $_globalMergeVars = [];

    /**
     * What language will be used in the template
     * Check @link http://handlebarsjs.com/ for more documentation about handlebars language
     *
     * @var string
     * @since 1.5.0
     */
    private $_mergeLanguage = self::LANGUAGE_MAILCHIMP;

    /**
     * Mandrill does not let users set a charset.
     *
     * @see \nickcv\mandrill\Message::setCharset() setter
     *
     * @return null
     */
    public function getCharset()
    {
        return null;
    }

    /**
     * Mandrill does not let users set a charset.
     *
     * @see \nickcv\mandrill\Message::getCharset() getter
     *
     * @param string $charset character set name.
     * @return \nickcv\mandrill\Message
     */
    public function setCharset($charset)
    {
        return $this;
    }

    /**
     * Returns the list of tags you already set for this message.
     *
     * @see \nickcv\mandrill\Message::setTags() setter
     *
     * @return array the list of tags
     */
    public function getTags()
    {
        return $this->_tags;
    }

    /**
     * Mandrill lets you use tags to categorize your messages, making it much
     * easier to find the messages your are looking for within their website
     * dashboard.
     *
     * Stats are accumulated within mandrill using tags, though they only store
     * the first 100 they see, so this should not be unique or change frequently.
     * Tags should be 50 characters or less.
     * Any tags starting with an underscore are reserved for internal use and
     * will be ignored.
     *
     * Some common tags include *registration* and *password reset*.
     *
     * @see \nickcv\mandrill\Message::getTags() getter
     *
     * @param string|array $tag tag or list of tags
     * @return \nickcv\mandrill\Message
     */
    public function setTags($tag)
    {
        if (is_string($tag) && $this->isTagValid($tag, '_tags')) {
            $this->_tags[] = $tag;
        }

        if (is_array($tag)) {
            foreach ($tag as $singleTag) {
                if ($this->isTagValid($singleTag, '_tags')) {
                    $this->_tags[] = $singleTag;
                }
            }
        }

        return $this;
    }

    /**
     * Tells whether or not the message will be sent asynchronously.
     *
     * @return boolean
     * @since 1.3.0
     */
    public function isAsync()
    {
        return $this->_async;
    }

    /**
     * Enables async sending for this message.
     *
     * @return \nickcv\mandrill\Message
     * @since 1.3.0
     */
    public function enableAsync()
    {
        $this->_async = true;

        return $this;
    }

    /**
     * Disables async sending the this message.
     *
     * @return \nickcv\mandrill\Message
     * @since 1.3.0
     */
    public function disableAsync()
    {
        $this->_async = false;

        return $this;
    }

    /**
     * Returns the from email address in this format:
     *
     * ~~~
     * Sender Name <email@example.com>
     * ~~~
     *
     * The default value for the sender name is the application name
     * configuration parameter inside `config/web.php`.
     *
     * The default value for the sender address is the adminEmail parameter
     * inside `config/params.php`.
     *
     * @see \nickcv\mandrill\Message::setFrom() setter
     *
     * @return string
     */
    public function getFrom()
    {
        $from = null;

        if ($this->getFromName()) {
            $from .= $this->getFromName();
        }

        if ($this->getFromAddress()) {
            $from .= $from === null ? $this->getFromAddress() : '<' . $this->getFromAddress() . '>';
        }

        return $from;
    }

    /**
     * Sets the message sender.
     *
     * @see \nickcv\mandrill\Message::getFrom() getter
     *
     * @param string|array $from sender email address.
     * You may specify sender name in addition to email address using format:
     * `[email => name]`.
     * If you don't set this parameter the application adminEmail parameter will
     * be used as the sender email address and the application name will be used
     * as the sender name.
     * @return \nickcv\mandrill\Message
     */
    public function setFrom($from)
    {
        if (is_string($from) && filter_var($from, FILTER_VALIDATE_EMAIL)) {
            $this->_fromAddress = $from;
            $this->_fromName = null;
        }

        if (is_array($from)) {
            $address = key($from);
            $name = array_shift($from);
            if (!filter_var($address, FILTER_VALIDATE_EMAIL)) {
                return $this;
            }

            $this->_fromAddress = $address;
            if (is_string($name) && strlen(trim($name)) > 0) {
                $this->_fromName = trim($name);
            } else {
                $this->_fromName = null;
            }
        }

        return $this;
    }

    /**
     * Returns an array of email addresses in the following format:
     *
     * ~~~
     * [
     *  'email1@example.com', //in case no recipient name was submitted
     *  'email2@example.com' => 'John Doe', //in case a recipient name was submitted
     * ]
     * ~~~
     *
     * @see \nickcv\mandrill\Message::setTo() setter
     *
     * @return array
     */
    public function getTo()
    {
        return $this->_to;
    }

    /**
     * Sets the message recipient(s).
     *
     * @see \nickcv\mandrill\Message::getTo() getter
     *
     * @param string|array $to receiver email address.
     * You may pass an array of addresses if multiple recipients should receive this message.
     * You may also specify receiver name in addition to email address using format:
     * `[email => name]`.
     * @return \nickcv\mandrill\Message
     */
    public function setTo($to)
    {
        $this->storeEmailAddressesInContainer($to, '_to');

        return $this;
    }

    /**
     * Returns an array of email addresses in the following format:
     *
     * ~~~
     * [
     *  'email1@example.com', //in case no recipient name was submitted
     *  'email2@example.com' => 'John Doe', //in case a recipient name was submitted
     * ]
     * ~~~
     *
     * @see \nickcv\mandrill\Message::setReplyTo() setter
     *
     * @return array
     */
    public function getReplyTo()
    {
        return $this->_replyTo;
    }

    /**
     * Sets the message recipient(s).
     *
     * @see \nickcv\mandrill\Message::getReplyTo() getter
     *
     * @param string|array $replyTo Reply-To email address.
     * You may pass an array of addresses if multiple recipients should receive this message.
     * You may also specify receiver name in addition to email address using format:
     * `[email => name]`.
     * @return \nickcv\mandrill\Message
     */
    public function setReplyTo($replyTo)
    {
        $this->storeEmailAddressesInContainer($replyTo, '_replyTo');

        return $this;
    }

    /**
     * Returns an array of email addresses in the following format:
     *
     * ~~~
     * [
     *  'email1@example.com', //in case no recipient name was submitted
     *  'email2@example.com' => 'John Doe', //in case a recipient name was submitted
     * ]
     * ~~~
     *
     * @see \nickcv\mandrill\Message::setCc() setter
     *
     * @return array
     */
    public function getCc()
    {
        return $this->_cc;
    }

    /**
     * Sets the message recipient(s).
     *
     * @see \nickcv\mandrill\Message::getCc() getter
     *
     * @param string|array $cc cc email address.
     * You may pass an array of addresses if multiple recipients should receive this message.
     * You may also specify receiver name in addition to email address using format:
     * `[email => name]`.
     * @return \nickcv\mandrill\Message
     */
    public function setCc($cc)
    {
        $this->storeEmailAddressesInContainer($cc, '_cc');

        return $this;
    }

    /**
     * Returns an array of email addresses in the following format:
     *
     * ~~~
     * [
     *  'email1@example.com', //in case no recipient name was submitted
     *  'email2@example.com' => 'John Doe', //in case a recipient name was submitted
     * ]
     * ~~~
     *
     * @see \nickcv\mandrill\Message::setBcc() setter
     *
     * @return array
     */
    public function getBcc()
    {
        return $this->_bcc;
    }

    /**
     * Sets the message recipient(s).
     *
     * @see \nickcv\mandrill\Message::getBcc() getter
     *
     * @param string|array $bcc bcc email address.
     * You may pass an array of addresses if multiple recipients should receive this message.
     * You may also specify receiver name in addition to email address using format:
     * `[email => name]`.
     * @return \nickcv\mandrill\Message
     */
    public function setBcc($bcc)
    {
        $this->storeEmailAddressesInContainer($bcc, '_bcc');

        return $this;
    }

    /**
     * Returns the html-encoded subject.
     *
     * @see \nickcv\mandrill\Message::setSubject() setter
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->_subject;
    }

    /**
     * Sets the message subject.
     *
     * @see \nickcv\mandrill\Message::getSubject() getter
     *
     * @param string $subject
     * The subject will be trimmed and html-encoded.
     * @return \nickcv\mandrill\Message
     */
    public function setSubject($subject)
    {
        if (is_string($subject)) {
            $this->_subject = trim(Html::encode($subject));
        }

        return $this;
    }

    /**
     * Returns the html-purified version of the raw text body.
     *
     * @see \nickcv\mandrill\Message::setTextBody() setter
     *
     * @return string
     */
    public function getTextBody()
    {
        return $this->_text;
    }

    /**
     * Sets the raw text body.
     *
     * @see \nickcv\mandrill\Message::getTextBody() getter
     *
     * @param string $text
     * The text will be purified.
     * @return \nickcv\mandrill\Message
     */
    public function setTextBody($text)
    {
        if (is_string($text)) {
            $this->_text = HtmlPurifier::process($text);
        }

        return $this;
    }

    /**
     * Returns the html purified version of the html body.
     *
     * @see \nickcv\mandrill\Message::setHtmlBody() setter
     *
     * @return string
     */
    public function getHtmlBody()
    {
        return $this->_html;
    }

    /**
     * Sets the html body.
     *
     * @see \nickcv\mandrill\Message::getHtmlBody() getter
     *
     * @param string $html
     * The html will be purified.
     * @return \nickcv\mandrill\Message
     */
    public function setHtmlBody($html)
    {
        if (is_string($html)) {
            $this->_html = HtmlPurifier::process($html);
        }

        return $this;
    }

    /**
     * Returns the attachments array.
     *
     * @see \nickcv\mandrill\Message::attach() setter for file name
     * @see \nickcv\mandrill\Message::attachContent() setter for binary
     *
     * @return array
     */
    public function getAttachments()
    {
        return $this->_attachments;
    }

    /**
     * Attaches existing file to the email message.
     *
     * @see \nickcv\mandrill\Message::getAttachments() getter
     *
     * @param string $fileName full file name
     * @param array $options options for embed file. Valid options are:
     *
     * - fileName: name, which should be used to attach file.
     * - contentType: attached file MIME type.
     *
     * @return \nickcv\mandrill\Message
     */
    public function attach($fileName, array $options = [])
    {
        if (file_exists($fileName) && !is_dir($fileName)) {
            $purifiedOptions = [
                'fileName' => ArrayHelper::getValue($options, 'fileName', basename($fileName)),
                'contentType' => ArrayHelper::getValue($options, 'contentType', FileHelper::getMimeType($fileName)),
            ];
            $this->attachContent(file_get_contents($fileName), $purifiedOptions);
        }

        return $this;
    }

    /**
     * Attach specified content as file for the email message.
     *
     * @see \nickcv\mandrill\Message::getAttachments() getter
     *
     * @param string $content attachment file content.
     * @param array $options options for embed file. Valid options are:
     *
     * - fileName: name, which should be used to attach file.
     * - contentType: attached file MIME type.
     *
     * @return \nickcv\mandrill\Message
     */
    public function attachContent($content, array $options = [])
    {
        $purifiedOptions = is_array($options) ? $options : [];

        if (is_string($content) && strlen($content) !== 0) {
            $this->_attachments[] = [
                'name' => ArrayHelper::getValue($purifiedOptions, 'fileName', ('file_' . count($this->_attachments))),
                'type' => ArrayHelper::getValue($purifiedOptions, 'contentType', $this->getMimeTypeFromBinary($content)),
                'content' => base64_encode($content),
            ];
        }
        return $this;
    }

    /**
     * Returns the images array.
     *
     * @see \nickcv\mandrill\Message::embed() setter for file name
     * @see \nickcv\mandrill\Message::embedContent() setter for binary
     *
     * @return array list of embedded content
     */
    public function getEmbeddedContent()
    {
        return $this->_images;
    }

    /**
     * Embeds an image in the email message.
     *
     * @see \nickcv\mandrill\Message::getEmbeddedContent() getter
     *
     * @param string $fileName file name.
     * @param array $options options for embed file. Valid options are:
     *
     * - fileName: name, which should be used to attach file.
     * - contentType: attached file MIME type.
     *
     * @return \nickcv\mandrill\Message
     */
    public function embed($fileName, array $options = [])
    {
        if (file_exists($fileName) && !is_dir($fileName) && strpos(FileHelper::getMimeType($fileName), 'image') === 0) {
            $purifiedOptions = [
                'fileName' => ArrayHelper::getValue($options, 'fileName', basename($fileName)),
                'contentType' => ArrayHelper::getValue($options, 'contentType', FileHelper::getMimeType($fileName)),
            ];
            $this->embedContent(file_get_contents($fileName), $purifiedOptions);
        }

        return $this;
    }

    /**
     * Embed a binary as an image in the message.
     *
     * @see \nickcv\mandrill\Message::getEmbeddedContent() getter
     *
     * @param string $content attachment file content.
     * @param array $options options for embed file. Valid options are:
     *
     * - fileName: name, which should be used to attach file.
     * - contentType: attached file MIME type.
     *
     * @return \nickcv\mandrill\Message
     */
    public function embedContent($content, array $options = [])
    {
        $purifiedOptions = is_array($options) ? $options : [];

        if (is_string($content) && strlen($content) !== 0 && strpos($this->getMimeTypeFromBinary($content), 'image') === 0) {
            $this->_images[] = [
                'name' => ArrayHelper::getValue($purifiedOptions, 'fileName', ('file_' . count($this->_images))),
                'type' => ArrayHelper::getValue($purifiedOptions, 'contentType', $this->getMimeTypeFromBinary($content)),
                'content' => base64_encode($content),
            ];
        }

        return $this;
    }

    /**
     * Sets the data to be used by the Mandrill template system.
     *
     * @param string $templateName
     * @param array $templateContent
     * @param string $templateLanguage
     *
     * @return \nickcv\mandrill\Message
     * @since 1.3.0
     */
    public function setTemplateData($templateName, array $templateContent = [], $templateLanguage = self::LANGUAGE_MAILCHIMP)
    {
        $this->_templateName = $templateName;

        if ($templateLanguage === self::LANGUAGE_MAILCHIMP) {
            $this->_templateContent = $this->convertParamsForTemplate($templateContent);
        } elseif ($templateLanguage === self::LANGUAGE_HANDLEBARS) {
            $this->setGlobalMergeVars($templateContent);
        }

        $this->_mergeLanguage = $templateLanguage;

        return $this;
    }

    /**
     * Returns the name of the mandrill template to be used.
     *
     * @return string
     * @since 1.3.0
     */
    public function getTemplateName()
    {
        return $this->_templateName;
    }

    /**
     * Returns the dynamic content used to replace blocks in the template.
     *
     * @return array
     * @since 1.3.0
     */
    public function getTemplateContent()
    {
        return $this->_templateContent;
    }

    /**
     * Enable the use of template defaults.
     *
     * @return \nickcv\mandrill\Message
     * @since 1.4.0
     */
    public function enableTemplateDefaults()
    {
        $this->_ = true;

        return $this;
    }

    /**
     * Disable the use of template defaults.
     *
     * @return \nickcv\mandrill\Message
     * @since 1.4.0
     */
    public function disableTemplateDefaults()
    {
        $this->_ = false;

        return $this;
    }

    /**
     * Returns the global merge vars that will be submitted to mandrill.
     *
     * @return array
     * @since 1.4.0
     */
    public function getGlobalMergeVars()
    {
        return $this->_globalMergeVars;
    }

    /**
     * Adds the given merge vars to the global merge vars array.
     * Merge vars are case insensitive and cannot start with _
     *
     * @param array $mergeVars
     *
     * @return \nickcv\mandrill\Message
     * @since 1.4.0
     */
    public function setGlobalMergeVars(array $mergeVars)
    {
        foreach ($mergeVars as $name => $content) {
            if ($name{0} === '_') {
                continue;
            }

            array_push($this->_globalMergeVars, [
                'name' => $name,
                'content' => $content
            ]);
        }

        return $this;
    }

    /**
     * Returns the string representation of this message.
     *
     * @return string
     */
    public function toString()
    {
        return $this->getSubject() . ' - Recipients:'
                . ' [TO] ' . implode('; ', $this->getTo())
                . ' [CC] ' . implode('; ', $this->getCc())
                . ' [BCC] ' . implode('; ', $this->getBcc());
    }

    /**
     * Returns the array used by the Mandrill Class to initialize a message
     * and submit it.
     *
     * @return array
     */
    public function getMandrillMessageArray()
    {
        return [
            'headers' => [
                'Reply-To' => $this->getReplyToString(),
            ],
            'html' => $this->getHtmlBody(),
            'text' => $this->getTextBody(),
            'subject' => $this->getSubject(),
            'from_email' => $this->getFromAddress(),
            'from_name' => $this->getFromName(),
            'to' => $this->getAllRecipients(),
            'track_opens' => true,
            'track_clicks' => true,
            'tags' => $this->_tags,
            'merge_language' => $this->_mergeLanguage,
            'global_merge_vars' => $this->_globalMergeVars,
            'attachments' => $this->_attachments,
            'images' => $this->_images,
        ];
    }

    /**
     * Stores email addresses in a private variable.
     *
     * @param string|array $emailAddresses
     * @param string $container
     */
    private function storeEmailAddressesInContainer($emailAddresses, $container)
    {
        if (is_string($emailAddresses) && $this->isRecipientValid($emailAddresses, $container)) {
            $this->{$container}[] = $emailAddresses;
        }

        if (is_array($emailAddresses)) {
            foreach ($emailAddresses as $key => $value) {
                $this->storeArrayEmailAddressInContainer($key, $value, $container);
            }
        }
    }

    /**
     * Stores the email address coming from an array, correctly placing the
     * recipient name if it exists.
     *
     * @param string|integer $key
     * @param string $value
     * @param string $container
     */
    private function storeArrayEmailAddressInContainer($key, $value, $container)
    {
        $name = is_string($key) ? $value : null;
        $singleAddress = is_string($key) ? $key : $value;
        if ($this->isRecipientValid($singleAddress, $container)) {
            if ($name) {
                $this->{$container}[$singleAddress] = $name;
            } else {
                $this->{$container}[] = $singleAddress;
            }
        }
    }

    /**
     * Checks if an email address is valid and that is not already present within
     * the private attribute.
     *
     * @param string $emailAddress
     * @param string $privateAttributeName
     * @return boolean
     */
    private function isRecipientValid($emailAddress, $privateAttributeName)
    {
        if (!filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        if (array_search($emailAddress, $this->{$privateAttributeName}) !== false) {
            return false;
        }

        if (array_key_exists($emailAddress, $this->{$privateAttributeName}) !== false) {
            return false;
        }

        return true;
    }

    /**
     * Checks that the tag is not already in the private attribute, that is not
     * exceeding the 50 characters limit and that is not starting with an underscore.
     *
     * @param string $string
     * @param string $privateAttributeName
     * @return boolean
     */
    private function isTagValid($string, $privateAttributeName)
    {
        if (array_search($string, $this->{$privateAttributeName}) !== false) {
            return false;
        }

        if (strlen($string) > 50) {
            return false;
        }

        if ($string[0] === '_') {
            return false;
        }

        return true;
    }

    /**
     * Returns the Mime Type from the file binary.
     *
     * @param string $binary
     * @return string
     */
    private function getMimeTypeFromBinary($binary)
    {
        if ($this->_finfo === null) {
            $this->_finfo = new \finfo(FILEINFO_MIME_TYPE);
        }

        return $this->_finfo->buffer($binary);
    }

    /**
     * Gets the string rappresentation of Reply-To to be later used in the
     * email header.
     *
     * @return string
     */
    private function getReplyToString()
    {
        $addresses = [];
        foreach ($this->_replyTo as $key => $value) {
            if (is_string($key)) {
                $addresses[] = $value . ' <' . $key . '>';
            } else {
                $addresses[] = $value;
            }
        }

        return implode(';', $addresses);
    }

    /**
     * Returns the from name default value if no one was set by the user.
     *
     * @return string
     */
    private function getFromName()
    {
        if ($this->_) {
            return $this->_fromName ? $this->_fromName : null;
        }

        return $this->_fromName ? $this->_fromName : \Yii::$app->name;
    }

    /**
     * Returns the from address default value if no one was set by the user.
     *
     * @return string
     */
    private function getFromAddress()
    {
        if ($this->_) {
            return $this->_fromAddress ? $this->_fromAddress : null;
        }

        return $this->_fromAddress ? $this->_fromAddress : \Yii::$app->params['adminEmail'];
    }

    /**
     * Returns all the recipients in the format used by Mandrill.
     *
     * @return array
     */
    private function getAllRecipients()
    {
        $recipients = [];
        foreach ($this->_to as $key => $value) {
            $recipients[] = $this->getRecipientEntry($key, $value, 'to');
        }

        foreach ($this->_cc as $key => $value) {
            $recipients[] = $this->getRecipientEntry($key, $value, 'cc');
        }

        foreach ($this->_bcc as $key => $value) {
            $recipients[] = $this->getRecipientEntry($key, $value, 'bcc');
        }

        return $recipients;
    }

    /**
     * Generates and returns the single recipient array following Mandrill
     * API's specs.
     *
     * @param string $key
     * @param string $value
     * @param string $type
     * @return array
     */
    private function getRecipientEntry($key, $value, $type)
    {
        return [
            'email' => is_string($key) ? $key : $value,
            'name' => is_string($key) ? $value : null,
            'type' => $type,
        ];
    }

    /**
     * Converts the parameters in the format used by Mandrill to render templates.
     *
     * @param array $params
     * @return array
     * @since 1.3.0
     */
    private function convertParamsForTemplate($params)
    {
        $merge = [];
        foreach ($params as $key => $value) {
            $merge[] = ['name' => $key, 'content' => $value];
        }
        return $merge;
    }
}
