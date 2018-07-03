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
namespace ElasticEmail\Test\TestCase\Mailer\Transport;

use Cake\Mailer\Email;
use Cake\TestSuite\TestCase;

class ElasticEmailTransportTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testMissingApiKey()
    {
        $this->expectException('ElasticEmail\Mailer\Exception\MissingElasticEmailApiKeyException');

        Email::setConfigTransport(
            'elasticemail',
            [
                'className' => 'ElasticEmail.ElasticEmail',
                'apiKey' => ''
            ]
        );

        $email = new Email();
        $email->setProfile(['transport' => 'elasticemail']);
        $email->setFrom(['from@example.com' => 'CakePHP Elastic Email'])
            ->setTo('to@example.com')
            ->setEmailFormat('both')
            ->setSubject('{title} - Email from CakePHP Elastic Email plugin')
            ->send('Hello {firstname} {lastname}, <br> This is an email from CakePHP Elastic Email plugin.');
    }
}