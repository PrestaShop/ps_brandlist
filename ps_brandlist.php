<?php
/*
* 2007-2016 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2016 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Ps_Brandlist extends Module implements WidgetInterface
{
    protected $templateFile;

    public function __construct()
    {
        $this->name = 'ps_brandlist';
        $this->tab = 'front_office_features';
        $this->version = '1.0.3';
        $this->author = 'PrestaShop';
        $this->need_instance = 0;

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->trans(
            'Brand list',
            array(),
            'Modules.Brandlist.Admin'
        );
        $this->description = $this->trans(
            'Display your brands on your catalog and allow your visitors to filter their search by brand.',
            array(),
            'Modules.Brandlist.Admin'
        );
        $this->ps_versions_compliancy = array('min' => '1.7.0.0', 'max' => _PS_VERSION_);

        $this->templateFile = 'module:ps_brandlist/views/templates/hook/ps_brandlist.tpl';
    }

    public function install()
    {
        Configuration::updateValue('BRAND_DISPLAY_TYPE', 'brand_text');
        Configuration::updateValue('BRAND_DISPLAY_TEXT_NB', 5);

        return parent::install() &&
            $this->registerHook('displayLeftColumn') &&
            $this->registerHook('displayRightColumn') &&
            $this->registerHook('actionObjectManufacturerDeleteAfter') &&
            $this->registerHook('actionObjectManufacturerAddAfter') &&
            $this->registerHook('actionObjectManufacturerUpdateAfter')
        ;
    }

    public function uninstall()
    {
        return parent::uninstall()
            && Configuration::deleteByName('BRAND_DISPLAY_TYPE')
            && Configuration::deleteByName('BRAND_DISPLAY_TEXT_NB');
    }

    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submitBlockBrands')) {
            $type = Tools::getValue('BRAND_DISPLAY_TYPE');
            $text_nb = (int)Tools::getValue('BRAND_DISPLAY_TEXT_NB');

            if ('brand_text' === $type && !Validate::isUnsignedInt($text_nb)) {
                $errors[] = $this->trans(
                    'There is an invalid number of elements.',
                    array(),
                    'Modules.Brandlist.Admin'
                );
            } elseif (!in_array($type, array('brand_text', 'brand_form'))) {
                $errors[] = $this->trans(
                    'Please activate at least one system list.',
                    array(),
                    'Modules.Brandlist.Admin'
                );
            } else {
                Configuration::updateValue('BRAND_DISPLAY_TYPE', $type);
                Configuration::updateValue('BRAND_DISPLAY_TEXT_NB', $text_nb);
                $this->_clearCache('*');
            }

            if (isset($errors) && count($errors)) {
                $output .= $this->displayError(implode('<br />', $errors));
            } else {
                $output .= $this->displayConfirmation($this->trans(
                    'Settings updated.',
                    array(),
                    'Admin.Global'
                ));
            }
        }

        return $output.$this->renderForm();
    }

    public function hookActionObjectManufacturerUpdateAfter($params)
    {
        $this->_clearCache('*');
    }

    public function hookActionObjectManufacturerAddAfter($params)
    {
        $this->_clearCache('*');
    }

    public function hookActionObjectManufacturerDeleteAfter($params)
    {
        $this->_clearCache('*');
    }

    public function _clearCache($template, $cache_id = null, $compile_id = null)
    {
        return parent::_clearCache($this->templateFile);
    }

    public function renderForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->trans(
                        'Settings',
                        array(),
                        'Admin.Global'
                    ),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'select',
                        'label' => $this->trans(
                            'Type of display',
                            array(),
                            'Modules.Brandlist.Admin'
                        ),
                        'name' => 'BRAND_DISPLAY_TYPE',
                        'required' => true,
                        'options' => array(
                            'query' => array(
                                array(
                                    'id' => 'brand_text',
                                    'name' => $this->trans(
                                        'Use a plain-text list',
                                        array(),
                                        'Modules.Brandlist.Admin'
                                    ),
                                ),
                                array(
                                    'id' => 'brand_form',
                                    'name' => $this->trans(
                                        'Use a drop-down list',
                                        array(),
                                        'Modules.Brandlist.Admin'
                                    ),
                                ),
                            ),
                            'id' => 'id',
                            'name' => 'name',
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans(
                            'Number of elements to display',
                            array(),
                            'Modules.Brandlist.Admin'
                        ),
                        'desc' => $this->trans(
                            'Only apply in plain-text mode',
                            array(),
                            'Modules.Brandlist.Admin'
                        ),
                        'name' => 'BRAND_DISPLAY_TEXT_NB',
                        'class' => 'fixed-width-xs'
                    ),
                ),
                'submit' => array(
                    'title' => $this->trans(
                        'Save',
                        array(),
                        'Admin.Actions'
                    ),
                ),
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang =
            Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ?
            Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') :
            0;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitBlockBrands';
        $helper->currentIndex = $this->context->link->getAdminLink(
                'AdminModules',
                false
            ) .
            '&configure=' . $this->name .
            '&tab_module=' . $this->tab .
            '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array($fields_form));
    }

    public function getConfigFieldsValues()
    {
        return array(
            'BRAND_DISPLAY_TYPE' => Tools::getValue(
                'BRAND_DISPLAY_TYPE',
                Configuration::get('BRAND_DISPLAY_TYPE')
            ),
            'BRAND_DISPLAY_TEXT_NB' => Tools::getValue(
                'BRAND_DISPLAY_TEXT_NB',
                Configuration::get('BRAND_DISPLAY_TEXT_NB')
            ),
        );
    }

    public function renderWidget(
        $hookName = null,
        array $configuration = array()
    ) {
        $cacheId = $this->getCacheId('ps_brandlist');
        $isCached = $this->isCached($this->templateFile, $cacheId);

        if (!$isCached) {
            $this->smarty->assign($this->getWidgetVariables($hookName, $configuration));
        }

        return $this->fetch($this->templateFile, $cacheId);
    }

    public function getWidgetVariables(
        $hookName = null,
        array $configuration = array()
    ) {
        $brands = Manufacturer::getManufacturers(
            false,
            (int)Context::getContext()->language->id,
            $active = true,
            $p = false,
            $n = false,
            $allGroup = false,
            $group_by = false,
            $withProduct = true
        );

        if (!empty($brands)) {
            foreach ($brands as &$brand) {
                $brand['image'] = $this->context->language->iso_code . '-default';
                $brand['link'] = $this->context->link->getManufacturerLink($brand['id_manufacturer']);
                $fileExist = file_exists(
                    _PS_MANU_IMG_DIR_ . $brand['id_manufacturer'] . '-' .
                    ImageType::getFormattedName('medium') . '.jpg'
                );

                if ($fileExist) {
                    $brand['image'] = $brand['id_manufacturer'];
                }
            }
        }

        return array(
            'brands' => $brands,
            'page_link' => $this->context->link->getPageLink('manufacturer'),
            'text_list_nb' => Configuration::get('BRAND_DISPLAY_TEXT_NB'),
            'brand_display_type' => Configuration::get('BRAND_DISPLAY_TYPE'),
            'display_link_brand' => Configuration::get('PS_DISPLAY_MANUFACTURERS'),
        );
    }
}
