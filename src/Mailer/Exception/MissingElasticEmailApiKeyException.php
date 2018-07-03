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
namespace ElasticEmail\Mailer\Exception;

use Cake\Core\Exception\Exception;

/**
 * Missing Elastic Email Api Key exception - used when an api key cannot be found.
 */
class MissingElasticEmailApiKeyException extends Exception
{

    /**
     * {@inheritDoc}
     */
    protected $_messageTemplate = '%s for Elastic Email could not be found.';
}
