<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\User\Test\Page\Backend;

use Mtf\Page\Page;
use Mtf\Factory\Factory;
use Mtf\Client\Element\Locator;

/**
 * Class UserIndex
 * System ->Permissions -> All Users page
 *
 */
class UserIndex extends Page
{
    /**
     * URL part for admin user page
     */
    const MCA = 'admin/user/';

    /**
     * Admin User Grid on backend
     *
     * @var string
     */
    protected $userGridBlock = 'permissionsUserGrid';

    /**
     * Message Block on page
     *
     * @var string
     */
    protected $messagesBlock = '#messages .messages';

    /**
     * Admin header Block
     *
     * @var string
     */
    protected $adminPanelHeaderBlock = 'page-header';

    /**
     * Constructor
     */
    protected function _init()
    {
        $this->_url = $_ENV['app_backend_url'] . self::MCA;
    }

    /**
     * Get User Grid block
     *
     * @return \Magento\User\Test\Block\Backend\UserGrid
     */
    public function getUserGridBlock()
    {
        return Factory::getBlockFactory()->getMagentoUserBackendUserGrid(
            $this->_browser->find($this->userGridBlock, Locator::SELECTOR_ID)
        );
    }

    /**
     * Get global messages block
     *
     * @return \Magento\Core\Test\Block\Messages
     */
    public function getMessagesBlock()
    {
        return Factory::getBlockFactory()->getMagentoCoreMessages($this->_browser->find($this->messagesBlock));
    }

    /**
     * Get Admin Panel Header Block
     *
     * @return \Magento\Backend\Test\Block\Page\Header
     */
    public function getAdminPanelHeaderBlock()
    {
        return Factory::getBlockFactory()->getMagentoBackendPageHeader(
            $this->_browser->find($this->adminPanelHeaderBlock, Locator::SELECTOR_CLASS_NAME)
        );
    }
}

