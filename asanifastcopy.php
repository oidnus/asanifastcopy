<?php
/*
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* It is available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
*
* DISCLAIMER
* This code is provided as is without any warranty.
* No promise of safety or security.
*
*  @author          Damian Ślimak <damian@asani.pl>
*  @author          Dobrawa Wrzosek <dobrawa.wrzosek@gmail.com>
*  @license         http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/


if (!defined('_CAN_LOAD_FILES_'))
	exit;

include_once(dirname(__FILE__) . '/AsaniCore.php');

class Asanifastcopy extends Module
{

	public function __construct()
	{
		$this->name = 'asanifastcopy';
		$this->author = 'asani.pl';
		$this->tab = 'front_office_features';
		$this->version = '0.1';

		$this->bootstrap = true;
		parent::__construct();

		$this->displayName = $this->l('Asani - Fastcopy');
		$this->description = $this->l('Asani - Fast copying products to category');
		$this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
	}

	public function install()
	{
		parent::install();
		return (bool)true;
	}

	public function uninstall()
	{
		parent::uninstall();
		return true;
	}

	public function getContent()
	{

		if (Tools::isSubmit('submitModule'))
		{

			if (is_array(Tools::getValue('prod'))){
				foreach (Tools::getValue('prod') as $id_product => $value){
					if (Tools::getValue('category_action')=='copy'){
						AsaniCore::productAddToCategory($id_product,Tools::getValue('category_secondary'));
					}


				}
			}


//			echo '<pre>';
//			print_r($_POST);
//			echo '</pre>';

		}

		$html = '';
		$html .= $this->renderForm();
		return $html;
	}

	protected function generateCategoriesMenu($tree, &$arr = []){
		foreach ($tree as $item){
			$arr[] = [
				'id' => $item['id_category'],
				'name' =>str_repeat('&nbsp;&nbsp;',$item['level_depth']-1).$item['name']
			];
			if (isset($item['children'])){
				$this->generateCategoriesMenu($item['children'],$arr);
			}
		}

		return $arr;
	}

	public function renderForm()
	{

		$action_list = [
			[
				'id' => 'reload',
				'name' => $this->l('Only reload')
			],[
				'id' => 'copy',
				'name' => $this->l('Copy')
			]
			

		];

		$category_tree = Category::getNestedCategories(Category::getRootCategories()[0]['id_category'], (int)$this->context->language->id, false);
		$category_for_select  = $this->generateCategoriesMenu($category_tree);

		$input = [];
		$input[] = [
			'type' => 'select',
			'label' => $this->l('Main category'),
			'name' => 'category_primary',
			'options' => array(
				'query' => $category_for_select,
				'id' => 'id',
				'name' => 'name'
			)
		];
		$input[] = [
			'type' => 'select',
			'label' => $this->l('Target category'),
			'name' => 'category_secondary',
			'options' => array(
				'query' => $category_for_select,
				'id' => 'id',
				'name' => 'name'
			)
		];

		$input[] = [
			'type' => 'select',
			'label' => $this->l('Action'),
			'name' => 'category_action',
			'options' => array(
				'query' => $action_list,
				'id' => 'id',
				'name' => 'name'
			)
		];

//		echo '<pre>';
//		print_r($this->getProductList());
//		die;
		$input[] = [
			'type' => 'fastcopy_productlist',
			'label' => $this->l('Products'),
			'name' => 'category_th',
			'desc' => 'category_th',
			'products' =>$this->getProductList(Tools::getValue('category_primary')!='' ? Tools::getValue('category_primary') : Category::getRootCategories()[0]['id_category']),
			'products_sec' =>$this->getProductList(Tools::getValue('category_secondary')!='' ? Tools::getValue('category_secondary') : Category::getRootCategories()[0]['id_category']),
			'category_primary' =>Tools::getValue('category_primary')
		];

		$fields_form = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Settings'),
					'icon' => 'icon-cogs'
				),
				'input' => $input,
				'submit' => array(
					'title' => $this->l('Execute'),
				)
			),
		);

		$helper = new HelperForm();
		$helper->show_toolbar = false;
		$helper->table =  $this->table;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		$this->fields_form = array();

		$helper->module = $this;
		$helper->identifier = $this->identifier;
		$helper->submit_action = 'submitModule';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->tpl_vars = array(
			'fields_value' => $this->getConfigFieldsValues(),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id
		);

		return $helper->generateForm(array($fields_form));
	}

	public function getProductList($id_category){

		$id_lang = Configuration::get('PS_LANG_DEFAULT');
		$limit = 1000;

		$result = Db::getInstance()->executeS('
					SELECT DISTINCT pl.name, p.reference, p.id_category_default, p.id_product, pl.id_shop
					FROM '._DB_PREFIX_.'product p
					LEFT JOIN '._DB_PREFIX_.'product_shop ps ON (ps.id_product = p.id_product AND ps.id_shop ='.(int)Context::getContext()->shop->id.')
					LEFT JOIN '._DB_PREFIX_.'product_lang pl
						ON (pl.id_product = p.id_product AND pl.id_lang = '.(int)$id_lang.')
					LEFT JOIN '._DB_PREFIX_.'category_product cp on cp.id_product = p.id_product 
					WHERE cp.id_category = '.$id_category.'
					GROUP BY pl.id_product
					LIMIT '.(int)$limit);

		foreach ($result as $key => $item){
			$protocol_link = (Configuration::get('PS_SSL_ENABLED') || Tools::usingSecureMode()) ? 'https://' : 'http://';
			$link = new Link($protocol_link,$protocol_link);

			if (Product::getCover($item['id_product'])['id_image'] > 0)
			{
				$result[$key]['image'] = $link->getImageLink('xxxx', Product::getCover($item['id_product'])['id_image'], 'small_default');
			}

		}

//		die;
		return $result;
	}

	public function getConfigFieldsValues()
	{
		$var =  array();

		$var['category_primary'] = Tools::getValue('category_primary')!='' ? Tools::getValue('category_primary') : Category::getRootCategories()[0]['id_category'];
		$var['category_secondary'] = Tools::getValue('category_secondary')!='' ? Tools::getValue('category_secondary') : Category::getRootCategories()[0]['id_category'];
		$var['category_action'] = 'reload';

		return $var;
	}

}
