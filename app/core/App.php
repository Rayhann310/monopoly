<?php
class App {
    protected $controller = 'Home';
    protected $method = 'index';
    protected $params = [];

    public function __construct() {
        $url = $this->parseURL();

        if ($url && file_exists('app/controllers/' . ucfirst($url[0]) . '.php')) {
            $this->controller = ucfirst($url[0]);
            unset($url[0]);
        } elseif ($url && $url[0] !== '') {
            // Controller tidak ditemukan → 404
            http_response_code(404);
            die("<div style='font-family:monospace;background:#111;color:#f87171;padding:40px;'><h2>404 — Halaman tidak ditemukan</h2><p>Controller '<b>" . htmlspecialchars($url[0]) . "</b>' tidak ada.</p><a href='javascript:history.back()' style='color:#60a5fa'>← Kembali</a></div>");
        }

        require_once 'app/controllers/' . $this->controller . '.php';
        $this->controller = new $this->controller;

        if (isset($url[1])) {
            if (method_exists($this->controller, $url[1])) {
                $this->method = $url[1];
                unset($url[1]);
            } else {
                // Method tidak ditemukan → 404
                http_response_code(404);
                $controllerName = get_class($this->controller);
                die("<div style='font-family:monospace;background:#111;color:#f87171;padding:40px;'><h2>404 — Method tidak ditemukan</h2><p>Controller '<b>{$controllerName}</b>' tidak punya method '<b>" . htmlspecialchars($url[1]) . "</b>'.</p><a href='javascript:history.back()' style='color:#60a5fa'>← Kembali</a></div>");
            }
        }

        if (!empty($url)) {
            $this->params = array_values($url);
        }

        call_user_func_array([$this->controller, $this->method], $this->params);
    }

    public function parseURL() {
        if (isset($_GET['url'])) {
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            $url = explode('/', $url);
            return $url;
        }
    }
}
