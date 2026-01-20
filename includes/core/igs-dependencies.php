<?php
/**
 * Dependency Manager for IGS Client System.
 *
 * Handles plugin dependency checks and displays admin notices for missing dependencies.
 * Inspired by WC_Subscriptions_Dependency_Manager.
 *
 * @since      1.0.0
 * @package    IGS_Client_System
 * @subpackage IGS_Client_System/includes/core
 */
class IGS_CS_Dependency_Manager {

  /**
   * Array of required plugin dependencies.
   *
   * @since 1.0.0
   * @access private
   * @var array $dependencies Plugin dependencies configuration.
   */
  private $dependencies = array();

  /**
   * Array of missing dependencies.
   *
   * @since 1.0.0
   * @access private
   * @var array $missing Missing dependencies.
   */
  private $missing = array();

  /**
   * Whether plugin.php has been loaded.
   *
   * @since 1.0.0
   * @access private
   * @var bool $plugin_functions_loaded
   */
  private $plugin_functions_loaded = false;

  /**
   * Constructor.
   *
   * @since 1.0.0
   */
  public function __construct() {
    $this->define_dependencies();
    $this->check_dependencies();
  }

  /**
   * Define all required plugin dependencies.
   *
   * @since 1.0.0
   * @access private
   */
  private function define_dependencies() {
    $this->dependencies = array(
      'woocommerce' => array(
        'name'              => 'WooCommerce',
        'main_file'         => 'woocommerce/woocommerce.php',
        'alternative_match' => array(
          'file' => 'woocommerce.php',
          'name' => 'WooCommerce'
        ),
        'min_version'       => '10.4.3'
      ),
      'woocommerce_subscriptions' => array(
        'name'              => 'WooCommerce Subscriptions',
        'main_file'         => 'woocommerce-subscriptions/woocommerce-subscriptions.php',
        'alternative_match' => null,
        'min_version'       => '8.3.1'
      )
    );
  }

  /**
   * Check all plugin dependencies.
   *
   * @since 1.0.0
   * @access private
   */
  private function check_dependencies() {
    $this->missing = array();

    foreach ($this->dependencies as $key => $dep) {
      $plugin_info = $this->get_plugin_info($dep);
      
      if (!$plugin_info['active']) {
        // Plugin не е активен
        $this->missing[$key] = array_merge($dep, array(
          'status'          => 'inactive',
          'current_version' => $plugin_info['version']
        ));
      } elseif (!empty($dep['min_version']) && $plugin_info['version'] && 
                version_compare($plugin_info['version'], $dep['min_version'], '<')) {
        // Plugin е активен, но версията е твърде стара
        $this->missing[$key] = array_merge($dep, array(
          'status'          => 'outdated',
          'current_version' => $plugin_info['version']
        ));
      }
    }
  }

  /**
   * Get plugin information (version and active status).
   *
   * @since 1.0.0
   * @access private
   * @param array $dependency Dependency configuration.
   * @return array Plugin information.
   */
  private function get_plugin_info($dependency) {
    $info = array(
      'active'  => false,
      'version' => null
    );

    // 1. Първо пробваме с класовете (най-бързо)
    if ($dependency['main_file'] === 'woocommerce/woocommerce.php' && defined('WC_VERSION')) {
      $info['active'] = true;
      $info['version'] = WC_VERSION;
      return $info;
    }

    if ($dependency['main_file'] === 'woocommerce-subscriptions/woocommerce-subscriptions.php' && 
        class_exists('WC_Subscriptions')) {
      $info['active'] = true;
      $info['version'] = WC_Subscriptions::$version ?? null;
      return $info;
    }

    // 2. Проверяваме директно файловете в plugins директорията
    $this->load_plugin_functions();
    
    $all_plugins = get_plugins();
    $plugin_found = false;
    
    foreach ($all_plugins as $plugin_slug => $plugin_data) {
      // Проверка за основния файл
      if ($plugin_slug === $dependency['main_file']) {
        $plugin_found = true;
        $info['version'] = $plugin_data['Version'];
        
        // Проверка дали plugin-ът е активен
        if (is_plugin_active($plugin_slug)) {
          $info['active'] = true;
        }
        break;
      }
      
      // Проверка за алтернативен файл (само за WooCommerce)
      if ($dependency['alternative_match'] && 
          basename($plugin_slug) === $dependency['alternative_match']['file'] &&
          $plugin_data['Name'] === $dependency['alternative_match']['name']) {
        $plugin_found = true;
        $info['version'] = $plugin_data['Version'];
        
        if (is_plugin_active($plugin_slug)) {
          $info['active'] = true;
        }
        break;
      }
    }
    
    // 3. Ако не сме намерили plugin въобще
    if (!$plugin_found) {
      $info['status'] = 'not_installed';
    }
    
    return $info;
  }

  /**
   * Load plugin.php functions if not already loaded.
   *
   * @since 1.0.0
   * @access private
   */
  private function load_plugin_functions() {
    if ($this->plugin_functions_loaded) {
      return;
    }
    
    if (!function_exists('get_plugins') || !function_exists('is_plugin_active')) {
      require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    
    $this->plugin_functions_loaded = true;
  }

  /**
   * Check if all dependencies are valid.
   *
   * @since 1.0.0
   * @return bool True if all dependencies are met, false otherwise.
   */
  public function has_valid_dependencies() {
    return empty($this->missing);
  }

  /**
   * Get missing dependencies.
   *
   * @since 1.0.0
   * @return array Array of missing dependencies.
   */
  public function get_missing_dependencies() {
    return $this->missing;
  }

  /**
   * Display admin notice for missing dependencies.
   *
   * @since 1.0.0
   */
  public function display_dependency_admin_notice() {
    if (empty($this->missing)) {
      return;
    }

    $plugin_name = 'Client System for Biskvitkite';
    ?>
    <div class="notice notice-error">
      <p>
        <strong><?php echo esc_html($plugin_name); ?></strong> 
        <?php esc_html_e('requires the following plugins to be installed and activated:', 'igs-client-system'); ?>
      </p>
      
      <ul style="list-style-type: disc; margin-left: 20px; margin-bottom: 10px;">
        <?php foreach ($this->missing as $dep): ?>
          <li>
            <strong><?php echo esc_html($dep['name']); ?></strong>
            <?php if ($dep['status'] === 'outdated' && !empty($dep['current_version'])): ?>
              <span class="description">
                (<?php printf(
                  __('version %s is installed, but version %s or higher is required', 'igs-client-system'),
                  esc_html($dep['current_version']),
                  esc_html($dep['min_version'])
                ); ?>)
              </span>
            <?php elseif ($dep['status'] === 'inactive'): ?>
              <span class="description">
                (<?php esc_html_e('installed but not activated', 'igs-client-system'); ?>)
              </span>
            <?php elseif (empty($dep['status']) || $dep['status'] === 'not_installed'): ?>
              <span class="description">
                (<?php esc_html_e('not installed', 'igs-client-system'); ?>)
              </span>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
      
      <p>
        <?php esc_html_e('Please install and activate the required plugins, then reactivate this plugin.', 'igs-client-system'); ?>
      </p>
    </div>
    <?php
  }
}