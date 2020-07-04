<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class Addhotjarbasic extends Module
{
    private $_postErrors = array();

    public function __construct()
    {
        $this->name = 'addhotjarbasic';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Łukasz Ryszkiewicz';
        $this->author_uri = 'https://ryszkiewicz.cloud';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array(
            'min' => '1.7',
            'max' => _PS_VERSION_,
        );
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->l('Add Hotjar Basic JavaScript');
        $this->description = $this->l('The module adds Hotjar Basic JavaScript code to the Prestashop store without having to edit the files.');
        $this->confirmUninstall = $this->l('Uninstall module?');

        $this->defaults = array(
            'ADDHOTJARBASIC_ENABLED', 0,
            'ADDHOTJARBASIC_CONTAINER_ID', null,
        );
    }

    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        $module_hooks = array(
            'header',
        );

        if (!parent::install()
            || !$this->setDefaults()
            || !$this->registerHook($module_hooks)
        ) {
            return false;
        }

        return true;
    }

    public function uninstall()
    {
        Configuration::deleteByName('ADDHOTJARBASIC_ENABLED');
        Configuration::deleteByName('ADDHOTJARBASIC_CONTAINER_ID');

        return parent::uninstall();
    }

    public function setDefaults()
    {
        foreach ($this->defaults as $default => $value) {
            Configuration::updateValue('ADDHOTJARBASIC_'.$default, $value);
        }

        return true;
    }

    private function _postValidation()
    {
        if (!preg_match('/^[0-9]{6,9}$/i', Tools::getValue('ADDHOTJARBASIC_CONTAINER_ID'))) {
            return false;
        } else {
            return true;
        }
    }

    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submit_'.$this->name)) {
            if ($this->_postValidation()) {
                if ($this->postProcess()) {
                    $output .= $this->displayConfirmation($this->l('Settings saved'));
                }
            } else {
                $output .= $this->displayWarning($this->l('Something went wrong! Check form values.'));
            }
        }

        $vars = array(
            $this->name.'_name' => $this->displayName,
            $this->name.'_version' => $this->version,
            $this->name.'_short_desc' => $this->description,
            $this->name.'_logo' => $this->getPathUri().'/logo.png',
        );

        $this->context->smarty->assign($vars);

        $output .= $this->context->smarty->fetch($this->local_path.'views/templates/admin/header.tpl');
        $output .= $this->displayForm();

        return $output;
    }

    public function displayForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Module settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    // Switch module Enabled / Disabled
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Enable module'),
                        'name' => 'ADDHOTJARBASIC_ENABLED',
                        'is_bool' => true,
                        'desc' => $this->l('Set "Yes" to add Hotjar Basic code to your store.')
                            .'<br>'
                            .$this->l('This will insert basic code in <head>'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('On'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Off'),
                            ),
                        ),
                    ),
                    array(
                        'col' => 2,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-google "></i>',
                        'desc' => $this->l('You Hotjar Tracking ID here'),
                        'name' => 'ADDHOTJARBASIC_CONTAINER_ID',
                        'label' => $this->l('Container ID:'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );

        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper = new HelperForm();
        $helper->show_toolbar = true;
        $helper->toolbar_scroll = true;
        $helper->table = $this->table;
        $helper->default_form_language = $lang->id;
        $helper->module = $this;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submit_'.$this->name;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->fields_value['ADDHOTJARBASIC_ENABLED'] = Configuration::get('ADDHOTJARBASIC_ENABLED');
        $helper->fields_value['ADDHOTJARBASIC_CONTAINER_ID'] = Configuration::get('ADDHOTJARBASIC_CONTAINER_ID');

        return $helper->generateForm(array($fields_form));
    }

    protected function postProcess()
    {
        if (
            Configuration::updateValue('ADDHOTJARBASIC_ENABLED', (bool) Tools::getValue('ADDHOTJARBASIC_ENABLED'))
            && Configuration::updateValue('ADDHOTJARBASIC_CONTAINER_ID', Tools::getValue('ADDHOTJARBASIC_CONTAINER_ID'))
            ) {
            return true;
        } else {
            return false;
        }
    }

    public function hookDisplayHeader()
    {
        if ((bool) Configuration::get('ADDHOTJARBASIC_ENABLED')) {
            $vars = [
                'addhotjarbasic_enabled' => (bool) Configuration::get('ADDHOTJARBASIC_ENABLED'),
                'addhotjarbasic_container_id' => Configuration::get('ADDHOTJARBASIC_CONTAINER_ID'),
            ];

            $this->context->smarty->assign($vars);

            return $this->fetch('module:'.$this->name.'/views/templates/hook/head.tpl');
        }
    }
}
