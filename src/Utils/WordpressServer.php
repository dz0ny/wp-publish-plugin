<?php

    namespace WPAPlugin\Utils;

    use HTTPServer;
    use Symfony\Component\Console\Output\OutputInterface;

    /**
     * Class WordpressServer
     * @package WPAPlugin\Utils
     */
    class WordpressServer extends HTTPServer
    {
        // We pass in variables, rather than querying options here, to allow this to
        // potentially be used in other commands.
        public $root_path, $debug, $env, $console, $site;

        /**
         * @param string $root_path
         * @param OutputInterface $console
         * @param int $port
         * @param bool $debug
         * @param array $env
         */
        function __construct($root_path, OutputInterface $console, $port = 4000, $debug = false, $env=array())
        {
            parent::__construct(array(
                'port' => $port,
                'addr' => '127.0.0.1',
                'server_id' => 'Wordpress/1.0'
            ));
            $this->console   = $console;
            $this->root_path = $root_path;
            $this->debug     = $debug;
            $this->site     = 'localhost';
            $this->env     = $env;
        }

        function listening()
        {
            $this->console->writeln("HTTP server listening on {$this->addr}:$this->port (see http://localhost:$this->port/)...");
        }

        function route_request($request)
        {
            $cgi_env = $this->env;

            // Handle static files and php scripts accessed directly
            $uri      = $request->uri;
            $path     = $this->root_path . $uri;
            if (is_file(realpath($path))) {
                if (preg_match('#\.php$#', $uri)) {
                    // SCRIPT_NAME is equal to uri if it does exist on disk
                    $cgi_env['SCRIPT_NAME'] = $uri;

                    return $this->get_php_response($request, $path, $cgi_env);
                }

                return $this->get_static_response($request, $path);
            }

            // Rewrite clean-urls
            $cgi_env['QUERY_STRING'] = 'q=' . ltrim($uri, '/');
            if ($request->query_string != "") {
                $cgi_env['QUERY_STRING'] .= '&' . $request->query_string;
            }

            $cgi_env['SCRIPT_NAME'] = '/index.php';
            $cgi_env['HTTP_HOST']   = $cgi_env['SERVER_NAME'] = $this->site;

            return $this->get_php_response($request, $this->root_path . '/index.php', $cgi_env);
        }

        /**
         * Override request done event.
         */
        function request_done($request)
        {
            $this->console->write($this->get_log_line($request));

            if ($this->debug) {
                $this->console->writeln($request);
            }
        }

        /**
         * Override get_static_response.
         */
        function get_static_response($request, $local_path)
        {
            if (is_file($local_path))
            {
                $response = $this->response(200,
                    fopen($local_path, 'rb'),
                    array(
                        'Content-Type' => static::get_mime_type($local_path),
                        'Cache-Control' => "max-age=0",
                        'Content-Length' => filesize($local_path),
                        // hopefully file size doesn't change before we're done writing the file
                    )
                );

                return $response;
            }
            else if (is_dir($local_path))
            {
                return $this->text_response(403, "Directory listing not allowed");
            }
            else
            {
                return $this->text_response(404, "File not found");
            }
        }
    }
