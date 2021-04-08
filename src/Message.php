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

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\HtmlPurifier;
use yii\mail\BaseMessage;

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
    private $_templateContent = [];

    /**
     * Value used to decide whether the message should calculate default values
     * for the sender based on the application settings or return nulls to use
     * mandrill template defaults.
     *
     * @var boolean
     * @since 1.4.0
     */
    private $_calculateDefaultValues = false;

    /**
     * Global merge vars used when sending the message to mandrill.
     *
     * @var array
     * @since 1.4.0
     */
    private $_globalMergeVars = [];


    /**
     * Merge vars used when sending the message to mandrill.
     *
     * @var array
     */
    private $_mergeVars = [];

    /**
     * Global meta data used when sending the message to mandrill.
     *
     * @var array
     */
    private $_metadata = [];

    /**
     * Per client meta data used when sending the message to mandrill.
     *
     * @var array
     */
    private $_recipientMetadata = [];

    /**
     * @var array an array of strings indicating for which any matching URLs will automatically have Google Analytics
     * parameters appended to their query string automatically
     */
    private $_googleAnalyticsDomains = [];

    /**
     * @var string indicating the value to set for the utm_campaign tracking parameter (optional)
     */
    private $_googleAnalyticsCampaign;

    /**
     * What language will be used in the template
     * Check @link http://handlebarsjs.com/ for more documentation about handlebars language
     *
     * @var string
     * @since 1.5.0
     */
    private $_mergeLanguage = self::LANGUAGE_MAILCHIMP;

    /**
     * Subaccount to use for Mandrill
     *
     * @var null|string
     * @since 1.7.0
     */
    private $_subaccount = null;

    /**
     * Give this email more priority in mandrill's queue
     *
     * @var boolean
     * @since 1.7.0
     */
    private $_important = false;

    /**
     * @var boolean
     * @since 1.7.0
     */
    private $_trackOpens = true;

    /**
     * @var boolean
     * @since 1.7.0
     */
    private $_trackClicks = true;

    /**
     * Mandrill does not let users set a charset.
     *
     * @return null
     * @see \nickcv\mandrill\Message::setCharset() setter
     *
     */
    public function getCharset()
    {
        return null;
    }

    /**
     * Mandrill does not let users set a charset.
     *
     * @param string $charset character set name.
     *
     * @return static
     * @see \nickcv\mandrill\Message::getCharset() getter
     *
     */
    public function setCharset($charset): Message
    {
        return $this;
    }

    /**
     * Returns the list of tags you already set for this message.
     *
     * @return array the list of tags
     * @see \nickcv\mandrill\Message::setTags() setter
     *
     */
    public function getTags(): array
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
     * @param string|array $tag tag or list of tags
     *
     * @return static
     * @see \nickcv\mandrill\Message::getTags() getter
     *
     */
    public function setTags($tag): Message
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
    public function isAsync(): bool
    {
        return $this->_async;
    }

    /**
     * Enables async sending for this message.
     *
     * @return static
     * @since 1.3.0
     */
    public function enableAsync(): Message
    {
        $this->_async = true;

        return $this;
    }

    /**
     * Disables async sending the this message.
     *
     * @return static
     * @since 1.3.0
     */
    public function disableAsync(): Message
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
     * @return string|null
     * @see \nickcv\mandrill\Message::setFrom() setter
     *
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
     * @param string|array $from sender email address.
     * You may specify sender name in addition to email address using format:
     * `[email => name]`.
     * If you don't set this parameter the application adminEmail parameter will
     * be used as the sender email address and the application name will be used
     * as the sender name.
     *
     * @return static
     * @see \nickcv\mandrill\Message::getFrom() getter
     *
     */
    public function setFrom($from): Message
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
     * @return array|null
     * @see \nickcv\mandrill\Message::setTo() setter
     *
     */
    public function getTo()
    {
        return $this->_to;
    }

    /**
     * Sets the message recipient(s).
     *
     * @param string|array $to receiver email address.
     * You may pass an array of addresses if multiple recipients should receive this message.
     * You may also specify receiver name in addition to email address using format:
     * `[email => name]`.
     *
     * @return static
     * @see \nickcv\mandrill\Message::getTo() getter
     *
     */
    public function setTo($to): Message
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
     * @return array|null
     * @see \nickcv\mandrill\Message::setReplyTo() setter
     *
     */
    public function getReplyTo()
    {
        return $this->_replyTo;
    }

    /**
     * Sets the message recipient(s).
     *
     * @param string|array $replyTo Reply-To email address.
     * You may pass an array of addresses if multiple recipients should receive this message.
     * You may also specify receiver name in addition to email address using format:
     * `[email => name]`.
     *
     * @return static
     * @see \nickcv\mandrill\Message::getReplyTo() getter
     *
     */
    public function setReplyTo($replyTo): Message
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
     * @return array|null
     * @see \nickcv\mandrill\Message::setCc() setter
     *
     */
    public function getCc()
    {
        return $this->_cc;
    }

    /**
     * Sets the message recipient(s).
     *
     * @param string|array $cc cc email address.
     * You may pass an array of addresses if multiple recipients should receive this message.
     * You may also specify receiver name in addition to email address using format:
     * `[email => name]`.
     *
     * @return static
     * @see \nickcv\mandrill\Message::getCc() getter
     *
     */
    public function setCc($cc): Message
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
     * @return array|null
     * @see \nickcv\mandrill\Message::setBcc() setter
     *
     */
    public function getBcc()
    {
        return $this->_bcc;
    }

    /**
     * Sets the message recipient(s).
     *
     * @param string|array $bcc bcc email address.
     * You may pass an array of addresses if multiple recipients should receive this message.
     * You may also specify receiver name in addition to email address using format:
     * `[email => name]`.
     *
     * @return static
     * @see \nickcv\mandrill\Message::getBcc() getter
     *
     */
    public function setBcc($bcc): Message
    {
        $this->storeEmailAddressesInContainer($bcc, '_bcc');

        return $this;
    }

    /**
     * Returns the html-encoded subject.
     *
     * @return string|null
     * @see \nickcv\mandrill\Message::setSubject() setter
     *
     */
    public function getSubject()
    {
        return $this->_subject;
    }

    /**
     * Sets the message subject.
     *
     * @param string $subject
     * The subject will be trimmed.
     *
     * @return static
     * @see \nickcv\mandrill\Message::getSubject() getter
     *
     */
    public function setSubject($subject): Message
    {
        if (is_string($subject)) {
            $this->_subject = trim($subject);
        }

        return $this;
    }

    /**
     * Returns the html-purified version of the raw text body.
     *
     * @return string|null
     * @see \nickcv\mandrill\Message::setTextBody() setter
     *
     */
    public function getTextBody()
    {
        return $this->_text;
    }

    /**
     * Sets the raw text body.
     *
     * @param string $text
     * The text will be purified.
     *
     * @return static
     * @see \nickcv\mandrill\Message::getTextBody() getter
     *
     */
    public function setTextBody($text): Message
    {
        if (is_string($text)) {
            $this->_text = HtmlPurifier::process($text);
        }

        return $this;
    }

    /**
     * Returns the html purified version of the html body.
     *
     * @return string|null
     * @see \nickcv\mandrill\Message::setHtmlBody() setter
     *
     */
    public function getHtmlBody()
    {
        return $this->_html;
    }

    /**
     * Sets the html body.
     *
     * @param string $html
     *
     * @return static
     * @see \nickcv\mandrill\Message::getHtmlBody() getter
     *
     */
    public function setHtmlBody($html): Message
    {
        if (is_string($html)) {
            $this->_html = $html;
        }

        return $this;
    }

    /**
     * Returns the attachments array.
     *
     * @return array
     * @see \nickcv\mandrill\Message::attachContent() setter for binary
     *
     * @see \nickcv\mandrill\Message::attach() setter for file name
     */
    public function getAttachments(): array
    {
        return $this->_attachments;
    }

    /**
     * Attaches existing file to the email message.
     *
     * @param string $fileName full file name
     * @param array $options options for embed file. Valid options are:
     *
     * - fileName: name, which should be used to attach file.
     * - contentType: attached file MIME type.
     *
     * @return static
     * @throws \yii\base\InvalidConfigException
     * @see \nickcv\mandrill\Message::getAttachments() getter
     *
     */
    public function attach($fileName, array $options = []): Message
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
     * @param string $content attachment file content.
     * @param array $options options for embed file. Valid options are:
     *
     * - fileName: name, which should be used to attach file.
     * - contentType: attached file MIME type.
     *
     * @return static
     * @see \nickcv\mandrill\Message::getAttachments() getter
     *
     */
    public function attachContent($content, array $options = []): Message
    {
        $purifiedOptions = is_array($options) ? $options : [];

        if (is_string($content) && strlen($content) !== 0) {
            $this->_attachments[] = [
                'name' => ArrayHelper::getValue($purifiedOptions, 'fileName', ('file_' . count($this->_attachments))),
                'type' => ArrayHelper::getValue($purifiedOptions, 'contentType',
                    $this->getMimeTypeFromBinary($content)),
                'content' => base64_encode($content),
            ];
        }

        return $this;
    }

    /**
     * Returns the images array.
     *
     * @return array list of embedded content
     * @see \nickcv\mandrill\Message::embedContent() setter for binary
     *
     * @see \nickcv\mandrill\Message::embed() setter for file name
     */
    public function getEmbeddedContent(): array
    {
        return $this->_images;
    }

    /**
     * Embeds an image in the email message.
     *
     * @param string $fileName file name.
     * @param array $options options for embed file. Valid options are:
     *
     * - fileName: name, which should be used to attach file.
     * - contentType: attached file MIME type.
     *
     * @return static
     * @throws \yii\base\InvalidConfigException
     * @see \nickcv\mandrill\Message::getEmbeddedContent() getter
     *
     */
    public function embed($fileName, array $options = []): Message
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
     * @param string $content attachment file content.
     * @param array $options options for embed file. Valid options are:
     *
     * - fileName: name, which should be used to attach file.
     * - contentType: attached file MIME type.
     *
     * @return static
     * @see \nickcv\mandrill\Message::getEmbeddedContent() getter
     *
     */
    public function embedContent($content, array $options = []): Message
    {
        $purifiedOptions = is_array($options) ? $options : [];

        if (is_string($content) && strlen($content) !== 0 && strpos($this->getMimeTypeFromBinary($content),
                'image') === 0) {
            $this->_images[] = [
                'name' => ArrayHelper::getValue($purifiedOptions, 'fileName', ('file_' . count($this->_images))),
                'type' => ArrayHelper::getValue(
                    $purifiedOptions,
                    'contentType',
                    $this->getMimeTypeFromBinary($content)
                ),
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
     * @return static
     * @since 1.3.0
     */
    public function setTemplateData(
        string $templateName,
        array $templateContent = [],
        string $templateLanguage = self::LANGUAGE_MAILCHIMP
    ): Message {
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
     * @return string|null
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
    public function getTemplateContent(): array
    {
        return $this->_templateContent;
    }

    /**
     * Enable the use of template defaults.
     *
     * @return static
     * @since 1.4.0
     */
    public function enableTemplateDefaults(): Message
    {
        $this->_calculateDefaultValues = true;

        return $this;
    }

    /**
     * Disable the use of template defaults.
     *
     * @return static
     * @since 1.4.0
     */
    public function disableTemplateDefaults(): Message
    {
        $this->_calculateDefaultValues = false;

        return $this;
    }

    /**
     * @param string $subaccount
     *
     * @return static
     * @since 1.7.0
     */
    public function setSubaccount(string $subaccount): Message
    {
        $this->_subaccount = $subaccount;

        return $this;
    }

    /**
     * @return string|null
     * @since 1.7.0
     */
    public function getSubaccount()
    {
        return $this->_subaccount;
    }

    /**
     * Make the message important.
     *
     * @return static
     * @since 1.7.0
     */
    public function setAsImportant(): Message
    {
        $this->_important = true;

        return $this;
    }

    /**
     * Make the message not important.
     * The message is not important by default.
     *
     * @return static
     * @since 1.7.0
     */
    public function setAsNotImportant(): Message
    {
        $this->_important = false;

        return $this;
    }

    /**
     * @return boolean
     * @since 1.7.0
     */
    public function isImportant(): bool
    {
        return $this->_important;
    }

    /**
     * Enable tracking of when the message is opened.
     * Tracking is enabled by default.
     *
     * @return static
     * @since 1.7.0
     */
    public function enableOpensTracking(): Message
    {
        $this->_trackOpens = true;

        return $this;
    }

    /**
     * Disable tracking of when the message is opened.
     *
     * @return static
     * @since 1.7.0
     */
    public function disableOpensTracking(): Message
    {
        $this->_trackOpens = false;

        return $this;
    }

    /**
     * Returns the status of tracking for when the message is opened.
     *
     * @return boolean
     * @since 1.7.0
     */
    public function areOpensTracked(): bool
    {
        return $this->_trackOpens;
    }

    /**
     * Enable tracking of when links in the message are being clicked.
     * Tracking is enabled by default.
     *
     * @return static
     * @since 1.7.0
     */
    public function enableClicksTracking(): Message
    {
        $this->_trackClicks = true;

        return $this;
    }

    /**
     * Disable tracking of when links in the message are being clicked.
     *
     * @return static
     * @since 1.7.0
     */
    public function disableClicksTracking(): Message
    {
        $this->_trackClicks = false;

        return $this;
    }

    /**
     * Returns the status of tracking for when the links in the message are clicked.
     *
     * @return boolean
     * @since 1.7.0
     */
    public function areClicksTracked(): bool
    {
        return $this->_trackClicks;
    }

    /**
     * Returns the global merge vars that will be submitted to mandrill.
     *
     * @return array
     * @since 1.4.0
     */
    public function getGlobalMergeVars(): array
    {
        return $this->_globalMergeVars;
    }

    /**
     * Returns the merge vars that will be submitted to mandrill.
     *
     * @return array
     */
    public function getMergeVars(): array
    {
        return $this->_mergeVars;
    }

    /**
     * Adds the given merge vars to the global merge vars array.
     * Merge vars are case insensitive and cannot start with _
     *
     * @param array $mergeVars
     *
     * @return static
     * @since 1.4.0
     */
    public function setGlobalMergeVars(array $mergeVars): Message
    {
        foreach ($mergeVars as $name => $content) {
            if ($name[0] === '_') {
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
     * Adds the given merge vars to the merge vars array.
     *
     * @param array $mergeVars with format :
     * [
     *    'rcpt' => 'string email address of the recipient that the merge variables should apply to',
     *    'vars' => [
     *                 'name'    => 'NAMEOFVARIABLE_IN_MANDRIL',
     *                 'content' => 'VALUEOFVARIABLE_IN_MANDRIL_TEMPLATE',
     *    ]
     * ], ...
     *
     * @return static
     */
    public function setMergeVars(array $mergeVars): Message
    {
        $this->_mergeVars = $mergeVars;

        return $this;
    }

    /**
     * Returns the global meta data that will be submitted to mandrill.
     *
     * @return array
     */
    public function getMetadata(): array
    {
        return $this->_metadata;
    }

    /**
     * Returns the per recipient meta data that will be submitted to mandrill.
     *
     * @return array
     */
    public function getRecipientMetadata(): array
    {
        return $this->_recipientMetadata;
    }

    /**
     * Adds the given meta data to the global meta data array
     *
     * @param array $metadata
     *
     * Example
     * ```php
     * [
     *      'group_id' => 'users_active'
     * ]
     * ```
     *
     * @return static
     */
    public function setMetadata(array $metadata): Message
    {
        $this->_metadata = $metadata;

        return $this;
    }

    /**
     * Adds the given meta data to the per recipient meta data array
     *
     * @param array $recipientMetadata
     *
     * Example
     * ```php
     * [
     *      [
     *          'rcpt' => 'foo@example.com',
     *          'values' => [
     *              'user_id' => '123'
     *          ]
     *      ],
     *      [
     *          'rcpt' => 'bar@example.com',
     *          'values' => [
     *              'user_id' => '456'
     *          ]
     *      ]
     * ]
     * ```
     *
     * @return static
     */
    public function setRecipientMetadata(array $recipientMetadata): Message
    {
        $this->_recipientMetadata = $recipientMetadata;

        return $this;
    }

    /**
     * Returns the Google Analytics domains that will be submitted to mandrill.
     *
     * @return array
     */
    public function getGoogleAnalyticsDomains(): array
    {
        return $this->_googleAnalyticsDomains;
    }

    /**
     * Returns the Google Analytics campaign that will be submitted to mandrill.
     *
     * @return string
     */
    public function getGoogleAnalyticsCampaign(): string
    {
        return $this->_googleAnalyticsCampaign;
    }

    /**
     * Sets the Google Analytics domains that will be submitted to mandrill.
     *
     * @param array $domains
     *
     * @return static
     */
    public function setGoogleAnalyticsDomains(array $domains): Message
    {
        $this->_googleAnalyticsDomains = $domains;

        return $this;
    }

    /**
     * Sets the Google Analytics campaign that will be submitted to mandrill.
     *
     * @param string $campaign
     *
     * @return static
     */
    public function setGoogleAnalyticsCampaign($campaign): Message
    {
        $this->_googleAnalyticsCampaign = $campaign;

        return $this;
    }

    /**
     * Returns the string representation of this message.
     *
     * @return string
     */
    public function toString(): string
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
    public function getMandrillMessageArray(): array
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
            'track_opens' => $this->_trackOpens,
            'track_clicks' => $this->_trackClicks,
            'tags' => $this->_tags,
            'merge_language' => $this->_mergeLanguage,
            'global_merge_vars' => $this->_globalMergeVars,
            'merge_vars' => $this->_mergeVars,
            'metadata' => $this->_metadata,
            'recipient_metadata' => $this->_recipientMetadata,
            'google_analytics_domains' => $this->_googleAnalyticsDomains,
            'google_analytics_campaign' => $this->_googleAnalyticsCampaign,
            'attachments' => $this->_attachments,
            'images' => $this->_images,
            'subaccount' => $this->_subaccount,
            'important' => $this->_important,
        ];
    }

    /**
     * Stores email addresses in a private variable.
     *
     * @param string|array $emailAddresses
     * @param string $container
     */
    private function storeEmailAddressesInContainer($emailAddresses, string $container)
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
    private function storeArrayEmailAddressInContainer($key, string $value, string $container)
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
     *
     * @return boolean
     */
    private function isRecipientValid(string $emailAddress, string $privateAttributeName): bool
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
     *
     * @return boolean
     */
    private function isTagValid(string $string, string $privateAttributeName): bool
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
     *
     * @return string
     */
    private function getMimeTypeFromBinary(string $binary): string
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
    private function getReplyToString(): string
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
     * @return string|null
     */
    private function getFromName()
    {
        if ($this->_calculateDefaultValues) {
            return $this->_fromName ? $this->_fromName : null;
        }

        return $this->_fromName ? $this->_fromName : Yii::$app->name;
    }

    /**
     * Returns the from address default value if no one was set by the user.
     *
     * @return string|null
     */
    private function getFromAddress()
    {
        if ($this->_calculateDefaultValues) {
            return $this->_fromAddress ? $this->_fromAddress : null;
        }

        return $this->_fromAddress ? $this->_fromAddress : Yii::$app->params['adminEmail'];
    }

    /**
     * Returns all the recipients in the format used by Mandrill.
     *
     * @return array
     */
    private function getAllRecipients(): array
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
     * @param string|integer $key
     * @param string $value
     * @param string $type
     *
     * @return array
     */
    private function getRecipientEntry($key, string $value, string $type): array
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
     *
     * @return array
     * @since 1.3.0
     */
    private function convertParamsForTemplate(array $params): array
    {
        $merge = [];
        foreach ($params as $key => $value) {
            $merge[] = ['name' => $key, 'content' => $value];
        }

        return $merge;
    }
}
