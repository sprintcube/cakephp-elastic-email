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
     * @return mixed
     */
    public function send(Email $email)
    {
        if (empty($this->getConfig('apiKey'))) {
            throw new MissingElasticEmailApiKeyException(['Api Key']);
        }

        $this->_reset();
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

        return $this->_sendEmail();
    }

    /**
     * Marks email as either transactional or marketing type.
     *
     * @param bool $value Either true or false
     * @return void
     */
    public function isTransactional($value = true)
    {
        debug('called');
        debug($this->_emailParams);
        if ($value) {
            $this->_emailParams['isTransactional'] = true;
        } else {
            $this->_emailParams['isTransactional'] = false;
        }
    }

    /**
     * Sets merge variables
     *
     * These variables are used to merge data with template.
     *
     * @return bool
     */
    public function setMergeVariables($mergeVars = [])
    {
        if (empty($mergeVars)) {
            return false;
        }

        foreach ($mergeVars as $field => $value) {
            $this->_emailParams['merge_' . $field] = $value;
        }

        return true;
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
        if (is_array($from)) {
            $this->_emailParams['from'] = key($from);
            $this->_emailParams['fromName'] = $from[key($from)];
        } else {
            $this->_emailParams['from'] = $from;
        }

        $this->_emailParams['to'] = '';
        $to = $email->getTo();
        foreach ($to as $toEmail) {
            $this->_emailParams['to'] .= $toEmail . ';';
        }

        $sender = $email->getSender();
        if (!empty($sender)) {
            if (is_array($sender)) {
                $this->_emailParams['sender'] = key($sender);
                $this->_emailParams['senderName'] = $sender[key($sender)];
            } else {
                $this->_emailParams['sender'] = $sender;
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
