<?php

require_once RAIZ_APP.'/MysqlConnector.php';
require_once RAIZ_APP.'/DamageRepository.php';
require_once RAIZ_APP.'/Damage.php';
require_once RAIZ_APP.'/AbstractMysqlRepository.php';

class MysqlDamageRepository extends AbstractMysqlRepository implements DamageRepository {

    public function __construct(MysqlConnector $connector) {
        parent::__construct($connector);
    }
    
    public function count() {
        $sql = 'select count(d_id) as num_damages from Damage';
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stmt->bind_result($num_damages);
        $stmt->fetch();
        $stmt->close();
        return $num_damages;
    }

    public function findById($id) {
        $damage = null;

        if(!isset($id))
            return null;

        $sql = sprintf("select d_id, vehicle, user, title, description, evidenceDamage, area, type, isRepaired from Damage where id = %d", $id);
        $result = $this->db->query($sql);

        if ($result !== false && $result->num_rows > 0) {
            $obj = $result->fetch_object();
            $damage = new Damage($obj->d_id, $obj->vehicle, $obj->user, $obj->title, $obj->description, $obj->evidenceDamage, $obj->area, $obj->type, $obj->isRepaired);
        }

        $result->close();

        return $damage;
    }

    public function findAll() {
        $damages = [];

        $sql = sprintf("select d_id, vehicle, user, title, description, evidenceDamage, area, type, isRepaired from Damage");
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $result = $stmt->get_result();
        $stmt->close();

        while ($row = $result->fetch_assoc()) {
            $damage = new Damage($row['d_id'], $row['vehicle'], $row['user'], $row['title'], $row['description'], $row['evidenceDamage'], $row['area'], $row['type'], $row['isRepaired']);
            $damages[] = $damages;
        }

        return $damages;
    }

    public function findByVehicle($vehicle) {
        $damages[] = array();

        $sql = sprintf("select d_id, vehicle, user, title, description, evidenceDamage, area, type, isRepaired from Damage where vehicle = '%d'",
                        $vehicle->getVin());
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $result = $stmt->get_result();
        $stmt->close();

        while ($row = $result->fetch_array(MYSQLI_NUM)) {
            foreach ($row as $damage)
                $damages[] = $damage;
        }

        return $damages;
    }

    public function deleteById($d_id) {
        // Check if the damage already exists
        if ($this->findById($d_id) !== null) {
            $sql = sprintf("SET FOREIGN_KEY_CHECKS=0");
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute();
            $stmt->close();
            $sql = sprintf("delete from Damage where d_id = %d", $d_id);
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute();
            $stmt->close();
            $sql = sprintf("SET FOREIGN_KEY_CHECKS=1");
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute();
            $stmt->close();
            
            
            if (!$result)
                error_log("Database error: ({$this->db->getConnection()->errno}) {$this->db->getConnection()->error}");
            
            return $result;
        }

        return false;
    }

    public function delete($damage) {
        // Check entity type and we check first if the user already exists
        $importedDamage = $this->findById($damage->getId());
        if ($damage instanceof Damage && ($importedDamage !== null))
            return $this->deleteById($importedDamage->getId());
        return false;
    }

    public function save($damage) {
        // Check entity type
        if ($damage instanceof Damage) {
            /**
             * If the damage already exists, we do an update.
             * We retrieve the damage's id from the database.
             */
            $importedDamage = $this->findById($damage->getId());
            if ($importedDamage !== null) {
                $damage->setId($importedDamage->getId());
                if ($damage->getEvidenceDamage() !== null) {
                $sql = sprintf("update Damage set vehicle = '%d', user = '%d', title = '%d', description  = '%d', evidenceDamage ='%d', area ='%d', type ='%d', isRepaired ='%d'",
                        $damage->getVehicle(),
                        $damage->getUser(),
                        $this->db->getConnection()->real_escape_string($damage->getTitle()),
                        $this->db->getConnection()->real_escape_string($damage->getDescription()),
                        $damage->getEvidenceDamage(),
                        $this->db->getConnection()->real_escape_string($damage->getArea()),
                        $this->db->getConnection()->real_escape_string($damage->getType()),
                        $damage->getIsRepaired(),
                        $damage->getId()
                       );
                } else {
                    $sql = sprintf("update Damage set vehicle = '%d', user = '%d', title = '%d', description  = '%d', evidenceDamage = NULL, area ='%d', type ='%d', isRepaired ='%d'",
                    $damage->getVehicle(),
                    $damage->getUser(),
                    $this->db->getConnection()->real_escape_string($damage->getTitle()),
                    $this->db->getConnection()->real_escape_string($damage->getDescription()),
                    $this->db->getConnection()->real_escape_string($damage->getArea()),
                    $this->db->getConnection()->real_escape_string($damage->getType()),
                    $damage->getIsRepaired(),
                    $damage->getId()
                   );
                }        
                
                $stmt = $this->db->prepare($sql);
                $result = $stmt->execute();
                $stmt->close(); 

                if ($result)
                    return $damage;
                else 
                    error_log("Database error: ({$this->db->getConnection()->errno}) {$this->db->getConnection()->error}");
                // If the reserve is not in the database, we insert it.
            } else {
                if ($damage->getEvidenceDamage() !== null) {
                    $sql = sprintf("insert into Damage (d_id, vehicle, user, title, description, evidenceDamage, area, type, isRepaired) values ('%d', '%d', '%s', '%s', '%d', '%d', '%s', '%s', '%s')",
                        $damage->getId(),
                        $damage->getVehicle(),
                        $this->db->getConnection()->real_escape_string($damage->getUser()),
                        $this->db->getConnection()->real_escape_string($damage->getTitle()),
                        $this->db->getConnection()->real_escape_string($damage->getDescription()),
                        $damage->getEvidenceDamage(),
                        $this->db->getConnection()->real_escape_string($damage->getArea()),
                        $this->db->getConnection()->real_escape_string($damage->getType()),
                        $this->db->getConnection()->real_escape_string($damage->getIsrepaired())
                    );
                } else {
                    $sql = sprintf("insert into Damage (d_id, vehicle, user, title, description, area, type, isRepaired) values ('%d', '%d', '%s', '%s', '%d', '%s', '%s', '%s')",
                        $damage->getId(),
                        $damage->getVehicle(),
                        $this->db->getConnection()->real_escape_string($damage->getUser()),
                        $this->db->getConnection()->real_escape_string($damage->getTitle()),
                        $this->db->getConnection()->real_escape_string($damage->getDescription()),
                        $this->db->getConnection()->real_escape_string($damage->getArea()),
                        $this->db->getConnection()->real_escape_string($damage->getType()),
                        $this->db->getConnection()->real_escape_string($damage->getIsrepaired())
                    );
                }       

                $stmt = $this->db->prepare($sql);
                $result = $stmt->execute();
                $stmt->close();

                if ($result) {
                    // We get the asssociated id to this new reserve
                    $damage->setId($this->db->getConnection()->insert_id);
                    return $damage;
                } else
                    error_log("Database error: ({$this->db->getConnection()->errno}) {$this->db->getConnection()->error}");
            }
        }
        return null;
    }

}
