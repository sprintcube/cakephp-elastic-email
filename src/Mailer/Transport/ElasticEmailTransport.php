<?php
/**
 * Elastic Email Plugin for CakePHP 3
 * Copyright (c) PNG Labz (https://www.pnglabz.com)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.md
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) PNG Labz (https://www.pnglabz.com)
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 * @link      https://github.com/pnglabz/cakephp-elastic-email
 * @since     1.0.0
 */
namespace ElasticEmail\Mailer\Transport;

use Cake\Http\Client;
use Cake\Mailer\AbstractTransport;
use Cake\Mailer\Email;
use Cake\Utility\Text;
use ElasticEmail\Mailer\Exception\MissingElasticEmailApiKeyException;

/**
 * Send mail using Elastic Email API
 */
class ElasticEmailTransport extends AbstractTransport
{
    /**
     * Default config for this class
     *
     * @var array
     */
    protected $_defaultConfig = [
        'apiKey' => ''
    ];

    /**
     * Defailt email parameters
     *
     * @var array
     */
    protected $_emailParams = [];

    /**
     * API Endpoint URL
     *
     * @var string
     */
    protected $_apiEndpoint = 'https://api.elasticemail.com/v2';

    /**
     * Prefix for setting custom headers
     *
     * @var string
     */
    protected $_customHeaderPrefix = 'X-';

    /**
     * Send mail
     *
     * @param \Cake\Mailer\Email $email Cake Email
     * @return array An array with api response and email parameters
     */
    public function send(Email $email)
    {
        if (empty($this->getConfig('apiKey'))) {
            throw new MissingElasticEmailApiKeyException(['Api Key']);
        }

        $this->_emailParams['apikey'] = $this->getConfig('apiKey');

        $this->_prepareEmailAddresses($email);

        $this->_emailParams['subject'] = $email->getSubject();

        $emailFormat = $email->getEmailFormat();
        $this->_emailParams['bodyHtml'] = trim($email->message(Email::MESSAGE_HTML));
        if ('both' == $emailFormat || 'text' == $emailFormat) {
            $this->_emailParams['bodyText'] = trim($email->message(Email::MESSAGE_TEXT));
        }

        $customHeaders = $email->getHeaders(['_headers']);
        if (!empty($customHeaders)) {
            foreach ($customHeaders as $header => $value) {
                if (0 === strpos($header, $this->_customHeaderPrefix) && !empty($value)) {
                    $this->_emailParams['headers_' . Text::slug(strtolower($header), '')] = $header . ': ' . $value;
                }
            }
        }

        $apiRsponse = $this->_sendEmail();
        $res = [
            'apiResponse' => $apiRsponse,
            'emailParams' => $this->_emailParams
        ];

        $this->_reset();

        return $res;
    }

    /**
     * Returns the email parameters for API request.
     *
     * @return array
     */
    public function getEmailParams()
    {
        return $this->_emailParams;
    }

    /**
     * Marks email as whether transactional or not.
     *
     * Example
     * ```
     * $email = new Email('elasticemail');
     * $emailInstance = $email->getTransport();
     * $emailInstance->isTransactional(true);
     * $email->send();
     * ```
     *
     * @param bool $value Either true or false
     * @return $this
     */
    public function isTransactional($value = true)
    {
        if ($value) {
            $this->_emailParams['isTransactional'] = true;
        } else {
            $this->_emailParams['isTransactional'] = false;
        }

        return $this;
    }

    /**
     * Sets merge variables
     *
     * These variables are used to merge data with template.
     *
     * Example
     * ```
     * $mergeVars = [
     *     'firstname' => 'Foo',
     *     'lastname' => 'Bar',
     *     'title' => 'Good Title'
     * ];
     *
     *  $email = new Email('elasticemail');
     *  $emailInstance = $email->getTransport();
     *  $emailInstance->setMergeVariables($mergeVars);
     *
     *  $email->setFrom(['from@example.com' => 'CakePHP Elastic Email'])
     *     ->setTo('to@example.com')
     *     ->setEmailFormat('both')
     *     ->setSubject('{title} - Email from CakePHP Elastic Email plugin')
     *     ->send('Hello {firstname} {lastname}, <br> This is an email from CakePHP Elastic Email plugin.');
     * ```
     *
     * @param array $mergeVars Array of template variables
     * @return $this
     */
    public function setMergeVariables($mergeVars = [])
    {
        if (!empty($mergeVars)) {
            foreach ($mergeVars as $field => $value) {
                $this->_emailParams['merge_' . $field] = $value;
            }
        }

        return $this;
    }

    /**
     * Sets template id
     *
     * This will set template to use in email. Template can be created
     * in Elastic Email dashboard.
     *
     * Example
     * ```
     *  $email = new Email('elasticemail');
     *  $emailInstance = $email->getTransport();
     *  $emailInstance->setTemplte(123);
     *
     *  $email->send();
     * ```
     *
     * @param array $id ID of template
     * @return $this
     */
    public function setTemplate($id = null)
    {
        if (is_numeric($id)) {
            $this->_emailParams['template'] = $id;
        }

        return $this;
    }

    /**
     * Sets number of minutes in the future this email should be sent
     *
     * Minutes can be up to 1 year i.e. 524160 minutes.
     *
     * Example
     * ```
     *  $email = new Email('elasticemail');
     *  $emailInstance = $email->getTransport();
     *  $emailInstance->setScheduleTime(60); // after 1 hour from sending time
     *
     *  $email->send();
     * ```
     *
     * @param array $minutes Number of minutes
     * @return $this
     */
    public function setScheduleTime($minutes = null)
    {
        if (is_numeric($minutes)) {
            $this->_emailParams['timeOffSetMinutes'] = $minutes;
        }

        return $this;
    }

    /**
     * Prepares the from, to and sender email addresses
     *
     * @param \Cake\Mailer\Email $email Cake Email instance
     * @return void
     */
    protected function _prepareEmailAddresses(Email $email)
    {
        $from = $email->getFrom();
        if (key($from) != $from[key($from)]) {
            $this->_emailParams['from'] = key($from);
            $this->_emailParams['fromName'] = $from[key($from)];
        } else {
            $this->_emailParams['from'] = key($from);
        }

        $this->_emailParams['to'] = '';
        $to = $email->getTo();
        foreach ($to as $toEmail) {
            $this->_emailParams['to'] .= $toEmail . ';';
        }

        $sender = $email->getSender();
        if (!empty($sender)) {
            if (key($sender) != $sender[key($sender)]) {
                $this->_emailParams['sender'] = key($sender);
                $this->_emailParams['senderName'] = $sender[key($sender)];
            } else {
                $this->_emailParams['sender'] = key($sender);
            }
        }
    }

    /**
     * Make an API request to send email
     *
     * @return mixed JSON Response from Elastic Email API
     */
    protected function _sendEmail()
    {
        $http = new Client();
        $response = $http->post("{$this->_apiEndpoint}/email/send", $this->_emailParams);

        return $response->json;
    }

    /**
     * Resets the parameters
     *
     * @return void
     */
    protected function _reset()
    {
        $this->_emailParams = [];
    }
}
