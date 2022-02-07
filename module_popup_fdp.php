<?php
 
    if (!defined('_PS_VERSION_')) {
        exit;
    }

    class Module_Popup_Fdp extends Module
    {

        /////////////////////////////////////////////////////////////////////CONSTRUCTEUR DE LA CLASSE//////////////////////////////////////////////////////////////////////////////
        public function __construct()
        {
            $this->name = 'module_popup_fdp';
            $this->tab = 'front_office_features';
            $this->version = '1.0.0';
            $this->author = 'Geoffrey';
            $this->need_instance = 0;
            $this->ps_versions_compliancy = [
                'min' => '1.7',
                'max' => _PS_VERSION_
            ];
            $this->bootstrap = true;
     
            parent::__construct();
     
            $this->displayName = $this->l('Module popup frais de ports');
            $this->description = $this->l('Ce module permet de générer un popup informant sur le montant restant avant de bénéficier des frais de ports gratuits');
     
            $this->confirmUninstall = $this->l('Êtes-vous sûr de vouloir désinstaller ce module ?');
     
            if (!Configuration::get('MODULE_POPUP_FDP_FRAIS')) {
                $this->warning = $this->l('Aucun nom fourni');
            }
        }

////////////////////////////////////////////////////////////////////////////////FONCTION D'INSTALLATION/////////////////////////////////////////////////////////////////////////////////////////
                    public function install()
            {
                if (Shop::isFeatureActive()) {
                    Shop::setContext(Shop::CONTEXT_ALL);
                }
             
                if (!parent::install() ||
                    !$this->registerHook('displayCartModalFooter') ||
                    !$this->registerHook('header') ||
                    !Configuration::updateValue('MODULE_POPUP_FDP_FRAIS', 60) ////UPDATE DE MODULE_POPUP_FDP_FRAIS EN BDD
                ) {
                    return false;
                }
             
                return true;
            }
//////////////////////////////////////////////////////////////////////////////////FONCTION DE DÉSINSTALLATION//////////////////////////////////////////////////////////////////////////////////////////////////////
            public function uninstall()
            {
                if (!parent::uninstall() ||
                !Configuration::deleteByName('MODULE_POPUP_FDP_FRAIS')
            ) {
                return false;
            }
         
            return true;
            }

///////////////////////////////////////////////////////////////////////////FONCTION GETCONTENT/////////////////////////////////////////////////////////////////////////////////////////////
            public function getContent()
{
    $output = null;
 
    if (Tools::isSubmit('btnSubmit')) {
        $pageName = intval(Tools::getValue('MODULE_POPUP_FDP_FRAIS')); // VA CHERCHER LA VALUE DE MODULE_POPUP_FDP_FRAIS + inval
 
        if (
            !$pageName||
            empty($pageName)
        ) {
            $output .= $this->displayError($this->l('Invalid Configuration value'));
        } else {
            Configuration::updateValue('MODULE_POPUP_FDP_FRAIS', $pageName);        //MODIFIE LA VALEUR DE MODULE_POPUP_FDP_FRAIS
            $output .= $this->displayConfirmation($this->l('Le montant a bien été modifié'));
        }
    }
 
    return $output.$this->displayForm();
}

//////////////////////////////////////////////////////////////////////////////////FONCTION DISPLAYFORM ///////////////////////////////////////////////////////////////////////////
public function displayForm()
{
    // Récupère la langue par défaut
    $defaultLang = (int)Configuration::get('PS_LANG_DEFAULT');
  
    // Initialise les champs du formulaire dans un tableau
    $form = array(
    'form' => array(
        'legend' => array(
            'title' => $this->l('Définir le montant'),
        ),
        'input' => array(
            array(
                'type' => 'text',
                'label' => $this->l('Montant pour frais de ports gratuits :'),
                'name' => 'MODULE_POPUP_FDP_FRAIS',
                'size' => 20,
                'required' => true,
            )
        ),
        'submit' => array(
            'title' => $this->l('Save'),
            'name'  => 'btnSubmit'
        )
    ),
);
  
    $helper = new HelperForm();
  
    // Module, token et currentIndex
    $helper->module = $this;
    $helper->name_controller = $this->name;
    $helper->token = Tools::getAdminTokenLite('AdminModules');
    $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
  
    // Langue
    $helper->default_form_language = $defaultLang;
  
    // Charge la valeur de MODULE_POPUP_FDP_FRAIS depuis la base
    $helper->fields_value['MODULE_POPUP_FDP_FRAIS'] = Configuration::get('MODULE_POPUP_FDP_FRAIS');
  
    return $helper->generateForm(array($form));
}

/////////////////////////////////////////////////////////////FONCTION POUR AFFICHER DANS LA BOUTIQUE///////////////////////////////////////////////////////////////////////////////////

public function hookDisplayCartModalFooter($params)
{
    $this->context->smarty->assign([
        'fdp_offert' => Configuration::get('MODULE_POPUP_FDP_FRAIS'), //je passe la value de MODULE_POPUP_FDP_FRAIS dans le tpl
        'module_popup_fdp_page_link' => $this->context->link->getModuleLink('module_popup_fdp', 'display'),
      ]);


      return $this->display(__FILE__, 'module_popup_fdp.tpl');
}

public function hookDisplayHeader()
{
$this->context->controller->registerStylesheet(
  'module_popup_fdp',
  $this->_path.'views/css/module_popup_fdp.css',
  ['server' => 'remote', 'position' => 'head', 'priority' => 150]
);
}

    }

?>