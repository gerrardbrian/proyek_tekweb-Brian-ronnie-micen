<?php

require_once 'database.php';

class order{
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function checkout($userid,$cartItems){
        try{
            $this->conn->beginTransaction();
            $totalAmount = 0;
            foreach($cartItems as $item){
                $totalAmount += $item['price'] * $item['qty'];
            }

            $query= "INSERT INTO orders (user_id, total_amount, status, created_at) VALUES (:uid, :total, 'pending', NOW())";
            $stmt= $this->conn->prepare($query);
            $stmt->bindparam(':uid',$userid);
            $stmt->bindparam(':total',$totalAmount);
            $stmt->execute();

            $orderid= $this->conn->lastInsertId();

            $querydetail="INSERT INTO order_details (order_id, product_id, quantity, price) VALUES (:oid, :pid, :qty, :price)";
            $stmtdetail= $this->conn->prepare($querydetail);

            foreach($cartItems as $item){
                $stmtdetail->bindparam(':oid',$orderid);
                $stmtdetail->bindparam(':pid',$item['id']);
                $stmtdetail->bindparam(':qty',$item['qty']);
                $stmtdetail->bindparam(':price',$item['price']);
                $stmtdetail->execute();
            }

            $this->conn->commit();
            return true;
        }catch(Exception $e){
            $this->conn->rollBack();
            return "Error: " . $e->getMessage();;
        }
    }
}
?>