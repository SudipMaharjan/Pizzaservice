<?php declare(strict_types=1);
require_once "./Page.php";
class Baecker extends Page{
    protected function __construct()
    {
        parent::__construct();
    }

    /**
     * Cleans up whatever is needed.
     * Calls the destructor of the parent i.e. page class.
     * So, the database connection is closed.
     */
    public function __destruct()
    {
        parent::__destruct();
    }

    protected function getViewData():array
    {
        $orderedArticleArray = array();
        $orderArticleQuery = "Select a.ordered_article_id,  b.name, a.status  
                                from ordered_article a 
                                join article b on a.article_id = b.article_id
                                where a.status < 3
                                ORDER BY ordered_article_id ASC";
        $recordSet = $this->_database->query($orderArticleQuery);

        if(!$recordSet) throw new Exception("Fehler in Abfrage: ".$this->_database->error);
        while($record = $recordSet->fetch_assoc()){
            $orderedArticleID = $record['ordered_article_id'];
            $articleID = $record['name'];
            $status = $record['status'];

            $orderedArticleArray[$orderedArticleID] = array(
                'article_name' => $articleID,
                'status' => $status
            );
        }
        //Freigabe des DB-RecordSets nach der Nutzung
        $recordSet->free();
        //return array containing data
        return $orderedArticleArray;
    }


    protected function generateView():void
    {
        header("Refresh: 10");  //refresh the page every 10 Seconds
        $data = $this->getViewData(); //NOSONAR ignore unused $data
        parent::generatePageHeader(); //to do: set optional parameters

        echo <<<EOT
             <h2>Pizzabäcker (bestellte Pizzen)</h2>               
        EOT;

        if(sizeof($data) > 0){
            foreach ($data as $id => $orderDetails ){

                $status = $orderDetails["status"];
                $pizzaName = $orderDetails["article_name"];
                $selectedBestellt ="";
                $selectedImOffen = "";
                $selectedFertig ="";
                if($status == 0){
                    $selectedBestellt = "checked";
                }
                elseif ($status == 1){
                    $selectedImOffen = "checked";
                }
                elseif ($status == 2){
                    $selectedFertig = "checked";
                }
                $pizzaName = htmlspecialchars($pizzaName);
                echo <<<EOT
          <article>
           <h3>Ordered Article Id: {$id}</h3>
            <article>
                    <h4>{$pizzaName}</h4>
                    <form id = "baeckerForm{$id}" action="baecker.php" method="post" accept-charset="UTF-8">
                        <input type="hidden" name="id" value={$id}>
                        <label for="order{$id}Status_bestellt">
                            <input type="radio" onclick = "this.form.submit();" id="order{$id}Status_bestellt"  value="0" name="order{$id}Status"  {$selectedBestellt}>bestellt
                        </label>
                        <label for="order{$id}Status_imOffen">
                            <input type="radio" onclick = "this.form.submit();" id="order{$id}Status_imOffen"  value=1 name="order{$id}Status" {$selectedImOffen}>im Offen
                        </label>
                        <label for="order{$id}Status_fetig">
                            <input type="radio" onclick = "this.form.submit();" id="order{$id}Status_fetig"  value="2"  name="order{$id}Status" {$selectedFertig}>fertig
                        </label>
                        
                    </form>
                </article>
            </article>
           <hr>
          EOT;

            }
        }else{
            echo "<h2>Es gibt keine Pizza zu backen</h2>";
        }

        parent::generatePageFooter();
    }
    protected function processReceivedData():void
    {
        if($_SERVER["REQUEST_METHOD"] == "POST"){
            if(isset($_POST['id']) && is_numeric($_POST['id'])){
                $id = $_POST['id'];
                $id = $this->_database->real_escape_string($id);        //to avoid possible attacks
                $orderIdStatus = 'order'.$id.'Status';
                if(isset($_POST[$orderIdStatus])){
                    $baeckerStatus = $_POST[$orderIdStatus];
                    $baeckerStatus = $this->_database->real_escape_string($baeckerStatus);      //to avoid possible attacks
                    //change status to database
                    $updateStatusQuery = "UPDATE ordered_article
                                      SET status = $baeckerStatus
                                      WHERE ordered_article_id =$id";
                    $this->_database->query($updateStatusQuery);
                }
            }
            //redirect
            header('Location: baecker.php');
            die();


        }
    }

    public static function main():void
    {
        try {
            $page = new Baecker();
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
Baecker::main();




