<?php

require_once "Page.php";
class Fahrer extends Page{
    protected function __construct()
    {
        parent::__construct();
    }

    public function __destruct()
    {
        parent::__destruct();
    }
    
    protected function getViewData():array
    {
        $orderAdressArray = array();
        $orderAdressQuery = "Select b.price, b.name, c.address, c.ordering_id, a.status
                              from ordered_article a 
                              join article b on a.article_id = b.article_id
                              join ordering c on  c.ordering_id = a.ordering_id
                              where a.ordering_id not in (
                                Select ordering_id
                                from ordered_article
                                where status <= 1 or status > 4)";

        $recordSet = $this->_database->query($orderAdressQuery);
        if(!$recordSet) throw new Exception("Fehler in Abfrage: ".$this->_database->error);

        // fetch data for this view from the database
        while($record = $recordSet->fetch_assoc()){          

            $orderingAddress = $record['address'];
            $articleID = $record['name'];
            $articlePrice = $record['price'];
            $orderingID = $record['ordering_id'];
            $status = $record['status'];

            if(isset($orderAdressArray[$orderingAddress])){
                $orderAdressArray[$orderingAddress][] = array(
                    'articleName' => $articleID,
                    'articlePrice' => $articlePrice,
                    'ordering_id' => $orderingID,
                    'status' => $status
                );
            }
            else{
                $orderAdressArray[$orderingAddress] = array(
                    array(
                        'articleName' => $articleID,
                        'articlePrice' => $articlePrice,
                        'ordering_id' => $orderingID,
                        'status' => $status
                    )
                );       
            }
        }
        $recordSet->free();
        return $orderAdressArray;       //return array containing data
    }

    protected function generateView():void
    {
        header("Refresh: 10");  //refresh the page every 10 Seconds
        $data = $this->getViewData(); //NOSONAR ignore unused $data
        parent::generatePageHeader(); 

        $hasWork = false;
        foreach ($data as $orderingAddress => $orderDetails) {
            foreach ($orderDetails as $details) {
                $status = $details["status"];
                if ($status == 2 || $status == 3) { // Adjust condition based on your requirement
                    $hasWork = true;
                    break 2; // Exit both foreach loops
                }
            }
        }
        
        // output view of this page
        echo <<<EOT
             <h2>Fahrer (auslieferbare Bestellungen)</h2>
                 
        EOT;
        if(empty($data) || !$hasWork){
            $meldung = "Sie haben momentan keinen Auftrag";
            echo <<<EOT
                <h3>$meldung</h3>
                 
            EOT;
        }
        else{
            foreach ($data as $orderingAddress => $orderDetails ){
                $pizzaName = "";
                $pizzaPrice = 0.0;
                foreach($orderDetails as $details){
                    $pizzaName .= $details["articleName"].", ";
                    $pizzaPrice += $details["articlePrice"];
                    $id = $details["ordering_id"];
                    $status = $details["status"];
                }

                $selectedFertig ="";
                $selectedUnterwegs ="";
                $selectedGeliefert = "";

                if($status == 2){
                    $selectedFertig = "checked";
                }
                elseif ($status == 3){
                    $selectedUnterwegs = "checked";
                }
                elseif ($status == 4){
                    $selectedGeliefert = "checked";
                }
                           
                // htmlspecialchar:replaces all characters with a special function in HTML with non-executable characters
                $orderingAddress = htmlspecialchars($orderingAddress);
                $pizzaName = htmlspecialchars($pizzaName);
                if($status != 4){
                    echo <<<EOT
                    <article>
                    <h3>Bestell-ID: $id</h3>
                    <p>Lieferadresse: $orderingAddress</p>
                    <p>Gesamtpries: $pizzaPrice €</p>
                    <p> Bestellung: $pizzaName</p>
                                    
                                <form action="fahrer.php" method="post" accept-charset="UTF-8">
                                    <input type="hidden" name="id" value="{$id}">
                                    <label for="lfrStatusFertig{$id}">
                                        <input id= "lfrStatusFertig{$id}" onclick = "this.form.submit();" type="radio" name="Lieferstatus{$id}" value="fertig" {$selectedFertig}>fertig
                                    </label>    
                                    <label for="lfrStatusUnterwegs{$id}">
                                        <input id= "lfrStatusUnterwegs{$id}" onclick = "this.form.submit();" type="radio" name="Lieferstatus{$id}" value="unterwegs" {$selectedUnterwegs}>unterwegs
                                    </label>    
                                    <label for="lfrStatusGeliefert{$id}">
                                        <input id= "lfrStatusGeliefert{$id}" onclick = "this.form.submit();" type="radio" name="Lieferstatus{$id}" value="geliefert" {$selectedGeliefert}>geliefert
                                    </label>    
                                </form>
                            
                        </article>
                    <hr>
                    EOT;
                }
            }
        }
        parent::generatePageFooter();
    }

    protected function processReceivedData():void
    {
        parent::processReceivedData();
        if(count($_POST) && isset($_POST['id'])){            
            $id = $_POST['id'];
            $id = $this->_database->real_escape_string($id);
            if(isset($_POST["Lieferstatus{$id}"])){
                $lieferstatus = $_POST["Lieferstatus{$id}"];
                $lieferstatus = $this->_database->real_escape_string($lieferstatus);
                switch($lieferstatus){
                    case 'fertig':
                        $status = 2;
                        break;
                    case 'unterwegs':
                        $status = 3;
                        break;
                    case 'geliefert':
                        $status = 4;
                        break;
                    default:
                        $status = 2;
                }
                $updateStatusQuery ="update ordered_article
                                    set status = $status
                                    where ordering_id =$id";
                $this->_database->query($updateStatusQuery);
                header("Location: fahrer.php");
                die(); 
            }
        }
    }

    public static function main():void
    {
        try {
            $page = new Fahrer();
            $page->processReceivedData();
            $page->generateView();
        } catch (Exception $e) {
            header("Content-type: text/html; charset=UTF-8");
            echo $e->getMessage();
        }
    }
}
// This call is starting the creation of the page.
// That is input is processed and output is created.
Fahrer::main();
