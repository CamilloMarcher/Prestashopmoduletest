<?php
//Checks for the version number of Prestashop
if (!defined('_PS_VERSION_')) {
    exit;
}
//Making my class
class MyModule extends Module
{
    public function __construct()
    {
        //naming the module for Prestashop
        $this->name = 'mymodule';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Camillo Marcher';
        $this->need_instance = 0;
        //The versions the module is gonna work for 
        $this->ps_versions_compliancy = [
            'min' => '1.6',
            'max' => _PS_VERSION_
        ];
        //Indicates that the we are using bootstrap
        $this->bootstrap = true;
        //Triggers at lot of functions in Prestashop
        parent::__construct();
        //Naming the app when you are looking at it in Prestashop and a description
        $this->displayName = $this->l('Hallo message');
        $this->description = $this->l('This is a test for a work.');
        //text for when you are uninstalling
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        if (!Configuration::get('MYMODULE_NAME')) {
            $this->warning = $this->l('No name provided');
        }
    }
//Making the install function if anything fails it will return false else true
    public function install()
{
    if (Shop::isFeatureActive()) {
        Shop::setContext(Shop::CONTEXT_ALL);
    }

    if (!parent::install() ||
        !$this->registerHook('displayNav1') ||
        !$this->registerHook('header') ||
        !Configuration::updateValue('MYMODULE_NAME', '')
    ) {
        return false;
    }

    return true;
}
//Making uninstall function to remove everything
    public function uninstall()
{
    if (!parent::uninstall() ||
        !Configuration::deleteByName('MYMODULE_NAME')
    ) {
        return false;
    }

    return true;
}

//Getting the content from the back office and saving it
public function getContent()
{
    $output = null;
    //Validates the input when you press the submit button in the back office
    if (Tools::isSubmit('submit'.$this->name)) {
        //Making the value I get from MYMODULE_NAME into a string
        $myModuleName = strval(Tools::getValue('MYMODULE_NAME'));
        //If it's emtry and making sure that it's doesn't contain special characters
        if ( !$myModuleName || empty($myModuleName) || !Validate::isGenericName($myModuleName))
        //if any of them is true then it output a error
        {
            $output .= $this->displayError($this->l('Invalid Configuration value'));
        }
        //Else it updates the value in the MYMODULE_NAME varible and outputs settings updated
        else {
            Configuration::updateValue('MYMODULE_NAME', $myModuleName);
            $output .= $this->displayConfirmation($this->l('Settings updated'));
        }
    }
    //when you load the page it make a textfield you can write in
    return $output.$this->displayForm();
}
//Display the text field for the back office
public function displayForm()
{
    // Get default language
    $defaultLang = (int)Configuration::get('PS_LANG_DEFAULT');

    // Init Fields form array
    $fieldsForm[0]['form'] = [
        'legend' => [
            'title' => $this->l('Settings'),
        ],
        //the input field
        'input' => [
            [
                'type' => 'text',
                'label' => $this->l('Please write your name'),
                'name' => 'MYMODULE_NAME',
                'size' => 20,
                'required' => true
            ]
        ],
        //submit button
        'submit' => [
            'title' => $this->l('Save'),
            'class' => 'btn btn-default pull-right'
        ]
    ];

    $helper = new HelperForm();

    // Module, token and currentIndex
    $helper->module = $this;
    $helper->name_controller = $this->name;
    $helper->token = Tools::getAdminTokenLite('AdminModules');
    $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

    // Language
    $helper->default_form_language = $defaultLang;
    $helper->allow_employee_form_lang = $defaultLang;

    // Title and toolbar
    $helper->title = $this->displayName;
    $helper->show_toolbar = true;        // false -> remove toolbar
    $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
    $helper->submit_action = 'submit'.$this->name;
    $helper->toolbar_btn = [
        'save' => [
            'desc' => $this->l('Save'),
            'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
            '&token='.Tools::getAdminTokenLite('AdminModules'),
        ],
        'back' => [
            'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
            'desc' => $this->l('Back to list')
        ]
    ];

    // Load current value
    $helper->fields_value['MYMODULE_NAME'] = Configuration::get('MYMODULE_NAME');

    return $helper->generateForm($fieldsForm);
}
//Function for hooking the module to a certien part of the browser
public function hookDisplayNav1($params)
{
    $this->context->smarty->assign([
        'my_module_name' => Configuration::get('MYMODULE_NAME'),
        'my_module_link' => $this->context->link->getModuleLink('mymodule', 'display')
    ]);
    //returns what is in the mymodule.tpl
        return $this->display(__FILE__, 'mymodule.tpl');
}

public function hookDisplayHeader()
{
    $this->context->controller->addCSS($this->_path.'css/mymodule.css', 'all');
}
}
