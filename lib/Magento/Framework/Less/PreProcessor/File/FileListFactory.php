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
namespace Magento\Framework\Less\PreProcessor\File;

/**
 * Factory class for \Magento\Framework\Less\PreProcessor\File\FileList
 */
class FileListFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManager
     */
    protected $objectManager;

    /**
     * Instance name to create
     *
     * @var string
     */
    protected $instanceName;

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManager $objectManager
     * @param string $instanceName
     */
    public function __construct(
        \Magento\Framework\ObjectManager $objectManager,
        $instanceName = 'Magento\Framework\Less\PreProcessor\File\FileList'
    ) {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return FileList
     * @throws \UnexpectedValueException
     */
    public function create(array $data = array())
    {
        $fileList = $this->objectManager->create($this->instanceName, $data);
        if (!$fileList instanceof FileList) {
            throw new \UnexpectedValueException(
                get_class($fileList) . ' doesn\'t extend \Magento\Framework\Less\PreProcessor\File\FileList'
            );
        }
        return $fileList;
    }
}
