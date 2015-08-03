<?php
/**
 * User: Junade Ali
 * Date: 21/07/15
 * Time: 18:05
 */

namespace IcyApril;

use Proxy\Factory;
use Proxy\Response\Filter\RemoveEncodingFilter;
use Symfony\Component\HttpFoundation\Request;


class App
{

    private $page;
    private $permittedIPs;
    private $allowLocal;

    function __construct() {

        $this->permittedIPs = array('89.197.18.218', '5.102.83.226', '195.62.222.110');
        $this->allowLocal = file_exists(getcwd().'/.allowdevelopment');

        if ($this->checkIP() !== true) {
            die();
        }

        if ($this->setPage() !== true) {
            die();
        }

        $this->getPage();

    }

    private function checkIP () {

        if (($_SERVER['REMOTE_ADDR'] === '127.0.0.1') && ($this->allowLocal === true)) {
            return true;
        }

        if (in_array($_SERVER['REMOTE_ADDR'], $this->permittedIPs)) {
            return true;
        }

        return false;
    }

    protected function setPage () {

        if ($_GET['page']) {
            $url = $_GET['page'];
        } else {
            return false;
        }

        $url = $this->fixURL($url);

        $this->page = $url;

        return true;

    }

    private function getPage () {

        // Create the proxy factory.
        $proxy = Factory::create();

        // Add a response filter that removes the encoding headers.
        $proxy->addResponseFilter(new RemoveEncodingFilter());

        // Create a Symfony request based on the current browser request.
        $request = Request::createFromGlobals();

        // Forward the request and get the response.
        $response = $proxy->forward($request)->to($this->page);

        // Output response to the browser.
        $response->send();

    }

    protected function fixURL ($url) {
        if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
            $url = "http://" . $url;
        }
        return $url;
    }


}