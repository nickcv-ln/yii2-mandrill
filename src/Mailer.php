<?php
/**
 * Contains the Mailer class.
 *
 * @link http://www.creationgears.com/
 * @copyright Copyright (c) 2014 Nicola Puddu
 * @license http://www.gnu.org/copyleft/gpl.html
 * @package nickcv/yii2-mandrill
 * @author Nicola Puddu <n.puddu@outlook.com>
 */

namespace nickcv\mandrill;

use GuzzleHttp\Exception\RequestException;
use MailchimpTransactional\ApiClient;
use MailchimpTransactional\Configuration;
use Yii;
use yii\base\InvalidConfigException;
use yii\mail\BaseMailer;

/**
 * Mailer is the class that consuming the Message object sends emails thorugh
 * the Mandrill API.
 *
 * @author Nicola Puddu <n.puddu@outlook.com>
 * @version 1.0
 */
class Mailer extends BaseMailer
{

    const STATUS_SENT = 'sent';
    const STATUS_QUEUED = 'queued';
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_REJECTED = 'rejected';
    const STATUS_INVALID = 'invalid';
    const LOG_CATEGORY = 'mandrill';

    const LANGUAGE_MAILCHIMP = 'mailchimp';
    const LANGUAGE_HANDLEBARS = 'handlebars';

    /**
     * @var string Mandrill API key
     */
    private $_apikey;

    /**
     * Whether the mailer should use mandrill templates instead of Yii views.
     *
     * @var boolean use mandrill templates instead of Yii views.
     * @since 1.2.0
     */
    public $useMandrillTemplates = false;

    /**
     * Whether the mailer should use the template defaults when using mandrill
     * templates.
     *
     * @var boolean use mandrill template defaults.
     * @since 1.4.0
     */
    public $useTemplateDefaults = true;

    /**
     * What language is used in mandrill templates, either mailchimp or handlebars
     * Mailchimp language allows to use mc:edit and *|VAR|*
     * Handlebars language allows to use {{ var }}, loops, conditions @link http://handlebarsjs.com/
     *
     * @var string language, that is used in templates
     * @since 1.5.0
     */
    public $templateLanguage = self::LANGUAGE_MAILCHIMP;

    /**
     * @var string message default class name.
     */
    public $messageClass = '\nickcv\mandrill\Message';

    /**
     * @var ApiClient the Mailchimp API instance
     * @since 2.0.0
     */
    private $_mailchimp;

    /**
     * @var array|RequestException|string last response from mandrill
     * @since 1.7.0
     */
    private $_mandrillResponse = [];

    /**
     * Checks that the API key has indeed been set.
     *
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function init()
    {
        if (!$this->_apikey) {
            throw new InvalidConfigException('"' . get_class($this) . '::apikey" cannot be null.');
        }

        if (!in_array($this->templateLanguage, [self::LANGUAGE_MAILCHIMP, self::LANGUAGE_HANDLEBARS])) {
            throw new InvalidConfigException('"' . get_class($this) . '::templateLanguage" has an invalid value.');
        }

        try {
            $this->_mailchimp = new ApiClient();
            $this->_mailchimp->setApiKey($this->_apikey);
            $this->_mailchimp->setDefaultOutputFormat('php');
        } catch (\Exception $exc) {
            Yii::error($exc->getMessage());
            throw new \Exception('an error occurred with your mailer. Please check the application logs.', 500);
        }
    }

    /**
     * Sets the API key for Mandrill
     *
     * @param string $apikey the Mandrill API key
     *
     * @throws InvalidConfigException
     */
    public function setApikey(string $apikey)
    {
        if (!is_string($apikey)) {
            throw new InvalidConfigException('"' . get_class($this) . '::apikey" should be a string, "' . gettype($apikey) . '" given.');
        }

        $trimmedApikey = trim($apikey);
        if (!strlen($trimmedApikey) > 0) {
            throw new InvalidConfigException('"' . get_class($this) . '::apikey" length should be greater than 0.');
        }

        $this->_apikey = $trimmedApikey;
    }

    /**
     * Gets Mandrill instance
     *
     * @return ApiClient initialized Mailchimp
     * @since 1.6.0
     * @deprecated
     */
    public function getMandrill(): ApiClient
    {
        return $this->_mailchimp;
    }

    /**
     * Gets Mailchimp instance
     *
     * @return ApiClient initialized Mailchimp API client
     * @since 2.0.0
     */
    public function getMailchimp(): ApiClient
    {
        return $this->_mailchimp;
    }

    /**
     * Returns the array of the last transaction returned by Mandrill.
     *
     * @return array
     * @since 1.7.0
     */
    public function getLastTransaction()
    {
        return $this->_mandrillResponse;
    }

    /**
     * Composes the message using a Mandrill template if the useMandrillTemplates
     * settings is true.
     *
     * If mandrill templates are not being used or if no template with the given
     * name has been found it will fallback to the normal compose method.
     *
     * {@inheritdoc}
     *
     * @since 1.2.0
     */
    public function compose($view = null, array $params = [])
    {
        if ($this->useMandrillTemplates) {
            /** @var Message $message */
            $message = parent::compose();
            $message->setTemplateData($view, $params, $this->templateLanguage);
            if ($this->useTemplateDefaults) {
                $message->enableTemplateDefaults();
            }

            return $message;
        }

        return parent::compose($view, $params);
    }

    /**
     * Sends the specified message.
     *
     * @param Message $message the message to be sent
     *
     * @return boolean whether the message is sent successfully
     */
    protected function sendMessage($message): bool
    {
        Yii::info(
            'Sending email "' . $message->getSubject() . '" to "' . implode(', ', $message->getTo()) . '"',
            self::LOG_CATEGORY
        );

        if ($this->useMandrillTemplates) {
            return $this->wasMessageSentSuccessful(
                $this->_mailchimp->messages->sendTemplate([
                    'template_name' => $message->getTemplateName(),
                    'template_content' => $message->getTemplateContent(),
                    'message' => $message->getMandrillMessageArray(),
                    'async' => $message->isAsync()
                ])
            );
        } else {
            return $this->wasMessageSentSuccessful(
                $this->_mailchimp->messages->sendRaw([
                    'message' => $message->getMandrillMessageArray(),
                    'async' => $message->isAsync()
                ])
            );
        }
    }

    /**
     * parse the mandrill response and returns false if any message was either invalid or rejected
     *
     * @param array|RequestException|string $mandrillResponse
     *
     * @return boolean
     */
    private function wasMessageSentSuccessful($mandrillResponse): bool
    {
        $this->_mandrillResponse = $mandrillResponse;
        if (is_string($mandrillResponse) || $mandrillResponse instanceof RequestException) {
            if ($mandrillResponse instanceof RequestException) {
                /** @var RequestException $mandrillResponse */
                Yii::error(
                    'A mandrill error occurred: ' . Configuration::class . ' - ' . $mandrillResponse->getMessage(),
                    self::LOG_CATEGORY
                );
            } else {
                /** @var string $mandrillResponse */
                Yii::error(
                    'A mandrill error occurred: ' . Configuration::class . ' - ' . $mandrillResponse,
                    self::LOG_CATEGORY
                );
            }
            return false;
        }

        $return = true;
        foreach ($mandrillResponse as $recipient) {
            switch ($recipient['status']) {
                case self::STATUS_INVALID:
                    $return = false;
                    Yii::warning(
                        'the email for "' . $recipient['email'] . '" has not been sent: status "' . $recipient['status'] . '"',
                        self::LOG_CATEGORY
                    );
                    break;
                case self::STATUS_QUEUED:
                    Yii::info(
                        'the email for "' . $recipient['email'] . '" is now in a queue waiting to be sent.',
                        self::LOG_CATEGORY
                    );
                    break;
                case self::STATUS_REJECTED:
                    $return = false;
                    Yii::warning(
                        'the email for "' . $recipient['email'] . '" has been rejected: reason "' . $recipient['reject_reason'] . '"',
                        self::LOG_CATEGORY
                    );
                    break;
                case self::STATUS_SCHEDULED:
                    Yii::info(
                        'the email submission for "' . $recipient['email'] . '" has been scheduled.',
                        self::LOG_CATEGORY
                    );
                    break;
                case self::STATUS_SENT:
                    Yii::info(
                        'the email for "' . $recipient['email'] . '" has been sent.',
                        self::LOG_CATEGORY
                    );
                    break;
            }
        }

        return $return;
    }
}
