<?php
class Router {
    private $routes = [];

    public function add($method, $path, $handler) {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler
        ];
    }

    public function dispatch($uri, $method) {
        $uri = parse_url($uri, PHP_URL_PATH);
        // Remove base path if needed (assuming root for now or handled by htaccess)
        // If site is in subdirectory, we might need adjustment. 
        // User's path is c:/Users/Hoai Hung/Documents/promptlib. 
        // Let's assume it runs at root or relative.
        
        foreach ($this->routes as $route) {
            // Simple regex matching for parameters like /prompt/{id}
            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<\1>[^/]+)', $route['path']);
            $pattern = '#^' . $pattern . '$#';

            if ($route['method'] === $method && preg_match($pattern, $uri, $matches)) {
                $handler = $route['handler'];
                // Remove integer keys from matches
                foreach ($matches as $key => $value) {
                    if (is_int($key)) unset($matches[$key]);
                }
                
                if (is_array($handler)) {
                    $controllerName = $handler[0];
                    $actionName = $handler[1];
                    require_once __DIR__ . "/Controllers/$controllerName.php";
                    $controller = new $controllerName();
                    call_user_func_array([$controller, $actionName], $matches);
                    return;
                }
            }
        }

        // 404 Not Found
        http_response_code(404);
        echo "404 Not Found";
    }
}
?>
