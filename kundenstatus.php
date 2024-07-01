<?php

require_once "Page.php";
class Kundenstatus extends Page{
    
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

        $orderedArticleArray = array();
        if(isset($_SESSION['Bestellung_ID'])){
            $sessionID = $_SESSION['Bestellung_ID'];

            // Convert the array of order IDs into a comma-separated string
            $sessionIDsString = implode(',', array_map('intval', $sessionID));

            $orderArticleQuery = "Select a.name, b.status, c.ordering_id from article a
                              join ordered_article b on a.article_id = b.article_id
                              join ordering c on b.ordering_id = c.ordering_id";
            $recordSet = $this->_database->query($orderArticleQuery);

            if(!$recordSet) throw new Exception("Fehler in Abfrage: ".$this->_database->error);
            
            while($record = $recordSet->fetch_assoc()){
                $ordering_id = $record['ordering_id'];
                $articleID = $record['name'];
                $status = $record['status'];

                // Check if the fetched ordering_id is in the session array
                if (in_array($ordering_id, $sessionID)) {
                    $orderedArticleArray[$ordering_id][] = array(
                        'articleName' => $articleID,
                        'status' => $status
                    );
                }
            }
        }

        // to do: fetch data for this view from the database
        // to do: return array containing data
        return $orderedArticleArray;
    }


    protected function generateView():void
    {
        header("Content-type: application/json; charset=UTF-8, Content-Language: de");
        $data = $this->getViewData(); //NOSONAR ignore unused $data
        $serializedData = json_encode($data);
        // Output JSON data
        echo $serializedData;
    }

    protected function processReceivedData():void
    {

    }

    public static function main():void
    {
        try {
            session_start();
            $page = new Kundenstatus();
            $page->processReceivedData();
            $page->generateView();
        } catch (Exception $e) {
            //header("Content-type: text/plain; charset=UTF-8");
            header("Content-type: text/html; charset=UTF-8");
            echo $e->getMessage();
        }
    }


}

Kundenstatus::main();