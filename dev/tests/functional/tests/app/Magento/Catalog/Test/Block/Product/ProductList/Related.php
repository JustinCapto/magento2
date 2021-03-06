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
 * @spi
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Catalog\Test\Block\Product\ProductList;

use Mtf\Block\Block;
use Mtf\Factory\Factory;
use Mtf\Client\Element\Locator;

class Related extends Block
{
    protected $relatedProduct = "//div[normalize-space(div//a)='%s']";

    /**
     * @param string $productName
     * @return bool
     */
    public function isRelatedProductVisible($productName)
    {
        return $this->getProductElement($productName)->isVisible();
    }

    /**
     * @param string $productName
     * @return bool
     */
    public function isRelatedProductSelectable($productName)
    {
        return $this->getProductElement($productName)->find("[name='related_products[]']")->isVisible();
    }

    /**
     * @param string $productName
     */
    public function openRelatedProduct($productName)
    {
        $this->getProductElement($productName)->find('.product.name>a')->click();
    }

    /**
     * @param string $productName
     */
    public function selectProductForAddToCart($productName)
    {
        $this->getProductElement($productName)
            ->find("[name='related_products[]']", Locator::SELECTOR_CSS, 'checkbox')
            ->setValue('Yes');
    }

    /**
     * @param string $productName
     * @return mixed|\Mtf\Client\Element
     */
    private function getProductElement($productName)
    {
        return $this->_rootElement->find(sprintf($this->relatedProduct, $productName), Locator::SELECTOR_XPATH);
    }
}