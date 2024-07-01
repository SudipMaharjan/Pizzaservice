<?php

require_once "Page.php";
class Kunde extends Page{

    protected function __construct()
    {
        parent::__construct();
        // to do: instantiate members representing substructures/blocks
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    protected function getViewData():array
    {

    }

    protected function generateView():void
    {

        parent::generatePageHeader('Pizza Service'); //to do: set optional parameters
        // to do: output view of this page

        echo <<<EOT
        <h2>Kunde (Lieferstatus)</h2>
        <section id = "kundenContainer">
            <h3> Lieferstatus lädt... </h3>
        </section>
        <input type="button" value="Neue Bestellung" onclick="window.location.href='bestellung.php'">
        <script src = "js/StatusUpdate.js"> </script>
        EOT;
        parent::generatePageFooter();
    }

    protected function processReceivedData():void
    {
        parent::processReceivedData();
    }

    public static function main():void
    {
        try {
            session_start();
            $page = new Kunde();
            $page->processReceivedData();
            $page->generateView();
        } catch (Exception $e) {
            //header("Content-type: text/plain; charset=UTF-8");
            header("Content-type: text/html; charset=UTF-8");
            echo $e->getMessage();
        }
    }


}
// This call is starting the creation of the page.
// That is input is processed and output is created.
Kunde::main();