<?php
use engine\system;
use engine\property;
class api_redirect_front {
    protected static $instance = null;

    public static function getInstance() {
        if(is_null(self::$instance))
            self::$instance = new self();
        return self::$instance;
    }

    public function make() {
        $url = system::getInstance()->get('url');
        if(!filter_var($url, FILTER_VALIDATE_URL)) {
            $url = property::getInstance()->get('url');
        }
        $theme = "<html>
                <head>
                    <meta http-equiv=\"refresh\" content=\"0; url={$url}\">
                    <script type=\"text/javascript\">
                        location.href(\"{$url}\");
                    </script>
                </head>
                <body>
                Redirecting ... <br />
                <strong>Link: <noindex><a href=\"{$url}\" rel=\"nofollow\">{$url}</a></noindex></strong>
                </body>
                </html>";
        echo $theme;
    }
}