<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */

use engine\system;
use engine\property;
class api_redirect_front extends \engine\singleton {

    public function make() {
        $url = system::getInstance()->get('url');
        if(!filter_var($url, FILTER_VALIDATE_URL)) {
            $url_decode = @base64_decode($url);
            if(filter_var($url_decode, FILTER_VALIDATE_URL))
                $url = $url_decode;
            else
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