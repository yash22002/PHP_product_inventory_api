<?php
header("Content-Type: application/json");
$storage = "products.json";		// data storage file
$method = $_SERVER['REQUEST_METHOD'];

// Reads product data from the JSON file.
function showdata($file){
	if (!file_exists($file)) return [];
	return json_decode(file_get_contents($file),true)?:[];
}

// Saves product data into the JSON file with pretty formatting.
function savedata($file,$data){
	return file_put_contents($file, json_encode($data,JSON_PRETTY_PRINT));
}

$products = showdata($storage);

switch ($method) {
	// API 1: Create Product (POST)
	case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        
        if(empty($data['name']) || !isset($data['price']) || !isset($data['quantity'])){
        http_response_code(400);
        echo json_encode(["error" => "Name, price, and quantity are mandatory"]);
        break;
    	}

		if(strlen($data['name']) > 255 || $data['price'] <= 0 || $data['quantity'] < 0) {
            http_response_code(400);
            echo json_encode(["error" => "Validation failed: Check name length, price (>0), or quantity (>=0)."]);
            break;
        }
        $data['id'] = time(); 
        $products[] = $data;
        savedata($storage, $products);
        http_response_code(201);
        echo json_encode($data);	// Success: Created
        break; 

		// API 2: Get Product (GET)
	case 'GET':
		$id = $_GET['id']?? null;
		if ($id === null) {
            echo json_encode($products);
            exit;
        }
		foreach ($products as $items) {
			if($items['id'] == $id){
				echo json_encode($items);
				exit;
			}
		}
		http_response_code(404);
		echo json_encode(["error"=>"Data is Not Found"]);
		break;
		
		// API 3: Update Product (PUT)
	case "PUT":
		$id = $_GET['id']??null;
		$input = json_decode(file_get_contents("php://input"),true);
		foreach($products as &$items){  
			if($items['id'] == $id){
				$items = array_merge($items,$input);
				savedata($storage,$products);
				echo json_encode($items);
				exit;
			}
		}
		http_response_code(404);
		echo json_encode(["error"=>"update Fail!"]);
		break;
	}
?>