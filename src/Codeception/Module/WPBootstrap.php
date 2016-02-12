<?php

namespace Codeception\Module;

use Codeception\Exception\ModuleConfigException;
use Codeception\Exception\ModuleConflictException;
use Codeception\Module;
use tad\WPBrowser\Filesystem\Utils;

/**
 * Class WPBootstrap
 *
 * Bootstrap an existing WordPress website for testing purposes.
 *
 * The class is a Codeception adaptation of WordPress automated testing suite,
 * see [here](http://make.wordpress.org/core/handbook/automated-testing/),
 * and takes care of bootstraping an existing WordPress installation.
 * Use with Db or WPDb to load a preconfigured setup.
 *
 * @package Codeception\Module
 */
class WPBootstrap extends Module
{
    public static $includeInheritedActions = true;
    public static $onlyActions = array();
    public static $excludeActions = array();

    /**
     * The fields the user will have to set to legit values for the module to run.
     *
     * wpRootFolder - the absolute path to the root folder of the WordPress
     * installation to use for testing, the ABSPATH global value.
     *
     * @var array
     */
    protected $requiredFields = array('wpRootFolder',);
    
    /**
     * The path to the modified tests bootstrap file.
     *
     * @var string
     */
    protected $wpBootstrapFile;
    /**
     * @var string The absolute path to WP root folder (`ABSPATH`).
     */
    protected $wpRootFolder;

    /**
     * @var string The absolute path to the plugins folder
     */
    protected $pluginsFolder;

    /**
     * The function that will initialize the module.
     *
     * The function will set up the WordPress testing configuration and will
     * take care of installing and loading WordPress. The simple inclusion of
     * the module in an test helper class will hence trigger WordPress loading,
     * no explicit method calling on the user side is needed.
     *
     * @return void
     */
    public function _initialize()
    {
        $this->ensureWPRoot($this->getWpRootFolder());

        $this->wpBootstrapFile = $this->wpRootFolder . '/wp-load.php';
        $this->loadWordPress();
        
        $this->setupTestEnvironment();
    }

    /**
     * @param string $wpRootFolder
     */
    private function ensureWPRoot($wpRootFolder)
    {
        if (!file_exists($wpRootFolder . DIRECTORY_SEPARATOR . 'wp-settings.php')) {
            throw new ModuleConfigException(__CLASS__, "\nThe path `{$wpRootFolder}` is not pointing to a valid WordPress installation folder.");
        }
    }

    /**
     * @return string
     */
    protected function getWpRootFolder()
    {
        if (empty($this->wpRootFolder)) {
            // allow me not to bother with traling slashes
            $wpRootFolder = Utils::untrailslashit($this->config['wpRootFolder']) . DIRECTORY_SEPARATOR;

            // maybe the user is using the `~` symbol for home?
            $this->wpRootFolder = Utils::homeify($wpRootFolder);
        }
        return $this->wpRootFolder;
    }

    /**
     * Loads WordPress calling the bootstrap file
     *
     * This method does little but wrapping preparing the global space for the
     * original automated testing bootstrap file and taking charge of replacing
     * the original "wp-tests-config.php" file in setting up the globals.
     *
     * @return void
     */
    protected function loadWordPress()
    {
        require_once dirname(dirname(__DIR__)) . '/includes/functions.php';

        require_once $this->wpBootstrapFile;
    }
    
    protected function setupTestEnvironment()
    {
        global $phpmailer;

        $includeDir = dirname(dirname(__DIR__)) . '/includes';
        
        if ( ! defined( 'WP_TESTS_FORCE_KNOWN_BUGS' ) )
        	define( 'WP_TESTS_FORCE_KNOWN_BUGS', false );
        
        // Cron tries to make an HTTP request to the blog, which always fails, because tests are run in CLI mode only
        if ( ! defined( 'DISABLE_WP_CRON' ) )
            define( 'DISABLE_WP_CRON', true );

        // Override the PHPMailer
        require_once( $includeDir . '/mock-mailer.php' );
        $phpmailer = new \MockPHPMailer();
        
        require_once $includeDir . '/functions.php';
        
        // Load WordPress: "untrailingslash" ABSPATH first of all to avoid double slashes in filepath,
        // while still working if ABSPATH did not include a trailing slash
        require_once rtrim( ABSPATH, '/\\' ) . '/wp-settings.php';

        if ( !class_exists( 'WP_UnitTestCase' ) ) {
            require $includeDir . '/testcase.php';
            require $includeDir . '/testcase-rest-api.php';
            require $includeDir . '/testcase-xmlrpc.php';
            require $includeDir . '/testcase-ajax.php';
            require $includeDir . '/testcase-canonical.php';
            require $includeDir . '/exceptions.php';
            require $includeDir . '/utils.php';
        }

        // let's make sure we are using a version of WordPress that integrates the WP_REST_Server class
        if ( class_exists( 'WP_REST_Server' ) && !class_exists( 'Spy_REST_Server' ) ) {
        	require $includeDir . '/spy-rest-server.php';
        }
    }
}
