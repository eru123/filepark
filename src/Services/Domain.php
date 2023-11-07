<?php

namespace Filepark\Services;

use eru123\router\Router;

class Domain
{
    static $domains = [];

    /**
     * Create a new domain
     * @param string $domain The domain name (e.g. "filepark.skiddph.com")
     * @return Router
     */
    public static function create($domain)
    {
        if (is_null($domain)) {
            $domain = isset($_SERVER['SERVER_NAME']) && $_SERVER['SERVER_NAME'] ? $_SERVER['SERVER_NAME'] : 'localhost';
        }

        $domain = strtolower($domain);
        if (isset(self::$domains[$domain])) {
            return self::$domains[$domain];
        }

        self::$domains[$domain] = new Router();
        return self::$domains[$domain];
    }

    /**
     * Get a domain
     * @param string $domain The domain name (e.g. "filepark.skiddph.com")
     * @return Router
     */
    public static function get($domain)
    {
        return isset(self::$domains[$domain]) ? self::$domains[$domain] : null;
    }

    /**
     * Run the domain router service
     */
    public static function run()
    {
        $host = isset($_SERVER['SERVER_NAME']) && $_SERVER['SERVER_NAME'] ? $_SERVER['SERVER_NAME'] : 'localhost';
        $host = strtolower($host);

        if (!isset(self::$domains[$host])) {
            return Router::status_page(500, 'Internal Server Error', "Domain not found: " . htmlspecialchars($host));
        }

        return self::$domains[$host]->run();
    }

    /**
     * Routes Directory Map
     */
    public static function routes_dirmap()
    {
        $scan = scandir(__ROUTES__);
        $dl = DIRECTORY_SEPARATOR;
        $result = [];

        while ($route = array_shift($scan)) {
            if ($route == '.' || $route == '..') {
                continue;
            }

            if (is_dir(__ROUTES__ . $dl . $route)) {
                $scan = array_merge($scan, scandir(__ROUTES__ . $dl . $route) ?? []);
            } else {
                $result[] = __ROUTES__ . $dl . $route;
            }
        }

        return $result;
    }

    /**
     * Load all routes
     */
    public static function load_routes()
    {
        $map_file = __ROOT_DIR__ . '/routes_map.php';
        if (file_exists($map_file)) {
            $routes = require($map_file);
            echo "Loaded routes from cache\n";
        } else {
            $routes = self::routes_dirmap();
            if (is_writable(__ROOT_DIR__)) {
                file_put_contents($map_file, '<?php return ' . var_export($routes, true) . ';');
            }
        }

        foreach ($routes as $route) {
            if (file_exists($route)) {
                require_once $route;
            }
        }
    }
}