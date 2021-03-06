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
namespace Magento\Framework\View\Element;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * @magentoAppIsolation enabled
 */
class AbstractBlockTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Element\AbstractBlock
     */
    protected $_block;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $_layout = null;

    protected static $_mocks = array();

    protected function setUp()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\App\State')
            ->setAreaCode('frontend');
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\DesignInterface'
        )->setDefaultDesignTheme();
        $this->_block = $this->getMockForAbstractClass(
            'Magento\Framework\View\Element\AbstractBlock',
            array(
                \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                    'Magento\Framework\View\Element\Context'
                ),
                array('module_name' => 'Magento_Core')
            )
        );
    }

    /**
     * Checks, that not existing image in CSS not affected own publication
     *
     * @magentoAppIsolation enabled
     */
    public function testCssWithWrongImage()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\Framework\App\Filesystem $filesystem */
        $relativePath = $objectManager->get(
            'Magento\Framework\App\Filesystem'
        )->getDirectoryRead(
            \Magento\Framework\App\Filesystem::ROOT_DIR
        )->getRelativePath(
            __DIR__ . '/_files'
        );
        /** @var $directoryList \Magento\Framework\App\Filesystem\DirectoryList */
        $directoryList = $objectManager->get('Magento\Framework\App\Filesystem\DirectoryList');
        $directoryList->addDirectory(\Magento\Framework\App\Filesystem::THEMES_DIR, array('path' => $relativePath));

        $cssUrl = $this->_block->getViewFileUrl(
            'css/wrong.css',
            array('area' => 'frontend', 'theme' => 'Magento/plushe', 'locale' => 'en_US')
        );
        $this->assertStringMatchesFormat('%s/css/wrong.css', $cssUrl);
    }

    public function testGetRequest()
    {
        $this->assertInstanceOf('Magento\Framework\App\RequestInterface', $this->_block->getRequest());
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetParentBlock()
    {
        // Without layout
        $this->assertFalse($this->_block->getParentBlock());

        // Need to create blocks through layout
        $parentBlock = $this->_createBlockWithLayout('block1', 'block1', 'Magento\Framework\View\Element\Text');
        $childBlock = $this->_createBlockWithLayout('block2', 'block2');

        $this->assertEmpty($childBlock->getParentBlock());
        $parentBlock->setChild('block2', $childBlock);
        $this->assertSame($parentBlock, $childBlock->getParentBlock());
    }

    /**
     * @covers \Magento\Framework\View\Element\AbstractBlock::addChild
     */
    public function testAddChild()
    {
        $parentBlock = $this->_createBlockWithLayout(
            'testAddChild',
            'testAddChild',
            'Magento\Framework\View\Element\Text'
        );
        $child = $parentBlock->addChild(
            'testAddChildAlias',
            'Magento\Framework\View\Element\Text',
            array('content' => 'content')
        );
        $this->assertInstanceOf('Magento\Framework\View\Element\Text', $child);
        $this->assertEquals('testAddChild.testAddChildAlias', $child->getNameInLayout());
        $this->assertEquals($child, $parentBlock->getChildBlock('testAddChildAlias'));
        $this->assertEquals('content', $child->getContent());
    }

    public function testSetGetNameInLayout()
    {
        // Basic setting/getting
        $this->assertEmpty($this->_block->getNameInLayout());
        $name = uniqid('name');
        $this->_block->setNameInLayout($name);
        $this->assertEquals($name, $this->_block->getNameInLayout());

        // Setting second time, along with the layout
        $layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        );
        $layout->createBlock('Magento\Framework\View\Element\Template', $name);
        $block = $layout->getBlock($name);
        $this->assertInstanceOf('Magento\Framework\View\Element\AbstractBlock', $block);
        $block->setNameInLayout($name);
        $this->assertInstanceOf('Magento\Framework\View\Element\AbstractBlock', $layout->getBlock($name));
        $this->assertEquals($name, $block->getNameInLayout());
        $this->assertTrue($layout->hasElement($name));
        $newName = 'new_name';
        $block->setNameInLayout($newName);
        $this->assertTrue($layout->hasElement($newName));
        $this->assertFalse($layout->hasElement($name));
    }

    /**
     * @magentoAppIsolation enabled
     * @covers \Magento\Framework\View\Element\AbstractBlock::getChildNames
     * @covers \Magento\Framework\View\Element\AbstractBlock::insert
     */
    public function testGetChildNames()
    {
        // Without layout
        $this->assertEquals(array(), $this->_block->getChildNames());

        // With layout
        $parent = $this->_createBlockWithLayout('parent', 'parent');
        $block1 = $this->_createBlockWithLayout('block1');
        $block2 = $this->_createBlockWithLayout('block2');
        $block3 = $this->_createBlockWithLayout('block3');
        $block4 = $this->_createBlockWithLayout('block4');

        $parent->insert($block1);
        // add one block
        $parent->insert($block2, 'block1', false);
        // add second to the 1st position
        $parent->insert($block3, 'block1', false);
        // add third to the 2nd position
        $parent->insert($block4, 'block3', true);
        // add fourth block to the 3rd position

        $this->assertEquals(array('block2', 'block3', 'block4', 'block1'), $parent->getChildNames());
    }

    public function testSetAttribute()
    {
        $this->assertEmpty($this->_block->getSomeValue());
        $this->_block->setAttribute('some_value', 'value');
        $this->assertEquals('value', $this->_block->getSomeValue());
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testSetGetUnsetChild()
    {
        // With layout
        $parent = $this->_createBlockWithLayout('parent', 'parent');

        // Regular block
        $nameOne = uniqid('block.');
        $blockOne = $this->_createBlockWithLayout($nameOne, $nameOne);
        $parent->setChild('block1', $blockOne);
        $this->assertSame($blockOne, $parent->getChildBlock('block1'));

        // Block factory name
        $blockTwo = $this->_createBlockWithLayout('parent_block2', 'parent_block2');
        $blockTwo->setChild('block2', $nameOne);
        $this->assertSame($blockOne, $blockTwo->getChildBlock('block2'));

        // No name block
        $blockThree = $this->_createBlockWithLayout('');
        $parent->setChild('block3', $blockThree);
        $this->assertSame($blockThree, $parent->getChildBlock('block3'));

        // Unset
        $parent->unsetChild('block3');
        $this->assertNotSame($blockThree, $parent->getChildBlock('block3'));
        $parent->insert($blockOne, '', true, 'block1');
        $this->assertContains($nameOne, $parent->getChildNames());
        $parent->unsetChild('block1');
        $this->assertNotSame($blockOne, $parent->getChildBlock('block1'));
        $this->assertNotContains($nameOne, $parent->getChildNames());
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testUnsetCallChild()
    {
        $blockParent = $this->_createBlockWithLayout('parent', 'parent');
        $block = $this->_createBlockWithLayout('block1', 'block1');
        $block->setSomeValue(true);
        $blockParent->setChild('block1', $block);
        $this->assertSame($block, $blockParent->getChildBlock('block1'));
        $blockParent->unsetCallChild('block1', 'getSomeValue', true, array());
        $this->assertNotSame($block, $blockParent->getChildBlock('block1'));
    }

    /**
     * @magentoAppIsolation enabled
     * @covers \Magento\Framework\View\Element\AbstractBlock::unsetChildren
     * @covers \Magento\Framework\View\Element\AbstractBlock::getChildBlock
     */
    public function testUnsetChildren()
    {
        $parent = $this->_createBlockWithLayout('block', 'block');
        $this->assertEquals(array(), $parent->getChildNames());
        $blockOne = $this->_createBlockWithLayout('block1', 'block1');
        $blockTwo = $this->_createBlockWithLayout('block2', 'block2');
        $parent->setChild('block1', $blockOne);
        $parent->setChild('block2', $blockTwo);
        $this->assertSame($blockOne, $parent->getChildBlock('block1'));
        $this->assertSame($blockTwo, $parent->getChildBlock('block2'));
        $parent->unsetChildren();
        $this->assertEquals(array(), $parent->getChildNames());
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetChildBlock()
    {
        $childAlias = 'child_alias';
        $childName = 'child';
        $parentName = 'parent';

        // Without layout
        $this->assertFalse($this->_block->getChildBlock($childAlias));

        // With layout
        /** @var $layout \Magento\Framework\View\Layout */
        $layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        );
        $child = $layout->createBlock('Magento\Framework\View\Element\Text', $childName);
        $layout->addBlock($this->_block, $parentName);

        $this->_block->setChild($childAlias, $child);
        $result = $this->_block->getChildBlock($childAlias);

        $this->assertInstanceOf('Magento\Framework\View\Element\Text', $result);
        $this->assertEquals($childName, $result->getNameInLayout());
        $this->assertEquals($child, $result);
    }

    /**
     * @magentoAppIsolation enabled
     * @covers \Magento\Framework\View\Element\AbstractBlock::getChildHtml
     * @covers \Magento\Framework\View\Element\AbstractBlock::getChildChildHtml
     */
    public function testGetChildHtml()
    {
        // Without layout
        $this->assertEmpty($this->_block->getChildHtml());
        $this->assertEmpty($this->_block->getChildHtml('block'));

        // With layout
        $parent = $this->_createBlockWithLayout('parent', 'parent');
        $blockOne = $this->_createBlockWithLayout('block1', 'block1', 'Magento\Framework\View\Element\Text');
        $blockTwo = $this->_createBlockWithLayout('block2', 'block2', 'Magento\Framework\View\Element\Text');
        $blockOne->setText('one');
        $blockTwo->setText('two');
        $parent->insert($blockTwo, '-', false, 'block2');
        // make block2 1st
        $parent->insert($blockOne, '-', false, 'block1');
        // make block1 1st

        $this->assertEquals('one', $parent->getChildHtml('block1'));
        $this->assertEquals('two', $parent->getChildHtml('block2'));

        // Sorted will render in the designated order
        $this->assertEquals('onetwo', $parent->getChildHtml('', true, true));

        // GetChildChildHtml
        $blockTwo->setChild('block11', $blockOne);
        $this->assertEquals('one', $parent->getChildChildHtml('block2'));
        $this->assertEquals('', $parent->getChildChildHtml(''));
        $this->assertEquals('', $parent->getChildChildHtml('block3'));
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetChildChildHtml()
    {
        // Without layout
        $this->assertEmpty($this->_block->getChildChildHtml('alias'));

        // With layout
        $parent1 = $this->_createBlockWithLayout('parent1', 'parent1');
        $parent2 = $this->_createBlockWithLayout('parent2', 'parent2');

        $block1 = $this->_createBlockWithLayout('block1', 'block1', 'Magento\Framework\View\Element\Text');
        $block2 = $this->_createBlockWithLayout('block2', 'block2', 'Magento\Framework\View\Element\Text');
        $block3 = $this->_createBlockWithLayout('block3', 'block3', 'Magento\Framework\View\Element\Text');
        $block4 = $this->_createBlockWithLayout('block4', 'block4', 'Magento\Framework\View\Element\Text');

        $block1->setText('one');
        $block2->setText('two');
        $block3->setText('three');
        $block4->setText('four');

        $parent1->insert($parent2);
        $parent2->insert($block1, '-', false, 'block1');
        $parent2->insert($block2, '-', false, 'block2');
        $parent2->insert($block3, '-', true, 'block3');
        $parent1->insert($block4);
        $this->assertEquals('twoonethree', $parent1->getChildChildHtml('parent2'));
    }

    public function testGetBlockHtml()
    {
        // Without layout
        /** @var $blockFactory \Magento\Framework\View\Element\BlockFactory */
        $blockFactory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\Element\BlockFactory'
        );
        $block1 = $blockFactory->createBlock('Magento\Framework\View\Element\Text');
        $block1->setText('Block text');
        $block1->setNameInLayout('block');
        $html = $this->_block->getBlockHtml('block');
        $this->assertInternalType('string', $html);
        $this->assertEmpty($html);

        // With layout
        $expected = 'Block2';
        $block2 = $this->_createBlockWithLayout('block2', 'block2', 'Magento\Framework\View\Element\Text');
        $block3 = $this->_createBlockWithLayout('block3', 'block3');
        $block2->setText($expected);
        $html = $block3->getBlockHtml('block2');
        $this->assertInternalType('string', $html);
        $this->assertEquals($expected, $html);
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testInsertBlockWithoutName()
    {
        $parent = $this->_createBlockWithLayout('parent', 'parent');
        $block = $this->_createBlockWithLayout('');
        $parent->setChild('', $block);
        $this->assertContains('abstractblockmock', $parent->getChildNames());
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testInsertBlockWithAlias()
    {
        $parent = $this->_createBlockWithLayout('parent', 'parent');
        $block = $this->_createBlockWithLayout('block_name');
        $parent->insert($block, '', true, 'block_alias');
        $this->assertContains('block_name', $parent->getChildNames());
        $this->assertSame($block, $parent->getChildBlock('block_alias'));
    }

    public function testInsertWithSibling()
    {
        $name1 = 'block_one';
        $parent = $this->_createBlockWithLayout('parent', 'parent');
        $blockOne = $this->_createBlockWithLayout($name1);
        $parent->insert($blockOne);
        $this->assertContains($name1, $parent->getChildNames());

        $name2 = 'block_two';
        $blockTwo = $this->_createBlockWithLayout($name2);
        $parent->insert($blockTwo, 'wrong_sibling', false);
        $this->assertSame(1, array_search($name2, $parent->getChildNames()));

        $name3 = 'block_three';
        $blockThree = $this->_createBlockWithLayout($name3);
        $parent->insert($blockThree, $name2, false);
        $this->assertSame(1, array_search($name3, $parent->getChildNames()));

        $name4 = 'block_four';
        $blockFour = $this->_createBlockWithLayout($name4);
        $parent->insert($blockFour, $name1, true);
        $this->assertSame(1, array_search($name4, $parent->getChildNames()));
    }

    /**
     * @magentoAppIsolation enabled
     * @expectedException \Magento\Framework\Exception
     */
    public function testInsertWithoutCreateBlock()
    {
        $parent = $this->_createBlockWithLayout('parent', 'parent');
        $parent->insert('block');
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testInsertContainer()
    {
        $parentName = 'parent';
        $name = 'container';
        $parent = $this->_createBlockWithLayout($parentName, $parentName);
        $layout = $parent->getLayout();

        $this->assertEmpty($layout->getChildNames($parentName));
        $layout->addContainer($name, 'Container');
        $parent->insert($name);
        $this->assertEquals(array($name), $layout->getChildNames($parentName));
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testAppend()
    {
        $parent = $this->_createBlockWithLayout('parent', 'parent');
        $child1 = $this->_createBlockWithLayout('child1');
        $parent->append($child1, 'child1');
        $child2 = $this->_createBlockWithLayout('child2');
        $parent->append($child2);
        $this->assertEquals(array('child1', 'child2'), $parent->getChildNames());
    }

    /**
     * @magentoAppIsolation enabled
     * @covers \Magento\Framework\View\Element\AbstractBlock::getGroupChildNames
     * @covers \Magento\Framework\View\Layout::addToParentGroup
     */
    public function testAddToParentGroup()
    {
        $parent = $this->_createBlockWithLayout('parent', 'parent');
        $block1 = $this->_createBlockWithLayout('block1', 'block1');
        $block2 = $this->_createBlockWithLayout('block2', 'block2');
        $parent->setChild('block1', $block1)->setChild('block2', $block2);
        $this->_layout->addToParentGroup('block1', 'group');
        $this->_layout->addToParentGroup('block2', 'group');
        $group = $parent->getGroupChildNames('group');
        $this->assertContains('block1', $group);
        $this->assertContains('block2', $group);
        $this->assertSame($group[0], 'block1');
        $this->assertSame($group[1], 'block2');
    }

    public function testGetChildData()
    {
        $parent = $this->_createBlockWithLayout('parent', 'parent');
        $block = $this->_createBlockWithLayout('block', 'block');
        $block->setSomeProperty('some_value');
        $parent->setChild('block1', $block);

        // all child data
        $actualChildData = $parent->getChildData('block1');
        $this->assertArrayHasKey('some_property', $actualChildData);
        $this->assertEquals('some_value', $actualChildData['some_property']);

        // specific child data key
        $this->assertEquals('some_value', $parent->getChildData('block1', 'some_property'));

        // non-existing child block
        $this->assertNull($parent->getChildData('unknown_block'));
    }

    public function testGetUrl()
    {
        $base = 'http://localhost/index.php/';
        $withRoute = "{$base}catalog/product/view/id/10/";
        $this->assertEquals($base, $this->_block->getUrl());
        $this->assertEquals($withRoute, $this->_block->getUrl('catalog/product/view', array('id' => 10)));
    }

    /**
     * Isolation level has been raised in order to flush themes configuration in-memory cache
     *
     * @magentoAppIsolation enabled
     */
    public function testGetViewFileUrl()
    {
        $this->assertStringStartsWith('http://localhost/pub/static/frontend/', $this->_block->getViewFileUrl());
        $this->assertStringEndsWith('css/styles.css', $this->_block->getViewFileUrl('css/styles.css'));

        /**
         * File is not exist
         */
        $this->assertStringEndsWith(
            '/core/index/notfound',
            $this->_block->getViewFileUrl('not_exist_folder/wrong_bad_file.xyz')
        );
    }

    public function testFormatDate()
    {
        $locale = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\Stdlib\DateTime\TimezoneInterface'
        );
        $this->assertEquals($locale->formatDate(), $this->_block->formatDate());
    }

    public function testFormatTime()
    {
        $locale = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\Stdlib\DateTime\TimezoneInterface'
        );
        $this->assertEquals($locale->formatTime(), $this->_block->formatTime());
    }

    public function testGetModuleName()
    {
        $this->assertEquals('Magento_Core', $this->_block->getModuleName());
        $this->assertEquals('Magento_Core', $this->_block->getData('module_name'));
    }

    /**
     * @dataProvider escapeHtmlDataProvider
     */
    public function testEscapeHtml($data, $expected)
    {
        $actual = $this->_block->escapeHtml($data);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array
     */
    public function escapeHtmlDataProvider()
    {
        return array(
            'array data' => array(
                'data' => array('one', '<two>three</two>'),
                'expected' => array('one', '&lt;two&gt;three&lt;/two&gt;')
            ),
            'string data conversion' => array(
                'data' => '<two>three</two>',
                'expected' => '&lt;two&gt;three&lt;/two&gt;'
            ),
            'string data no conversion' => array('data' => 'one', 'expected' => 'one')
        );
    }

    public function testStripTags()
    {
        $str = '<p>text</p>';
        $this->assertEquals('text', $this->_block->stripTags($str));
    }

    public function testEscapeUrl()
    {
        $url = 'http://example.com/?wsdl=1';
        $this->assertEquals($url, $this->_block->escapeUrl($url));
    }

    public function testEscapeJsQuote()
    {
        $script = "var s = 'text';";
        $this->assertEquals('var s = \\\'text\\\';', $this->_block->escapeJsQuote($script));
    }

    public function testGetCacheKeyInfo()
    {
        $name = uniqid('block.');
        $block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Framework\View\Element\Text'
        );
        $block->setNameInLayout($name);
        $this->assertEquals(array($name), $block->getCacheKeyInfo());
    }

    public function testGetCacheKey()
    {
        $name = uniqid('block.');
        $block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Framework\View\Element\Text'
        );
        $block->setNameInLayout($name);
        $key = $block->getCacheKey();
        $this->assertNotEmpty($key);
        $this->assertNotEquals('key', $key);
        $this->assertNotEquals($name, $key);

        $block->setCacheKey('key');
        $this->assertEquals('key', $block->getCacheKey());
    }

    /**
     * Create <N> sample blocks
     *
     * @param int $qty
     * @param bool $withLayout
     * @param string $className
     * @return array
     */
    protected function _createSampleBlocks(
        $qty,
        $withLayout = true,
        $className = 'Magento\Framework\View\Element\Template'
    ) {
        $blocks = array();
        $names = array();
        $layout = false;
        if ($withLayout) {
            $layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                'Magento\Framework\View\LayoutInterface'
            );
        }
        for ($i = 0; $i < $qty; $i++) {
            $name = uniqid('block.');
            if ($layout) {
                $block = $layout->createBlock($className, $name);
            } else {
                $block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create($className);
                $block->setNameInLayout($name);
            }
            $blocks[] = $block;
            $names[] = $name;
        }
        return array($blocks, $names);
    }

    /**
     * Create Block with Layout
     *
     * @param string $name
     * @param null|string $alias
     * @param null|string $type
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    protected function _createBlockWithLayout(
        $name = 'block',
        $alias = null,
        $type = 'Magento\Framework\View\Element\AbstractBlock'
    ) {
        $typePart = explode('\\', $type);
        $mockClass = array_pop($typePart) . 'Mock';
        if (!isset(self::$_mocks[$mockClass])) {
            self::$_mocks[$mockClass] = $this->getMockForAbstractClass(
                $type,
                array(
                    \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                        'Magento\Framework\View\Element\Context'
                    ),
                    array('module_name' => 'Magento_Core')
                ),
                $mockClass
            );
        }
        if (is_null($this->_layout)) {
            $this->_layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                'Magento\Framework\View\LayoutInterface'
            );
        }
        $block = $this->_layout->addBlock($mockClass, $name, '', $alias);
        return $block;
    }
}
