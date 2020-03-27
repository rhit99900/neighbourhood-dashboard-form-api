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

    // Creating Array for Options 
    $category = [
        'essential_supplies' => 'Essential Supplies',
        'social_distancing'  => 'Social Distancing',
        'essential_supplies_needed' => 'Need Supplies',
        'needy_supplies'     =>  'Need Supplies',
        'nominate_person'    =>  'Get Supplies',
    ];

    $location = [
        'apartment' => 'Apartment',
        'shops'     => 'Shops',
        'company'   => 'Company/Places of Work',
        'street'    => 'Roads/Streets',
        'other_loc' => 'Others'
    ];

    $items = [
        'groceries' => 'Vegetables/Food/Groceries',
        'fuel'      => 'Fuel/LPG/Gas',
        'money'     => 'Money/Funds',
        'medical'   => 'Medicines/Medical Services/Hospitals',
        'other_ess' => 'Others'
    ];
    
    $data_added = true;
    $message = [];        

    // instantiate database and product object
    $database = new Database();
    $db = $database->getConnection();
    
    // initialize object
    // Body of Request
    $form_data = json_decode(file_get_contents('php://input'),true); 
        
    // Getting Number of Responses from the Form Sections    
    if(isset($form_data['social_distance_details'])){
        $count = count($form_data['social_distance_details']);
    }
    else if(isset($form_data['essentials_repeat'])){
        $count = count($form_data['essentials_repeat']);
    }
    else if(isset($form_data['nominee_repeat'])){
        $count = count($form_data['nominee_repeat']);
    }
    else{
        $count = 1;
    }
    
    // for ($i = 1, $i <= $count; $i)

    $data = new Data($db);    

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
                            $data->name .= ' - '.$location[$value['social_distance_details/details/location']];
                        }
                    }
                    else{
                        $data->fillable = false;
                        continue;
                    }

                    if(isset($value['social_distance_details/details/loc_geocode'])){
                        $data->address = $value['social_distance_details/details/loc_geocode'];
                        $data->setLocation($data->address);
                    }
                    else{
                        $data->fillable = false;
                        continue;
                    }

                    if(isset($value['social_distance_details/details/social_distancing'])){
                        if($value['social_distance_details/details/social_distancing']=='no')
                            $data->subcategory = 'Poor';
                        else
                            $data->subcategory = 'Good';
                    }
                    else{
                        $data->fillable = false;
                        continue;
                    }

                    if($data->create()){
                        $message[] = [
                            'response_code' => '201',
                            'message'       => 'Data Added'
                        ];
                    }
                    else{
                        $data_added = false;
                        $message[] = [
                            'response_code' => '503',
                            'message'       => 'Data not added, Not enough parameters'
                        ];
                    }
                }                
            }            
        }
        // Data is From "Essential Supplies" Section of Form 
        else if($form_data_type=='essential_supplies'){
            if(isset($form_data['essentials_repeat'])){
                $essentials_repeat = $form_data['essentials_repeat'];
                foreach ($essentials_repeat as $key => $value){                    

                    if(isset($value['essentials_repeat/essentials_geocode'])){
                        $data->address = $value['essentials_repeat/essentials_geocode'];
                        $data->setLocation($data->address);
                    }
                    else{
                        $data->fillable = false;
                        continue;
                    }                    

                    if(isset($value['essentials_repeat/essential_sup_choice'])){
                        $data->subcategory = ucwords($value['essentials_repeat/essential_sup_choice']);
                        $data->name = ucwords($value['essentials_repeat/essential_sup_choice']);
                    }
                    else{
                        $data->fillable = false;
                        continue;
                    }

                    if(isset($value['essentials_repeat/essentials_name'])){
                        $data->name = $value['essentials_repeat/essentials_name'];
                        if(isset($value['social_distance_details/details/location_landmark'])){
                            $data->name .= ' '.$value['social_distance_details/details/location_landmark'];
                        }
                        if(isset($value['social_distance_details/details/location'])){
                            $data->name .= ' - '.ucwords($value['social_distance_details/details/location']);
                        }
                    }

                    if($data->create()){
                        $message[] = [
                            'response_code' => '201',
                            'message'       => 'Data Added'
                        ];
                    }
                    else{
                        $data_added = false;
                        $message[] = [
                            'response_code' => '503',
                            'message'       => 'Data not added, Not enough parameters'
                        ];
                    }
                }
            }

        }
        // Data is From "I need essential supplies" Section of Form 
        else if($form_data_type=='essential_supplies_needed'){
            if(isset($form_data['essentials_needed_grp/essential_needed_choice'])){
                $data->subcategory = ucwords($form_data['essentials_needed_grp/essential_needed_choice']);
                $data->name = ucwords($form_data['essentials_needed_grp/essential_needed_choice']);                
            }
            else{
                $data->fillable = false;                
            }            

            if(isset($form_data['essentials_needed_grp/essential_needed_address'])){
                $data->address = $form_data['essentials_needed_grp/essential_needed_address'].' ';
            }
            else{
                $data->address = '';
            }

            if(isset($form_data['essentials_needed_grp/essentials_needed_geocode'])){
                $data->address .= $form_data['essentials_needed_grp/essentials_needed_geocode'];
                $data->setLocation($form_data['essentials_needed_grp/essentials_needed_geocode']);
            }
            else{
                $data->fillable = false;                
            }    

            if($data->create()){
                $message[] = [
                    'response_code' => '201',
                    'message'       => 'Data Added'
                ];
            }
            else{
                $data_added = false;
                $message[] = [
                    'response_code' => '503',
                    'message'       => 'Data not added, Not enough parameters'
                ];
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
                foreach ($essentials_repeat as $key => $value){
                    if(isset($value['essentials_repeat/essentials_name'])){
                        $data->name = ucwords($value['essentials_repeat/essentials_name']);
                    }
                    else{
                        $data->fillable = false;                
                        continue;
                    }

                    if(isset($value['essentials_repeat/essential_sup_choice'])){
                        $data->subcategory = ucwords($value['essentials_repeat/essential_sup_choice']);
                    } 
                    else{
                        $data->fillable = false;                
                        continue;
                    }             

                    if(isset($value['essentials_repeat/essentials_geocode'])){
                        $data->address = $value['essentials_repeat/essentials_geocode'];
                        $data->setLocation($value['essentials_repeat/essentials_geocode']);
                    }
                    else{
                        $data->fillable = false;                
                        continue;
                    }    

                    if($data->create()){
                        $message[] = [
                            'response_code' => '201',
                            'message'       => 'Data Added'
                        ];
                    }
                    else{
                        $data_added = false;
                        $message[] = [
                            'response_code' => '503',
                            'message'       => 'Data not added, Not enough parameters'
                        ];
                    }
                }
            }
        }
        else if($form_data_type=='nominate_person'){
            if(isset($form_data['nominee_repeat'])){
                $nominee_repeat = $form_data['nominee_repeat'];
                foreach($nominee_repeat as $key => $value){
                    if(isset($value['nominee_repeat/lab_name'])){
                        $data->name = $value['nominee_repeat/lab_name'];
                        if(isset($value['nominee_repeat/job_desc'])){
                            $data->name .= ' ('.ucwords($value['nominee_repeat/job_desc']).')';
                        }
                    }
                    else{      
                        $data->fillable = false;                  
                        continue;
                    }

                    if(isset($value['nominee_repeat/contact'])){
                        $data->address = $value['nominee_repeat/contact'];
                    }
                    else{
                        $data->address = '';
                    }

                    if(isset($value['nominee_repeat/geocode'])){
                        $data->address .= ' '.$value['nominee_repeat/geocode'];
                        $data->setLocation($value['nominee_repeat/geocode']);
                    }
                    else{      
                        $data->fillable = false; 
                        continue;
                    }

                    if(isset($value['nominee_repeat/lab_help_required'])){
                        $data->subcategory = ucwords($value['nominee_repeat/lab_help_required']);
                    }
                    else{
                        $data->fillable = false;                
                        continue;
                    }

                    if(isset($value['nominee_repeat/lab_phone_num'])){
                        $data->number = $value['nominee_repeat/lab_phone_num'];
                    }
                    else{
                        $data->fillable = false;                
                        continue;
                    }


                    if($data->create()){
                        $message[] = [
                            'response_code' => '201',
                            'message'       => 'Data Added'
                        ];
                    }
                    else{
                        $data_added = false;
                        $message[] = [
                            'response_code' => '503',
                            'message'       => 'Data not added, Not enough parameters'
                        ];
                    }
                }
            }
        }
        else{
            http_response_code(400);                
            echo json_encode(array("message" => "Unable to create product. Data is incomplete."));
        }

    }
    // Error:: Send Error Code 400 with Error Message
    else{
        http_response_code(400);                
        echo json_encode(array("message" => "Unable to create product. Data is incomplete."));
    }
    

    if($data_added){
        http_response_code(201);  
        echo json_encode($message);
    }
    else{
        http_response_code(503);
        echo json_encode($message);
    }




