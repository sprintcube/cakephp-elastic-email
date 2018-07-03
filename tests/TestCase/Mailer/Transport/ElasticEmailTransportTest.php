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

    public function testTransactional()
    {
        Email::dropTransport('elasticemail');
        Email::setConfigTransport(
            'elasticemail',
            [
                'className' => 'ElasticEmail.ElasticEmail',
                'apiKey' => '123'
            ]
        );

        $email = new Email();
        $email->setProfile(['transport' => 'elasticemail']);
        $emailInstance = $email->getTransport();
        $emailInstance->isTransactional(true);
        $emailParams = $emailInstance->getEmailParams();
        $this->assertArrayHasKey('isTransactional', $emailParams);
        $this->assertTrue($emailParams['isTransactional']);

        $secondemail = new Email();
        $secondemail->setProfile(['transport' => 'elasticemail']);
        $secondemailInstance = $secondemail->getTransport();
        $secondemailInstance->isTransactional(false);
        $secondemailParams = $secondemailInstance->getEmailParams();
        $this->assertArrayHasKey('isTransactional', $secondemailParams);
        $this->assertFalse($secondemailParams['isTransactional']);

        $thirdemail = new Email();
        $thirdemail->setProfile(['transport' => 'elasticemail']);
        $thirdemailInstance = $thirdemail->getTransport();

        $thirdemailParams = $thirdemailInstance->getEmailParams();
        $this->assertArrayNotHasKey('isTransactional', $thirdemailParams);
    }

    public function testTemplate()
    {
        Email::dropTransport('elasticemail');
        Email::setConfigTransport(
            'elasticemail',
            [
                'className' => 'ElasticEmail.ElasticEmail',
                'apiKey' => '123'
            ]
        );

        $email = new Email();
        $email->setProfile(['transport' => 'elasticemail']);

        $emailInstance = $email->getTransport();
        $emailInstance->setTemplate(111);

        $emailParams = $emailInstance->getEmailParams();
        $this->assertArrayHasKey('template', $emailParams);
        $this->assertEquals(111, $emailParams['template']);
    }

    public function testTemplateVars()
    {
        Email::dropTransport('elasticemail');
        Email::setConfigTransport(
            'elasticemail',
            [
                'className' => 'ElasticEmail.ElasticEmail',
                'apiKey' => '123'
            ]
        );

        $mergeVars = [
            'foo' => 'bar'
        ];

        $email = new Email();
        $email->setProfile(['transport' => 'elasticemail']);

        $emailInstance = $email->getTransport();
        $emailInstance->setMergeVariables($mergeVars);

        $emailParams = $emailInstance->getEmailParams();
        $this->assertArrayHasKey('merge_foo', $emailParams);
        $this->assertEquals('bar', $emailParams['merge_foo']);
    }

    public function testSchedule()
    {
        Email::dropTransport('elasticemail');
        Email::setConfigTransport(
            'elasticemail',
            [
                'className' => 'ElasticEmail.ElasticEmail',
                'apiKey' => '123'
            ]
        );

        $email = new Email();
        $email->setProfile(['transport' => 'elasticemail']);

        $emailInstance = $email->getTransport();
        $emailInstance->setScheduleTime(60);

        $emailParams = $emailInstance->getEmailParams();
        $this->assertArrayHasKey('timeOffSetMinutes', $emailParams);
        $this->assertEquals(60, $emailParams['timeOffSetMinutes']);
    }

    public function testAddresses()
    {
        Email::dropTransport('elasticemail');
        Email::setConfigTransport(
            'elasticemail',
            [
                'className' => 'ElasticEmail.ElasticEmail',
                'apiKey' => '123'
            ]
        );

        $email = new Email();
        $email->setProfile(['transport' => 'elasticemail']);
        $res = $email->setFrom('from@example.com')
            ->setSender('sender@example.com')
            ->setTo('to@example.com')
            ->send();

        $emailParams = $res['emailParams'];
        $this->assertArrayHasKey('from', $emailParams);
        $this->assertArrayNotHasKey('fromName', $emailParams);
        $this->assertEquals('from@example.com', $emailParams['from']);
        $this->assertArrayHasKey('sender', $emailParams);
        $this->assertArrayNotHasKey('senderName', $emailParams);
        $this->assertEquals('sender@example.com', $emailParams['sender']);
        $this->assertArrayHasKey('to', $emailParams);
        $this->assertEquals('to@example.com;', $emailParams['to']);
    }

    public function testMissingApiKey()
    {
        $this->expectException('ElasticEmail\Mailer\Exception\MissingElasticEmailApiKeyException');

        Email::dropTransport('elasticemail');
        Email::setConfigTransport(
            'elasticemail',
            [
                'className' => 'ElasticEmail.ElasticEmail',
                'apiKey' => ''
            ]
        );

        $email = new Email();
        $email->setProfile(['transport' => 'elasticemail']);
        $email->setFrom('from@example.com')
            ->setSender('from@example.com')
            ->setTo('to@example.com')
            ->setEmailFormat('both')
            ->setSubject('Email from CakePHP Elastic Email plugin')
            ->send('Hello there, <br> This is an email from CakePHP Elastic Email plugin.');
    }

    public function testInvalidKey()
    {
        Email::dropTransport('elasticemail');
        Email::setConfigTransport(
            'elasticemail',
            [
                'className' => 'ElasticEmail.ElasticEmail',
                'apiKey' => '123'
            ]
        );

        $email = new Email();
        $email->setProfile(['transport' => 'elasticemail']);
        $res = $email->setFrom(['from@example.com' => 'CakePHP Elastic Email'])
            ->setSender(['from@example.com' => 'CakePHP Elastic Email'])
            ->setTo('to@example.com')
            ->setEmailFormat('both')
            ->setSubject('Email from CakePHP Elastic Email plugin')
            ->send('Hello there, <br> This is an email from CakePHP Elastic Email plugin.');

        $this->assertEquals(false, $res['apiResponse']['success']);
        $this->assertEquals('Incorrect apikey', $res['apiResponse']['error']);
    }
}
