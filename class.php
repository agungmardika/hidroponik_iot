<?php
class Nodemcu_log
{

    // Connection
    private $conn;

    // Table
    private $db_table = "status";

    // Columns
    public $id;
    public $suhu;
    public $suhu2;
    public $tds;
    public $created_at;

    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
    }

    // CREATE
    public function createLogData()
    {
        $sqlQuery = "INSERT INTO
                        " . $this->db_table . "
                    SET
                        suhu = :suhu,
                        suhu2 = :suhu2, 
                        tds = :tds";
        $stmt = $this->conn->prepare($sqlQuery);

        // sanitize
        $this->suhu = htmlspecialchars(strip_tags($this->suhu));
        $this->suhu2 = htmlspecialchars(strip_tags($this->suhu2));
        $this->tds = htmlspecialchars(strip_tags($this->tds));

        // bind data
        $stmt->bindParam(":suhu", $this->suhu);
        $stmt->bindParam(":suhu2", $this->suhu2);
        $stmt->bindParam(":tds", $this->tds);
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
