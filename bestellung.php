<?php declare(strict_types=1);
require_once "./Page.php";

class Bestellung extends Page{
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
        //fetch data for this view from the database
        $articleArray = array();
        $orderArticleQuery = "Select *  from article";
        $recordSet = $this->_database->query($orderArticleQuery);

        if(!$recordSet) throw new Exception("Fehler in Abfrage: ".$this->_database->error);
        $orderedArticleData =array();
        while($record = $recordSet->fetch_assoc()){
            $articleID = $record['article_id'];
            $articleName = $record['name'];
            $picture = $record['picture'];
            $price = $record['price'];

            $articleArray[$articleID] = array(
                'article_name' => $articleName,
                'picture' => $picture,
                'price' => $price
            );
        }
        // return array containing data
        return $articleArray;
    }
    protected function generateView():void
    {
        $data = $this->getViewData(); 
        $this->generatePageHeader(); 
        echo <<<EOT
                <section id="main-container" > 
                    <div id="title-cntainer" class="center-container">
                       <h1 class="font2">WÄHLEN SIE IHRE PIZZA</h1> 
                    </div>  
                    <div id="a" class="a">   
                        <div class="inner-body-container">           
        EOT;
        
        foreach ($data as  $orderDetails ){
            $pizzaName = htmlspecialchars($orderDetails["article_name"]);
            $picture = "Images/".htmlspecialchars($orderDetails["picture"]);
            $price = $orderDetails ["price"];
            echo <<<EOT
                <div class="content-list-grid-item column-items-container">
                    <img src = {$picture} height = "150" width = "150" alt = "" title= {$pizzaName} onclick = "addPizza('{$pizzaName}',{$price})" class="responsive-image">
                    <div class="product-text-container fontBodyText">
                        <p> $pizzaName </p>
                        <p class="price-font">€ $price</p>
                    </div>
                </div> 
                   
            EOT;
        }
        echo <<< EOT
                </div>
            </div>    
            <article id="cart" class="inner-body-container column-items-container ">
                <h2 class=" cart-title">Bestellung</h2>
                <form id = "bestellen" action = "bestellung.php" method="post" accept-charset="UTF-8">   
                    <input type="hidden" name="SessionID">      
                    <article id="orderList" class="column-items-container fontBodyText">
                        <h3 class="cartHeader cart-title">Warenkorb</h3>
                        <div id="order-container" class="column-items-container product-text-containerfontBodyText">
                            <div id="order-textarea" class="product-text-container">
                                <select id="orders" name="orders[]" size="10" multiple tabindex="1" class="fontBodyText"></select>
                            </div>                     
                            <div id="buttons-container" class="product-text-container fontBodyText">
                                <button type = "button" tabindex="1" accesskey="l" onclick = "removeAll()" class=" button-text fontBodyText"> Alle Löschen</button>
                                <button type = "button" tabindex="2" accesskey="k" onclick = "removeSelected()" class="button-text fontBodyText"> Auswahl Löschen</button>
                            </div>
                        </div>         
                    </article>
                    <div class="column-items-container">
                        <article class="fontBodyText">
                            <h3>Lieferadresse:</h3>
                            <label for="adresse">Bitte Ihre Adresse eingeben:</label>
                            <input type="text" id = "adresse" name="adresse" placeholder="Adresse" value="" tabindex="1" required>
                        </article>
                        
                        <article class="fontBodyText">
                            <h4 id = "totalPrice" class="price-font"><b>Preis: € 0.0</b></h4>
                            <button type = "button" id = "bestellbutton" tabindex="3" accesskey="b" onclick = "placeOrder()" class="button-text fontBodyText"  > Bestellen</button>
                        </article>
                    </div>            
                </form>
            </article>
         </section> 
        EOT;
        $this->generatePageFooter();
    }

    protected function processReceivedData():void
    {
        if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['orders']) && isset($_POST['adresse'])){

            $bestellungen = $_POST['orders'];
            $adresse = $_POST['adresse']; 
            $adresse = $this->_database->real_escape_string($adresse);

            if (!isset($_SESSION['Bestellung_ID'])) {
                $_SESSION['Bestellung_ID'] = array();
            }

            $orderingQuery = "Insert into ordering (address)
                              Values ('$adresse');";
            $this->_database->query($orderingQuery);
            $ordering_id = $this->_database->insert_id;
            array_push($_SESSION['Bestellung_ID'],$ordering_id);

            // Get the article data from the database
            $articleArray = $this->getViewData();

            // Create a mapping of article names to article IDs
            $articleNameToId = array();
            foreach ($articleArray as $articleID => $articleData) {
                $articleNameToId[$articleData['article_name']] = $articleID;
            }

            foreach($bestellungen as $bestellung){
                $article_id = isset($articleNameToId[$bestellung]) ? $articleNameToId[$bestellung] : 1000;
                //var_dump($article_id);
                $orderedArticleQuery = "Insert into ordered_article (ordering_id,article_id)
                                        Values ('$ordering_id','$article_id')";
                $this->_database->query($orderedArticleQuery);
            }
            header('Location: bestellung.php');
            die();
        }
    }

    public static function main():void
    {
        try {
            session_start();
            $pageBestellung = new Bestellung();
            $pageBestellung->processReceivedData();
            $pageBestellung->generateView();
        } catch (Exception $e) {
            header("Content-type: text/html; charset=UTF-8");
            echo $e->getMessage();
        }
    }
}

Bestellung::main();