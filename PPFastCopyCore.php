<?php
/**
 * Copyright since 2024 PrestaPilot
 * PrestaPilot is a trademark of PrestaPilot
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@prestapilot.com so we can send you a copy immediately.
 *
 * @author    PrestaPilot <contact@prestapilot.com>
 * @copyright Since 2024 PrestaPilot
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class PPFastCopyCore extends ObjectModel
{
    /**
     * Check if product is in category
     *
     * @param int $id_product
     * @param int $id_category
     * @return bool
     */
    public static function checkProductInCategory($id_product, $id_category)
    {
        $sql = 'SELECT COUNT(*)
            FROM ' . _DB_PREFIX_ . 'category_product
            WHERE id_product = ' . (int)$id_product . '
            AND id_category = ' . (int)$id_category;

        return Db::getInstance()->getValue($sql) > 0;
    }

    /**
     * Get product default category
     *
     * @param int $id_product
     * @return int
     */
    public static function productGetDefaultCategory($id_product)
    {
        $sql = 'SELECT id_category_default
            FROM ' . _DB_PREFIX_ . 'product
            WHERE id_product = ' . (int)$id_product;

        return (int)Db::getInstance()->getValue($sql);
    }

    /**
     * Set product default category
     *
     * @param int $id_product
     * @param int $id_category
     * @return bool
     */
    public static function productSetDefaultCategory($id_product, $id_category)
    {
        return Db::getInstance()->update(
            'product',
            ['id_category_default' => (int)$id_category],
            'id_product = ' . (int)$id_product
        );
    }

    /**
     * Add product to category
     *
     * @param int $id_product
     * @param int $id_category
     * @return bool
     */
    public static function productAddToCategory($id_product, $id_category)
    {
        if (self::checkProductInCategory($id_product, $id_category)) {
            return true;
        }

        $position = 0;
        $arr = [
            'id_product' => (int)$id_product,
            'id_category' => (int)$id_category,
            'position' => (int)$position,
        ];

        return Db::getInstance()->insert('category_product', $arr);
    }
}