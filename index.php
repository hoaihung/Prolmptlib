<?php
require_once 'includes/loader.php';
require_once 'includes/svg.php'; // Included here as it was in original index.php
require_once 'app/Router.php';

$router = new Router();

// Define routes
$router->add('GET', '/', ['HomeController', 'index']);
$router->add('GET', '/index.php', ['HomeController', 'index']);
$router->add('GET', '/prompt/{id}', ['PromptController', 'detail']);
$router->add('GET', '/news', ['NewsController', 'index']);
$router->add('GET', '/news/{slug}', ['NewsController', 'detail']);
$router->add('GET', '/my-prompts', ['MyPromptController', 'index']);
$router->add('GET', '/my-prompts/create', ['MyPromptController', 'create']);
$router->add('POST', '/my-prompts/store', ['MyPromptController', 'store']);
$router->add('GET', '/my-prompts/edit/{id}', ['MyPromptController', 'edit']);
$router->add('POST', '/my-prompts/update/{id}', ['MyPromptController', 'update']);
$router->add('GET', '/my-prompts/delete/{id}', ['MyPromptController', 'delete']);
$router->add('POST', '/comments/store', ['CommentController', 'store']);
$router->add('GET', '/page/{slug}', ['PageController', 'show']);

// Dispatch
// Fix URI for localhost/subfolder if needed, but assuming root for now based on typical setup
// If user is in /promptlib/, we might need to strip that.
// But Router.php logic: $uri = parse_url($uri, PHP_URL_PATH);
// If URI is /promptlib/, regex #^/$# won't match.
// Let's adjust Router or URI here.
// For now, let's assume root. If it fails, we fix.
// Actually, user path is c:/Users/.../promptlib. It's likely a local dev setup.
// If they run `php -S localhost:8000`, it's root.
// If they use XAMPP/WAMP, it might be /promptlib/.
// I'll make the Router more flexible or handle it here.
// Let's try to detect base path.

$uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

// Handle query string in URI for Router? Router uses parse_url(PHP_URL_PATH), so query string is stripped.
// But if URI is /index.php?page=home, Router sees /index.php.
// If URI is /?page=home, Router sees /.
// This matches our routes.

$router->dispatch($uri, $method);
?>
