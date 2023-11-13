<?php

namespace Filepark\Services;

use eru123\router\Router;

class Domain
{
    static $domains = [];
    static $usedCachedRoutes = false;

    /**
     * Create a new domain
     * @param string $domain The domain name (e.g. "filepark.skiddph.com")
     * @return Router
     */
    public static function create($domain, $autofix = true)
    {
        if (is_null($domain) && $autofix) {
            $domain = static::getDomain();
        } else if (is_null($domain)) {
            return new Router();
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
        $host = static::getDomain();

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

        sort($result);
        return $result;
    }

    /**
     * Load all routes
     */
    public static function load_routes()
    {
        $map_file = __CACHE_DIR__ . '/routes_map.php';
        $map_dir = dirname($map_file);
        if (!venv('CACHE_ROUTES', true) && venv('APP_ENV', 'production') == 'production' && file_exists($map_file)) {
            self::$usedCachedRoutes = true;
            $routes = require($map_file);
        } else {
            $routes = self::routes_dirmap();
            if (is_writable($map_dir)) {
                file_put_contents($map_file, '<?php return ' . var_export($routes, true) . ';');
            }
        }

        foreach ($routes as $route) {
            if (file_exists($route)) {
                require_once $route;
            }
        }
    }

    /**
     * Check if cached routes are used
     */
    public static function isCachedRoutes()
    {
        return self::$usedCachedRoutes;
    }

    /**
     * Get request's domain
     */
    public static function getDomain()
    {
        $domain = isset($_SERVER['SERVER_NAME']) && $_SERVER['SERVER_NAME'] ? $_SERVER['SERVER_NAME'] : 'localhost';
        return strtolower($domain);
    }
}