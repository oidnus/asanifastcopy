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

require_once _PS_MODULE_DIR_ . 'ppfastcopy/PPFastCopyCore.php';

class PPFastCopy extends Module
{
    /**
     * Module constructor
     */
    public function __construct()
    {
        $this->name = 'ppfastcopy';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'PrestaPilot';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '9.0.0',
            'max' => _PS_VERSION_
        ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Szybkie kopiowanie produktów');
        $this->description = $this->l('Moduł do szybkiego kopiowania produktów między kategoriami.');
        $this->confirmUninstall = $this->l('Czy na pewno chcesz odinstalować ten moduł?');
    }

    /**
     * Install module
     *
     * @return bool
     */
    public function install()
    {
        return parent::install();
    }

    /**
     * Uninstall module
     *
     * @return bool
     */
    public function uninstall()
    {
        return parent::uninstall();
    }

    /**
     * Get module content (configuration page)
     *
     * @return string
     */
    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submitModule')) {
            $output .= $this->processForm();
        }

        $output .= $this->renderForm();
        return $output;
    }

    /**
     * Process form submission
     *
     * @return string
     */
    protected function processForm()
    {
        $products = Tools::getValue('prod');
        $categoryAction = Tools::getValue('category_action');
        $categorySecondary = (int)Tools::getValue('category_secondary');

        if (!is_array($products)) {
            return $this->displayError($this->l('Nie wybrano żadnych produktów.'));
        }

        if ($categoryAction === 'copy') {
            $count = 0;
            foreach ($products as $id_product => $value) {
                if (PPFastCopyCore::productAddToCategory((int)$id_product, $categorySecondary)) {
                    $count++;
                }
            }
            return $this->displayConfirmation(
                sprintf($this->l('Skopiowano %d produktów do kategorii docelowej.'), $count)
            );
        }

        return '';
    }

    /**
     * Generate categories menu (recursive)
     *
     * @param array $tree
     * @param array $arr
     * @return array
     */
    protected function generateCategoriesMenu($tree, &$arr = [])
    {
        foreach ($tree as $item) {
            $arr[] = [
                'id' => $item['id_category'],
                'name' => str_repeat('&nbsp;&nbsp;', $item['level_depth'] - 1) . $item['name']
            ];
            if (isset($item['children'])) {
                $this->generateCategoriesMenu($item['children'], $arr);
            }
        }

        return $arr;
    }

    /**
     * Render configuration form
     *
     * @return string
     */
    public function renderForm()
    {
        $action_list = [
            [
                'id' => 'reload',
                'name' => $this->l('Tylko odśwież')
            ],
            [
                'id' => 'copy',
                'name' => $this->l('Kopiuj')
            ]
        ];

        $rootCategories = Category::getRootCategories();
        $rootCategoryId = $rootCategories[0]['id_category'];
        $category_tree = Category::getNestedCategories($rootCategoryId, (int)$this->context->language->id, false);
        $category_for_select = $this->generateCategoriesMenu($category_tree);

        $input = [];
        $input[] = [
            'type' => 'select',
            'label' => $this->l('Kategoria główna'),
            'name' => 'category_primary',
            'options' => [
                'query' => $category_for_select,
                'id' => 'id',
                'name' => 'name'
            ]
        ];
        $input[] = [
            'type' => 'select',
            'label' => $this->l('Kategoria docelowa'),
            'name' => 'category_secondary',
            'options' => [
                'query' => $category_for_select,
                'id' => 'id',
                'name' => 'name'
            ]
        ];

        $input[] = [
            'type' => 'select',
            'label' => $this->l('Akcja'),
            'name' => 'category_action',
            'options' => [
                'query' => $action_list,
                'id' => 'id',
                'name' => 'name'
            ]
        ];

        $primaryCategory = Tools::getValue('category_primary') != ''
            ? Tools::getValue('category_primary')
            : $rootCategoryId;
        $secondaryCategory = Tools::getValue('category_secondary') != ''
            ? Tools::getValue('category_secondary')
            : $rootCategoryId;

        $input[] = [
            'type' => 'fastcopy_productlist',
            'label' => $this->l('Produkty'),
            'name' => 'category_th',
            'desc' => 'category_th',
            'products' => $this->getProductList($primaryCategory),
            'products_sec' => $this->getProductList($secondaryCategory),
            'category_primary' => $primaryCategory
        ];

        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Ustawienia'),
                    'icon' => 'icon-cogs'
                ],
                'input' => $input,
                'submit' => [
                    'title' => $this->l('Wykonaj'),
                ]
            ],
        ];

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form = [];

        $helper->module = $this;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        ];

        return $helper->generateForm([$fields_form]);
    }

    /**
     * Get product list for category
     *
     * @param int $id_category
     * @return array
     */
    public function getProductList($id_category)
    {
        $id_lang = (int)Configuration::get('PS_LANG_DEFAULT');
        $id_shop = (int)Context::getContext()->shop->id;
        $limit = 1000;

        $result = Db::getInstance()->executeS('
            SELECT DISTINCT pl.name, p.reference, p.id_category_default, p.id_product, pl.id_shop
            FROM ' . _DB_PREFIX_ . 'product p
            LEFT JOIN ' . _DB_PREFIX_ . 'product_shop ps
                ON (ps.id_product = p.id_product AND ps.id_shop = ' . (int)$id_shop . ')
            LEFT JOIN ' . _DB_PREFIX_ . 'product_lang pl
                ON (pl.id_product = p.id_product AND pl.id_lang = ' . (int)$id_lang . ')
            LEFT JOIN ' . _DB_PREFIX_ . 'category_product cp
                ON cp.id_product = p.id_product
            WHERE cp.id_category = ' . (int)$id_category . '
            GROUP BY pl.id_product
            LIMIT ' . (int)$limit
        );

        foreach ($result as $key => $item) {
            $cover = Product::getCover($item['id_product']);
            if ($cover && isset($cover['id_image']) && $cover['id_image'] > 0) {
                $link = Context::getContext()->link;
                $result[$key]['image'] = $link->getImageLink('product', $cover['id_image'], 'small_default');
            }
        }

        return $result;
    }

    /**
     * Get configuration field values
     *
     * @return array
     */
    public function getConfigFieldsValues()
    {
        $rootCategories = Category::getRootCategories();
        $rootCategoryId = $rootCategories[0]['id_category'];

        return [
            'category_primary' => Tools::getValue('category_primary') != ''
                ? Tools::getValue('category_primary')
                : $rootCategoryId,
            'category_secondary' => Tools::getValue('category_secondary') != ''
                ? Tools::getValue('category_secondary')
                : $rootCategoryId,
            'category_action' => 'reload',
        ];
    }
}