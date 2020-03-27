<?php
class Data{
  
    // database connection and table name
    private $conn;
    private $table_name = "public_data_place_org_table";
  
    // object properties

//     Category

// 	Social Distancing
// 	Essential Supplies
// 	Need Supplies
// 	Help the needy/Get Supplies 
	
// Subcategory
// 	One Option or All
// 	Kind of Work 
	     
    public $name;
    public $address;
    public $latitude;
    public $longitude;
    public $category;
    public $subcategory;    
    public $number;
    public $info;
        
    public $fillable = true;
  
    // constructor with $db as database connection
    public function __construct($db){
        $this->conn = $db;
    }

    public function create(){
  
        // query to insert record
        $query = "INSERT INTO
                    " . $this->table_name . "
                  SET
                    place_org_name =:name, 
                    place_org_address =:address, 
                    place_org_lat=:latitude, 
                    place_org_long=:longitude,
                    place_org_category=:category,
                    place_org_subcategory=:subcategory,
                    place_org_number=:number,
                    info=:info,
                    flagged_as_erronous=0,
                    logical_delete=0
                    ";
      
        // prepare query
        $stmt = $this->conn->prepare($query);      
        // sanitize
        $this->name=htmlspecialchars(strip_tags($this->name));
        $this->address=htmlspecialchars(strip_tags($this->address));
        $this->latitude=htmlspecialchars(strip_tags($this->latitude));
        $this->longitude=htmlspecialchars(strip_tags($this->longitude));
        $this->category=htmlspecialchars(strip_tags($this->category));
        $this->subcategory=htmlspecialchars(strip_tags($this->subcategory));
        $this->number=htmlspecialchars(strip_tags($this->number));
        $this->info=htmlspecialchars(strip_tags($this->info));
      
        // bind values
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":latitude", $this->latitude);
        $stmt->bindParam(":longitude", $this->longitude);
        $stmt->bindParam(":category", $this->category);
        $stmt->bindParam(":subcategory", $this->subcategory);
        $stmt->bindParam(":number", $this->number);
        $stmt->bindParam(":info", $this->info);
        // execute query
        if($this->fillable){
            if($stmt->execute()){
                return true;
            }
        }
      
        return false;
          
    }
    
    public function setLocation($location_string){
        $location = explode(" ",$location_string);
        $this->latitude = $location[0];
        $this->longitude = $location[1];
    }
}
?>