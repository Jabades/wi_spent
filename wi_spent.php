<?php
/**
 * 2007-2021 PrestaShop
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
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2021 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Wi_spent extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'wi_spent';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Jesús Abades';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Webimpacto Customers spent');
        $this->description = $this->l('Remain how much have spent your customers and gift coupons to premium customers.');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        include dirname(__FILE__) . '/sql/install.php';
        Configuration::updateValue('WI_SPENT_ENABLED', '0');
        Configuration::updateValue('WI_SPENT_AMOUNT', '');
        Configuration::updateValue('WI_SPENT_COUPON', '');
        Configuration::updateValue('WI_SPENT_DAYS', '30');
        return parent::install() &&
        $this->registerHook('header') &&
        $this->registerHook('backOfficeHeader') &&
        $this->registerHook('actionValidateOrder') &&
        $this->registerHook('postUpdateOrderStatus') &&
        $this->registerHook('actionOrderStatusPostUpdate') &&
        $this->registerHook('displayOrderConfirmation');
    }

    public function uninstall()
    {
        include dirname(__FILE__) . '/sql/uninstall.php';
        Configuration::deleteByName('WI_SPENT_ENABLED');
        Configuration::deleteByName('WI_SPENT_AMOUNT');
        Configuration::deleteByName('WI_SPENT_COUPON');
        Configuration::deleteByName('WI_SPENT_DAYS');
        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool) Tools::isSubmit('submitWi_spentModule')) == true) {
            $this->postProcess();
        }
        switch (Tools::getValue('tab_sec')) {
            case 'list':
                $html = $this->getList();
                break;
            case 'help':
                $html = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/help.tpl');
                break;
            default:
                $html = $this->renderForm();
                break;
        }

        $params = array(
            'wi_spent' => array(
                'module_dir' => $this->_path,
                'module_name' => $this->name,
                'base_url' => _MODULE_DIR_ . $this->name . '/',
                'iso_code' => $this->context->language->iso_code,
                'menu' => $this->getMenu(),
                'html' => $html,
                'errors' => empty($this->errors) ? array() : $this->errors,
            ),
        );

        $this->context->smarty->assign($params);

        $header = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/header.tpl');
        $body = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/body.tpl');
        $footer = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/footer.tpl');

        return $header . $body . $footer;
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitWi_spentModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
        . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    protected function getMenu()
    {
        $tab = Tools::getValue('tab_sec');
        $tab_link = $this->context->link->getAdminLink('AdminModules', true)
        . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name . '&tab_sec=';
        return array(
            array(
                'label' => $this->l('Configure Amounts'),
                'link' => $tab_link . 'edit',
                'active' => ($tab == 'edit' || empty($tab) ? 1 : 0),
            ),
            array(
                'label' => $this->l('List of coupons'),
                'link' => $tab_link . 'list',
                'active' => ($tab == 'list' ? 1 : 0),
            ),
            array(
                'label' => $this->l('Help'),
                'link' => $tab_link . 'help',
                'active' => ($tab == 'help' ? 1 : 0),
            ),
        );
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'col' => 2,
                        'type' => 'text',
                        'suffix' => '<i class="icon icon-euro"></i>',
                        'required' => true,
                        'label' => $this->l('Customer must spent'),
                        'desc' => $this->l('Enter the amount which your customers have to sepent to receibe the coupon.'),
                        'name' => 'WI_SPENT_AMOUNT',
                    ),
                    array(
                        'col' => 2,
                        'type' => 'text',
                        'suffix' => '<i class="icon icon-euro"></i>',
                        'required' => true,
                        'label' => $this->l('Amount of the coupon'),
                        'desc' => $this->l('Enter the amount of the coupon for gift to your customers.'),
                        'name' => 'WI_SPENT_COUPON',
                    ),
                    array(
                        'col' => 1,
                        'type' => 'text',
                        'required' => true,
                        'label' => $this->l('Coupon validity in days'),
                        'desc' => $this->l('Enter the number of days while the coupon will be valid.'),
                        'name' => 'WI_SPENT_DAYS',
                    ),
                    array(
                        'col' => 6,
                        'type' => 'switch',
                        'label' => $this->l('Enabled'),
                        'name' => 'WI_SPENT_ENABLED',
                        'desc' => $this->l('Enable or disable this feature.'),
                        'values' => array(
                            array('value' => 1, 'name' => $this->l('Yes')),
                            array('value' => 0, 'name' => $this->l('No')),
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        $formValues = array(
            'WI_SPENT_AMOUNT' => Configuration::get('WI_SPENT_AMOUNT', ''),
            'WI_SPENT_COUPON' => Configuration::get('WI_SPENT_COUPON', ''),
            'WI_SPENT_ENABLED' => Configuration::get('WI_SPENT_ENABLED', ''),
            'WI_SPENT_DAYS' => Configuration::get('WI_SPENT_DAYS', ''),
        );
        return $formValues;
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        if (Tools::getValue('submitWi_spentModule')) {
            $formValues = $this->getConfigFormValues();
            foreach (array_keys($formValues) as $key) {
                Configuration::updateValue($key, Tools::getValue($key));
            }
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name || Tools::getValue('configure') == $this->name) {
            $this->context->controller->addJS($this->_path . 'views/js/back.js');
            $this->context->controller->addCSS($this->_path . 'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        if ($this->context->controller instanceof OrderConfirmationController) {
            $this->context->controller->addJS($this->_path . '/views/js/scratch-card.js');
            $this->context->controller->addCSS($this->_path . '/views/css/front.css');
        }
    }

    public function hookActionOrderStatusPostUpdate($params)
    {
        $this->hookActionValidateOrder($params);
    }

    public function hookPostUpdateOrderStatus($params)
    {
        $this->hookActionValidateOrder($params);
    }    

    public function hookActionValidateOrder($params)
    {
        $amount_target = Configuration::get('WI_SPENT_AMOUNT');
        $reward = Tools::ps_round(Configuration::get('WI_SPENT_COUPON'), 2);
        if (isset($params['id_order'])) {
            $order = new Order($params['id_order']);
            $id_customer = $order->id_customer;
            $customer = new Customer($id_customer);
            $email = $customer->email;
            $id_lang = $customer->id_lang;
        } else if (isset($params['order'])) {
            $id_customer = $params['order']->id_customer;
            $email = $params['customer']->email;
            $id_lang = $params['customer']->id_lang;
        } else {
            return false;
        }
        if (!empty($id_customer) &&
            !empty($email) &&
            !empty($id_lang) &&
            Configuration::get('WI_SPENT_ENABLED') &&
            (float) $amount_target > 0 &&
            (float) $reward > 0
        ) {
            
            if (!empty($id_customer) && !$this->haveCoupon($id_customer)) {
                $send = true;
                $amount = $this->getAmount($id_customer);
                if ((float) $amount > 0) {
                    if ((float) $amount >= (float) $amount_target) {
                        $coupon = $this->createCoupon($id_customer);
                        if (!$coupon) {
                            $send = false;
                        } else {
                            $subject = $this->l('Congratulations you have got your discount coupon');
                            $template = 'coupon';
                            $templateVars = array(
                                '{coupon}' => $coupon,
                                '{reward}' => $reward,
                            );
                        }
                    } else {
                        $subject = $this->l('Your amount spent to date');
                        $template = 'amount';
                        $templateVars = array(
                            '{amount}' => $amount,
                        );
                    }
                    if ($send) {
                        $this->sendMail(
                            $subject,
                            $email,
                            $id_lang,
                            $template,
                            $templateVars
                        );
                    }
                }
            }
        }
    }

    public function hookDisplayOrderConfirmation($params)
    {
        if (empty($params['order']->id_customer)) {
            return ;            
        }
        $coupon = $this->getCoupon($params['order']->id_customer);
        if (!empty($coupon['code'])) {
            $amount = strpos($coupon['amount'], '.') !== false
                ? Tools::ps_round($coupon['amount'], 2)
                : $coupon['amount'];
            $params = array(
                'wi_spent' => array(
                    'code' => $coupon['code'],
                    'amount' => $amount
                )
            );
            $this->context->smarty->assign($params);
            return $this->context->smarty->fetch(
                $this->local_path . 'views/templates/hook/displayOrderConfirmation.tpl'
            );
        }
    }

    protected function createCoupon($id_customer = null)
    {
        if ((float) Configuration::get('WI_SPENT_AMOUNT') > 0) {
            $days = Configuration::get('WI_SPENT_DAYS')
                ? Configuration::get('WI_SPENT_DAYS')
                : 30;
            $date = new DateTime();
            $date_from = $date->format('Y-m-d H:i:s');
            $date = new DateTime();
            $date->add(new DateInterval('P' . (int) $days . 'D'));
            $date_to = $date->format('Y-m-d H:i:s');
            $cr = new CartRule();
            $languages = Language::getLanguages();
            foreach ($languages as $lang) {
                $cr->name[$lang['id_lang']] = $this->l('PREMIUM CUSTOMER');
            }
            $cr->id_customer = $id_customer;
            $cr->code = $this->codeGen(4, 3);
            $cr->reduction_amount = Tools::ps_round((float) Configuration::get('WI_SPENT_COUPON'), 2);
            $cr->partial_use = 0;
            $cr->priority = 1;
            $cr->active = 1;
            $cr->highlight = 1;
            $cr->reduction_tax = 1;
            $cr->date_from = $date_from;
            $cr->date_to = $date_to;
            $cr->add();
            $sql = 'INSERT INTO `' . _DB_PREFIX_ . 'wi_spent_coupons` 
                (`id_customer`,`id_cart_rule`,`code`,`date_add`) 
                    VALUES 
                (' . (int) $id_customer. ',' . (int)$cr->id . ',"' . pSQL($cr->code). '",NOW())';
            Db::getInstance()->execute($sql);
            return $cr->code;
        }
        return false;
    }

    protected function getCoupon($id_customer = null)
    {
        $sql = 'SELECT wsc.`code`,cr.`reduction_amount` AS `amount` FROM `' . _DB_PREFIX_ . 'wi_spent_coupons` wsc 
            JOIN `' . _DB_PREFIX_ . 'cart_rule` cr ON cr.`id_cart_rule` = wsc.`id_cart_rule` 
            WHERE wsc.`id_customer` = ' . (int) $id_customer;
        return Db::getInstance()->getRow($sql);
    }

    protected function codeGen(
        $lenght = 4,
        $blocks = 4,
        $pattern = '1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ',
        $slug = '-'
    ) {
        $max = strlen($pattern) - 1;
        $blocks_arr = array();
        for ($i = 0; $i < $blocks; $i++) {
            $key = '';
            for ($z = 0; $z < $lenght; $z++) {
                $key .= $pattern[mt_rand(0, $max)];
            }
            $blocks_arr[] = $key;
        }
        return implode($slug, $blocks_arr);
    }

    protected function haveCoupon($id_customer = null)
    {
        $sql = 'SELECT 1 FROM `' . _DB_PREFIX_ . 'wi_spent_coupons` WHERE `id_customer` = ' . (int) $id_customer;
        return Db::getInstance()->getValue($sql);
    }

    protected function getAmount($id_customer = null)
    {
        $sql = 'SELECT SUM(`total_paid`) AS `total` FROM `' . _DB_PREFIX_ . 'orders`
            WHERE `id_customer` = ' . (int) $id_customer . ' AND `current_state` IN(
                SELECT DISTINCT `id_order_state` FROM `' . _DB_PREFIX_ . 'order_state`
                WHERE `logable` = 1
            )';
        return Db::getInstance()->getValue($sql);
    }

    protected function sendMail(
        $subject = null,
        $email = null,
        $id_lang = null,
        $template = 'amount',
        $templateVars = array()
    ) {
        Mail::Send(
            $id_lang, // id_lang
            $template, // template
            $this->l('Your amount spent to date'), // subject
            $templateVars, // templateVars
            $email, // to
            null, // To Name
            null, // From
            null, // From Name
            null, // Attachment
            null, // SMTP
            _PS_MODULE_DIR_ . $this->name . '/mails/'
        );
    }

    protected function getList()
    {        
        $sort = Tools::getValue('sort') ? Tools::getValue('sort') : 'wsc.`date_add`';
        $mode = Tools::getValue('mode') ? Tools::getValue('mode') : 'DESC';
        $limit = Tools::getValue('limit') ? Tools::getValue('limit') : 10;
        $page = Tools::getValue('page') ? Tools::getValue('page') : 1;
        $offset = ($page * $limit) - $limit;
        $link = $this->context->link->getAdminLink('AdminModules', true) .
            '&configure=' . $this->name . '&tab_module=' . $this->tab .
            '&module_name=' . $this->name . '&tab_sec=list&limit=' . $limit;        
        switch ($sort) {
            case 'id_customer':
                $sort_by = 'wsc.`id_customer`';
                break;
            case 'name':
                $sort_by = 'c.`firstname`';
                break;
            case 'date_add':
            default:
                $sort_by = 'wsc.`date_add`';
                break;
        }
        $sql = 'SELECT 
                wsc.`id_customer`,
                c.`email`,
                CONCAT(c.`firstname`, "", c.`lastname`) AS `name`,
                cr.`code`,
                DATE_FORMAT(wsc.`date_add`, "%d/%m/%Y") AS `date_add` 
            FROM `' . _DB_PREFIX_ . 'wi_spent_coupons` wsc 
            JOIN `' . _DB_PREFIX_ . 'cart_rule` cr ON cr.`id_cart_rule` = wsc.`id_cart_rule` 
            JOIN `' . _DB_PREFIX_ . 'customer` c ON c.`id_customer` = wsc.`id_customer` 
            WHERE wsc.`id_customer` > 0 ';
        if (Tools::getValue('filters')) {
            $filters = Tools::getValue('filters');
            if (!empty($filters['id_customer'])) {
				$sql.= ' AND wsc.`id_customer` = "' . (int) $filters['id_customer'] . '%" ';
			}
			if (!empty($filters['name'])) {
				$sql.= ' AND c.`firstname` LIKE "' . pSQL($filters['name']) . '%" ';
			}
			if (!empty($filters['email'])) {
				$sql.= ' AND c.`email` = "' . pSQL($filters['email']) . '" ';
			}
            if (!empty($filters['code'])) {
				$sql.= ' AND wsc.`code` = "' . (int) $filters['code'] . '%" ';
			}
        }
        $sql.= ' ORDER BY ' . pSQL($sort_by) . ' ' . pSQL($mode) . ' LIMIT '. (int) $offset . ',' . (int) $limit;
        $rows = Db::getInstance()->executeS($sql);
        $total = (int) Db::getInstance()->getValue('SELECT FOUND_ROWS() AS `total`');
        $pages = ceil($total / $limit);
        $params = array(
            'wi_spent' => array(
                'coupons' => $rows,
                'pages' => $pages,
                'page' => $page,
                'page_size' => $limit,
                'total' => $total,
                'disable_save' => true,
                'mode' => Tools::strtolower($mode),
                'sort' => Tools::strtolower($sort),
                'link' => $link
            )
        );
        $this->context->smarty->assign($params);
        return $this->context->smarty->fetch($this->local_path . 'views/templates/admin/list.tpl');
    }
}
