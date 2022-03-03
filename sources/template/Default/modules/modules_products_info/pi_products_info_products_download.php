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
  use ClicShopping\OM\CLICSHOPPING;
  use ClicShopping\OM\HTML;
  use ClicShopping\OM\HTTP;

  class pi_products_info_products_download {
    public string $code;
    public string $group;
    public $title;
    public $description;
    public ?int $sort_order = 0;
    public bool $enabled = false;

    public function __construct() {
      $this->code = get_class($this);
      $this->group = basename(__DIR__);

      $this->title = CLICSHOPPING::getDef('module_products_info_products_download_name');
      $this->description = CLICSHOPPING::getDef('module_products_info_products_download_name_description');

      if (\defined('MODULE_PRODUCTS_INFO_PRODUCTS_DOWNLOAD_STATUS')) {
        $this->sort_order = MODULE_PRODUCTS_INFO_PRODUCTS_DOWNLOAD_SORT_ORDER;
        $this->enabled = (MODULE_PRODUCTS_INFO_PRODUCTS_DOWNLOAD_STATUS == 'True');
      }
    }

    public function execute() {
      $CLICSHOPPING_ProductsCommon = Registry::get('ProductsCommon');

      if ($CLICSHOPPING_ProductsCommon->getID() && isset($_GET['Products'])) {
        $content_width = (int)MODULE_PRODUCTS_INFO_PRODUCTS_DOWNLOAD_CONTENT_WIDTH;
        $text_position = MODULE_PRODUCTS_INFO_PRODUCTS_DOWNLOAD_POSITION;

        $CLICSHOPPING_Db = Registry::get('Db');
        $CLICSHOPPING_Template = Registry::get('Template');

        $Qproducts = $CLICSHOPPING_Db->prepare('select p.products_download_filename,
                                                       p.products_download_public
                                                from :table_products p,
                                                     :table_products_to_categories p2c,
                                                     :table_categories c                                                
                                                where p.products_status = 1
                                                and p.products_id = :products_id
                                                and p.products_view = 1
                                                and p.products_id = p2c.products_id
                                                and p2c.categories_id = c.categories_id
                                                and c.status = 1       
                                             ');

        $Qproducts->bindInt(':products_id', $CLICSHOPPING_ProductsCommon->getID());

        $Qproducts->execute();

        $products_download_filename = $Qproducts->value('products_download_filename');
        $products_download_public = $Qproducts->value('products_download_public');

        if (!empty($products_download_filename) && $products_download_public == '1') {
          $url = HTML::link(HTTP::getShopUrlDomain() . 'sources/Download/public/' . $products_download_filename, CLICSHOPPING::getDef('text_products_download_filename'));

          $products_products_download_content = '<!-- Start products_produtcs_download-->' . "\n";

          ob_start();
          require_once($CLICSHOPPING_Template->getTemplateModules($this->group . '/content/products_info_products_download'));

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
      return \defined('MODULE_PRODUCTS_INFO_PRODUCTS_DOWNLOAD_STATUS');
    }

    public function install() {
      $CLICSHOPPING_Db = Registry::get('Db');

      $CLICSHOPPING_Db->save('configuration', [
          'configuration_title' => 'Do you want to enable this module ?',
          'configuration_key' => 'MODULE_PRODUCTS_INFO_PRODUCTS_DOWNLOAD_STATUS',
          'configuration_value' => 'True',
          'configuration_description' => 'Do you want to enable this module in your shop ?',
          'configuration_group_id' => '6',
          'sort_order' => '1',
          'set_function' => 'clic_cfg_set_boolean_value(array(\'True\', \'False\'))',
          'date_added' => 'now()'
        ]
      );

      $CLICSHOPPING_Db->save('configuration', [
          'configuration_title' => 'Please select the width of the display?',
          'configuration_key' => 'MODULE_PRODUCTS_INFO_PRODUCTS_DOWNLOAD_CONTENT_WIDTH',
          'configuration_value' => '12',
          'configuration_description' => 'Please enter a number between 1 and 12',
          'configuration_group_id' => '6',
          'sort_order' => '1',
          'set_function' => 'clic_cfg_set_content_module_width_pull_down',
          'date_added' => 'now()'
        ]
      );

      $CLICSHOPPING_Db->save('configuration', [
          'configuration_title' => 'Where do you want to display the barcode?',
          'configuration_key' => 'MODULE_PRODUCTS_INFO_PRODUCTS_DOWNLOAD_POSITION',
          'configuration_value' => 'none',
          'configuration_description' => 'Displays the product to the left or right',
          'configuration_group_id' => '6',
          'sort_order' => '2',
          'configuration_value' => 'none',
          'date_added' => 'now()'
        ]
      );

      $CLICSHOPPING_Db->save('configuration', [
          'configuration_title' => 'Sort order',
          'configuration_key' => 'MODULE_PRODUCTS_INFO_PRODUCTS_DOWNLOAD_SORT_ORDER',
          'configuration_value' => '101',
          'configuration_description' => 'Sort order of display. Lowest is displayed first. The sort order must be different on every module',
          'configuration_group_id' => '6',
          'sort_order' => '3',
          'set_function' => '',
          'date_added' => 'now()'
        ]
      );
    }

    public function remove() {
      return Registry::get('Db')->exec('delete from :table_configuration where configuration_key in ("' . implode('", "', $this->keys()) . '")');
    }

    public function keys() {
      return array(
        'MODULE_PRODUCTS_INFO_PRODUCTS_DOWNLOAD_STATUS',
        'MODULE_PRODUCTS_INFO_PRODUCTS_DOWNLOAD_CONTENT_WIDTH',
        'MODULE_PRODUCTS_INFO_PRODUCTS_DOWNLOAD_POSITION',
        'MODULE_PRODUCTS_INFO_PRODUCTS_DOWNLOAD_SORT_ORDER'
      );
    }
  }
