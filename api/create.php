<?php
    // required headers
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: POST");
    header("Access-Control-Max-Age: 3600");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
        
    // include database and object files
    include_once './config/database.php';
    include_once './models/data.php';
    
    // instantiate database and product object
    $database = new Database();
    $db = $database->getConnection();
    
    // initialize object
    $data = new Data($db);

    // Body of Request
    $form_data = json_decode(file_get_contents('php://input'),true);    


    $category = [
        'essential_supplies' => 'Essential Supplies',
        'social_distancing'  => 'Social Distancing',
        'essential_supplies_needed' => 'Need Supplies',
        'needy_supplies'     =>  'Need Supplies',
        'nominate_person'    =>  'Get Supplies',
    ];
    
    // Data Is from "Social Distancing" Form
    if(isset($form_data['qnnr_type/data_type_choice'])){

        $form_data_type = $form_data['qnnr_type/data_type_choice'];
        // Category of Information
        $data->category = $category[$form_data_type];        
        // Data is From "Social Distance" Section of Form
        if($form_data_type=='social_distancing'){
            if(isset($form_data['social_distance_details'])){
                $social_distance_details = $form_data['social_distance_details'];
                foreach ($social_distance_details as $key => $value) {
                    if(isset($value['social_distance_details/details/location_name'])){
                        $data->name = $value['social_distance_details/details/location_name'];
                        if(isset($value['social_distance_details/details/location_landmark'])){
                            $data->name .= ' '.$value['social_distance_details/details/location_landmark'];
                        }
                        if(isset($value['social_distance_details/details/location'])){
                            $data->name .= ' - '.ucwords($value['social_distance_details/details/location']);
                        }
                    }
                    else{
                        continue;
                    }

                    if(isset($value['social_distance_details/details/loc_geocode'])){
                        $data->address = $value['social_distance_details/details/loc_geocode'];
                        $data->setLocation($data->address);
                    }
                    else{
                        continue;
                    }

                    if(isset($value['social_distance_details/details/social_distancing'])){
                        if($value['social_distance_details/details/social_distancing']=='no')
                            $data->subcategory = 'Poor';
                        else
                            $data->subcategory = 'Good';
                    }
                    else{
                        continue;
                    }
                }                           
            }            
        }

        else if($form_data_type=='essential_supplies'){
            if(isset($form_data['essentials_repeat'])){
                $essentials_repeat = $form_data['essentials_repeat'];
                foreach ($essentials_repeat as $key => $value){
                    if(isset($value['essentials_repeat/essentials_name'])){
                        $data->name = $value['essentials_repeat/essentials_name'];
                        if(isset($value['social_distance_details/details/location_landmark'])){
                            $data->name .= ' '.$value['social_distance_details/details/location_landmark'];
                        }
                        if(isset($value['social_distance_details/details/location'])){
                            $data->name .= ' - '.ucwords($value['social_distance_details/details/location']);
                        }
                    }
                    else{
                        continue;
                    }
                    if(isset($value['essentials_repeat/essentials_geocode'])){
                        $data->address = $value['essentials_repeat/essentials_geocode'];
                        $data->setLocation($data->address);
                    }
                    if(isset($value['essentials_repeat/essential_sup_choice'])){
                        $data->subcategory = ucwords($value['essentials_repeat/essential_sup_choice']);
                    }
                }
            }

        }


    }
    // Data Is from "Help the Needy" Form
    else if(isset($form_data['details/dwe_data_type'])){

        $form_data_type = $form_data['details/dwe_data_type'];  
        // Category of Information
        $data->category = $category[$form_data_type];        
        if($form_data_type=='needy_supplies'){
            if(isset($form_data['essentials_repeat'])){
                $essentials_repeat = $form_data['essentials_repeat'];
                var_dump($essentials_repeat);
                foreach ($essentials_repeat as $key => $value){
                    
                }
            }
        }
        else if($form_data_type=='nominate_person'){

        }
        else{
            http_response_code(400);                
            echo json_encode(array("message" => "Unable to create product. Data is incomplete."));
        }

    }
    // Error
    else{
        http_response_code(400);                
        echo json_encode(array("message" => "Unable to create product. Data is incomplete."));
    }

    // Adding Attached Image(s) Link
    if(isset($form_data['_attachments'])){  
        $attachments = $form_data['_attachments'];  
        $data->info = '';
        foreach ($attachments as $key => $value) {
            if(isset($value['download_url'])){
                $data->info .= $value['download_url'].' ';
            }
        }     
    }
    else{
        $data->info = '';
    }

    // Adding Contact Number 
    if(isset($form_data['contact_details/phone_num'])){
        $data->number = $form_data['contact_details/phone_num'];
    }
    else{
        $data->number = '';
    }

    var_dump($data);
    exit;

    if($data->create()){
        http_response_code(201);  
        echo json_encode(array("message" => "Data Added."));
    }
    else{
        http_response_code(503);
        echo json_encode(array("message" => "Unable to Add Data."));
    }




