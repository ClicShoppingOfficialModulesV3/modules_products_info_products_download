<?php
  /**
 *
 *  @copyright 2008 - https://www.clicshopping.org
 *  @Brand : ClicShopping(Tm) at Inpi all right Reserved
 *  @Licence GPL 2 & MIT
 *  @licence MIT - Portion of osCommerce 2.4
 *  @Info : https://www.clicshopping.org/forum/trademark/
 *
 */

  use ClicShopping\OM\Registry;
  use ClicShopping\OM\HTML;
  use ClicShopping\OM\CLICSHOPPING;

  class pi_products_info_products_download {
    public $code;
    public $group;
    public $title;
    public $description;
    public $sort_order;
    public $enabled = false;

    public function __construct() {
      $this->code = get_class($this);
      $this->group = basename(__DIR__);

      $this->title = CLICSHOPPING::getDef('module_products_info_products_download_name');
      $this->description = CLICSHOPPING::getDef('module_products_info_products_download_name_description');

      if (defined('MODULE_PRODUCTS_INFO_PRODUCTS_DOWNLOAD_STATUS')) {
        $this->sort_order = MODULE_PRODUCTS_INFO_PRODUCTS_DOWNLOAD_SORT_ORDER;
        $this->enabled = (MODULE_PRODUCTS_INFO_PRODUCTS_DOWNLOAD_STATUS == 'True');
      }
    }

    public function execute() {

      if (isset($_GET['products_id']) && isset($_GET['Products']) ) {
        $content_width = (int)MODULE_PRODUCTS_INFO_PRODUCTS_DOWNLOAD_CONTENT_WIDTH;
        $text_position = MODULE_PRODUCTS_INFO_PRODUCTS_DOWNLOAD_POSITION;

        $CLICSHOPPING_Db = Registry::get('Db');
        $CLICSHOPPING_Template = Registry::get('Template');

        $Qproducts = $CLICSHOPPING_Db->prepare('select p.products_download_filename,
                                                 p.products_download_public
                                          from :table_products p
                                          where p.products_status = :products_status
                                          and p.products_id = :products_id
                                          and p.products_view = :products_view
                                       ');

        $Qproducts->bindInt(':products_id',   (int)$_GET['products_id'] );

        $Qproducts->bindValue(':products_status',   '1' );
        $Qproducts->bindValue(':products_view', '1' );

        $Qproducts->execute();

        $products = $Qproducts->fetch();

        $products_download_filename = $products['products_download_filename'];
        $products_download_public = $products['products_download_public'];

        if (!empty($products_download_filename) && $products_download_public == '1') {

          $url = '<a href="'. 'sources/Download/Public/' . $products['products_download_filename'] . '"> '. CLICSHOPPING::getDef('text_products_download') . '</a>';

          $products_products_download_content = '<!-- Start products_produtcs_download-->' . "\n";

          ob_start();
          require($CLICSHOPPING_Template->getTemplateModules($this->group . '/content/products_info_products_download'));

          $products_products_download_content .= ob_get_clean();

          $products_products_download_content .= '<!-- end products_produtcs_download -->' . "\n";

          $CLICSHOPPING_Template->addBlock($products_products_download_content, $this->group);
        }
      }  // end empty
    } // public function execute

    public function isEnabled() {
      return $this->enabled;
    }

    public function check() {
      return defined('MODULE_PRODUCTS_INFO_PRODUCTS_DOWNLOAD_STATUS');
    }

    public function install() {
      $CLICSHOPPING_Db = Registry::get('Db');

      $CLICSHOPPING_Db->save('configuration', [
          'configuration_title' => 'Souhaitez-vous activer ce module ?',
          'configuration_key' => 'MODULE_PRODUCTS_INFO_PRODUCTS_DOWNLOAD_STATUS',
          'configuration_value' => 'True',
          'configuration_description' => 'Souhaitez vous activer ce module à votre boutique ?',
          'configuration_group_id' => '6',
          'sort_order' => '1',
          'set_function' => 'clic_cfg_set_boolean_value(array(\'True\', \'False\'))',
          'date_added' => 'now()'
        ]
      );

      $CLICSHOPPING_Db->save('configuration', [
          'configuration_title' => 'Veuillez selectionner la largeur de l\'affichage?',
          'configuration_key' => 'MODULE_PRODUCTS_INFO_PRODUCTS_DOWNLOAD_CONTENT_WIDTH',
          'configuration_value' => '12',
          'configuration_description' => 'Veuillez indiquer un nombre compris entre 1 et 12',
          'configuration_group_id' => '6',
          'sort_order' => '1',
          'set_function' => 'clic_cfg_set_content_module_width_pull_down',
          'date_added' => 'now()'
        ]
      );

      $CLICSHOPPING_Db->save('configuration', [
          'configuration_title' => 'A quel endroit souhaitez-vous afficher le code barre ?',
          'configuration_key' => 'MODULE_PRODUCTS_INFO_PRODUCTS_DOWNLOAD_POSITION',
          'configuration_value' => 'none',
          'configuration_description' => 'Affiche le code barre du produit à gauche ou à droite<br><br><i>(Valeur Left = Gauche <br>Valeur Right = Droite <br>Valeur None = Aucun)</i>',
          'configuration_group_id' => '6',
          'sort_order' => '2',
          'set_function' => 'clic_cfg_set_boolean_value(array(\'float-md-right\', \'float-md-left\', \'float-md-none\'),',
          'date_added' => 'now()'
        ]
      );

      $CLICSHOPPING_Db->save('configuration', [
          'configuration_title' => 'Ordre de tri d\'affichage',
          'configuration_key' => 'MODULE_PRODUCTS_INFO_PRODUCTS_DOWNLOAD_SORT_ORDER',
          'configuration_value' => '100',
          'configuration_description' => 'Ordre de tri pour l\'affichage (Le plus petit nombre est montré en premier)',
          'configuration_group_id' => '6',
          'sort_order' => '3',
          'set_function' => '',
          'date_added' => 'now()'
        ]
      );

      return $CLICSHOPPING_Db->save('configuration', ['configuration_value' => '1'],
                                              ['configuration_key' => 'WEBSITE_MODULE_INSTALLED']
                            );
    }

    public function remove() {
      return Registry::get('Db')->exec('delete from :table_configuration where configuration_key in ("' . implode('", "', $this->keys()) . '")');
    }

    public function keys() {
      return array (
        'MODULE_PRODUCTS_INFO_PRODUCTS_DOWNLOAD_STATUS',
        'MODULE_PRODUCTS_INFO_PRODUCTS_DOWNLOAD_CONTENT_WIDTH',
        'MODULE_PRODUCTS_INFO_PRODUCTS_DOWNLOAD_POSITION',
        'MODULE_PRODUCTS_INFO_PRODUCTS_DOWNLOAD_SORT_ORDER'
      );
    }
  }
