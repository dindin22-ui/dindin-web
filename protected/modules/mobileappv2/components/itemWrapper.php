<?php
class itemWrapper
{
	static $sizes = array();
	static $enabled_trans=false;
	
	public static function setMultiTranslation()
	{
		$enabled_trans=getOptionA('enabled_multiple_translation');
		if($enabled_trans==2){
			self::$enabled_trans = true;
		}
	}
	
// 	public static function getMenu($merchant_id='', $pagenumber=0, $pagelimit=10, $cat_id='') // the function in which don chicos menus are comming
// 	{	
// 		$p = new CHtmlPurifier();
		
// 		$paginate_total=0;
// 		$menu_type = mobileWrapper::getMenuType();
		
// 		$default_image='';
		
// 		$disabled_default_image = getOptionA('mobile2_disabled_default_image');
// 		$merchant_menu_type = getOptionA('mobileapp2_merchant_menu_type');		
// 		if($merchant_menu_type==3){
// 			$default_image='resto_banner.jpg';
// 			$disabled_default_image=false;
// 		}
		
// 		if($merchant_id>0){
// 			self::$sizes = self::getSize($merchant_id);
			
// 			$todays_day = date("l");
//             $todays_day = !empty($todays_day)?strtolower($todays_day):'';
            
//             $and='';
            
//             $enabled_category_sked = getOption($merchant_id,'enabled_category_sked'); 
//             if($enabled_category_sked==1){
//     		    $and .= " AND $todays_day='1' ";
//     	    }   
//             if($enabled_category_sked==1){
//     		    $and .= " AND $todays_day='1' ";
//     	    }   
//     	    if($cat_id !=''){
//     	        $and .= " AND cat_id= '$cat_id' ";
//     	    }
    	   
//     	    $db = new DbExt();
    	   
//     	   // popular_item start
                
//             if($pagenumber == 0){
            
//         	    $popular_item = "SELECT SUM(a.qty) as total_qty,
//                 a.item_id, a.item_name,a.discounted_price,a.size, b.*
//                 FROM
//                 mt_view_order_details a     
//             	left join mt_item b
//             	ON
//                 a.item_id=b.item_id
//                 WHERE
//                 a.merchant_id = ".FunctionsV3::q($merchant_id)."
//                 AND a.status NOT IN ('initial_order')
//                 GROUP BY a.item_id, a.size
//                 ORDER BY total_qty DESC
//                 LIMIT 5";
//                 $popularItems = $db->rst($popular_item);
                
                
                
//                 $popularitemall = array();
                
//                 if( $popularItems &&  (count($popularItems) == 5) ){
//                 	$popularitemall = array(
//             	        'cat_id' => '987654321',
//                         'category_name' => 'Popular Items',
//                         'category_description' =>'Popular Items', 
//                         'category_pic' => 'https://dindin.site/protected/modules/mobileappv2/assets/images/mobile-default-logo.png',
//                         'item' => array()
//             	       );
                    
//                     foreach($popularItems as $keypop => $popularItem){
//                         $popular['item_id'] = $popularItem['item_id'];
//                         $popular['merchant_id'] = $popularItem['merchant_id'];
//                         $popular['item_name'] = $popularItem['item_name'];
//                         $popular['item_description'] = $popularItem['item_description'];
//                         if($popularItem['item_description'] == ''){
//                             $popular['item_description'] = '';
//                         }
                        
//                         $popular['item_name_trans'] = $popularItem['item_name_trans'];
//                         $popular['item_description_trans'] = $popularItem['item_description_trans'];
//                         $popular['status'] = $popularItem['status'];
//                         $popular['price'] = $popularItem['price'];
                        
//                 		$popular['photo']=mobileWrapper::getImage($popularItem['photo'],$default_image,$disabled_default_image);                    
                        
//                         $popular['discount'] = $popularItem['discount'];
//                         $popular['dish'] = $popularItem['dish'];
//                         if($popularItem['dish'] == ''){
//                             $popular['dish'] = '';
//                         }                    
//                         if (json_decode($popularItem['price'])){
//     					    $price = json_decode($popularItem['price'],true);					
    						
//     					        foreach ($price as $size_id=>$priceval) {
//             						if($popularItem['discount']>=0.001){
//             							$priceval = $priceval-$popularItem['discount'];
//             						}					
//             						if(array_key_exists($size_id,(array)self::$sizes)){
//             						    if(strval($size_id) == 0){
//                 						    $set = array(
//                 						            'id'=>'standard',
//                 						            'name'=>'regular',
//                 						            'price'=>FunctionsV3::prettyPrice($priceval)
//                 						        );						        
//             						    }
//             						    else{
//             						    $set = array(
//             						            'id'=> strval($size_id),
//             						            'name'=>self::$sizes[$size_id],
//             						            'price'=>FunctionsV3::prettyPrice($priceval)
//             						        );						        
//             						    }
//             							$prices[]=$set;
//             						} else {					
//             						    $set = array(
//             						            'id'=>'standard',
//             						            'name'=>'regular',
//             						            'price'=>FunctionsV3::prettyPrice($priceval)
//             						        );
//             							$prices[]=$set;
            							
            							
//             						}
//     					        }					
//     				    } 
//                         $popular['prices'] = $prices;
        
        
//                         $category = json_decode($popularItem['category']);
        
//                         if (json_last_error() === JSON_ERROR_NONE) {
//                             $popular['cat_id'] = reset($category);
//                         }
//                         else{
//                             $popular['cat_id'] = $popularItem['item_id'];
//                         }
//                         $popular['dish_image'] ='';
                        
//                         array_push($popularitemall['item'], $popular);
//                     }
                    
                    
//     		    }
		    
// 		    }
//     	   // popular_item end


    	   
// 			$stmt="
// 			SELECT SQL_CALC_FOUND_ROWS *
// 			FROM
// 			{{category}}
// 			WHERE
// 			merchant_id = ".FunctionsV3::q($merchant_id)."
// 			AND status in ('publish','published')
// 			$and
// 			ORDER BY sequence,date_created ASC
// 			LIMIT $pagenumber,$pagelimit
// 			";
// // 			dump($stmt);

// 			if($res = $db->rst($stmt)){
// 				$total_records=0;
// 				$stmtc="SELECT FOUND_ROWS() as total_records";
// 				if ($resp=$db->rst($stmtc)){			 			
// 					$total_records=$resp[0]['total_records'];
// 				}		
				
// 				$paginate_total = ceil( $total_records / $pagelimit );
				
// 				$popular = array();
			        
// 				foreach ($res as $val) {
// 					$new_data['cat_id']=$val['cat_id'];
// 					$new_data['category_name']=$val['category_name'];
			
// 					$new_data['category_description']=$val['category_description'];
// 					$new_data['category_pic']=mobileWrapper::getImage($val['photo'],$default_image,$disabled_default_image);
					
// 					if(self::$enabled_trans==TRUE){
// 					  $category_name['category_name_trans']=!empty($val['category_name_trans'])?json_decode($val['category_name_trans'],true):'';
// 					  $new_data['category_name'] = qTranslate($new_data['category_name'],'category_name',$category_name);
					  
// 					  $category_description['category_description_trans']=!empty($val['category_description_trans'])?json_decode($val['category_description_trans'],true):'';
// 					  $new_data['category_description'] = qTranslate($new_data['category_description'],'category_description',$category_description);					  
// 					}
					
					
// 					$new_data['category_description'] = self::limitText($p,$new_data['category_description']);
					
// 					//dump($menu_type);					
// 					if($menu_type==1){
// 					   $item_data = self::getItemByCategory($merchant_id,$val['cat_id'],false);
// 					    $new_data['item'] = is_array($item_data['data'])?$item_data['data']:array();
// 					}
// 					$data[]=$new_data;
// 				}

// 				// popular_item condition
// 				if(!empty($popularitemall)){
// 				    array_unshift($data,$popularitemall);  
// 				}
// 				// popular_item condition
				
// 				return array(
// 				  'paginate_total'=>$paginate_total,
// 				  'list'=>$data
// 				);
// 			}
// 			unset($db);
// 		}
// 		return false;
// 	}
	
	
	public static function getMenu($merchant_id='', $pagenumber=0, $pagelimit=10, $cat_id='', $timezone='')  //was not showing the corrcect at Don Chicos Menu
	{
	    
	    $tz = $timezone;
        $tz_obj = new DateTimeZone($tz);
        $today = new DateTime("now", $tz_obj);
        $format = $today->modify('+1 hour');
        $today_formatted = $today->format('h:i A');
        
        $tz = $timezone;
        $tz_obj = new DateTimeZone($tz);
        $today = new DateTime("now", $tz_obj);
        $day = $today->format('l');
        $day = strtolower($day);
        
        $p = new CHtmlPurifier();
  
		$paginate_total=0;
		$menu_type = mobileWrapper::getMenuType();
		
		$default_image='';
		
		$disabled_default_image = getOptionA('mobile2_disabled_default_image');
		$merchant_menu_type = getOptionA('mobileapp2_merchant_menu_type');		
		if($merchant_menu_type==3){
			$default_image='resto_banner.jpg';
			$disabled_default_image=false;
		}
		
		if($merchant_id>0){
			self::$sizes = self::getSize($merchant_id);
			
			$todays_day = date("l");
            $todays_day = !empty($todays_day)?strtolower($todays_day):'';
            
            $and='';
            
            $enabled_category_sked = getOption($merchant_id,'enabled_category_sked'); 
            if($enabled_category_sked==1){
    		    $and .= " AND $todays_day='1' ";
    	    }   
            if($enabled_category_sked==1){
    		    $and .= " AND $todays_day='1' ";
    	    }   
    	    if($cat_id !=''){
    	        $and .= " AND cat_id= '$cat_id' ";
    	    }
    	   
    	    $db = new DbExt();
    	   
    	   // popular_item start
    	 
            if($pagenumber == 0){
        	    $popular_item = "SELECT SUM(a.qty) as total_qty,
                a.item_id, a.item_name,a.discounted_price,a.size, b.*
                FROM
                mt_view_order_details a     
            	left join mt_item b
            	ON
                a.item_id=b.item_id
                WHERE
                a.merchant_id = ".FunctionsV3::q($merchant_id)."
                AND a.status NOT IN ('initial_order')
				AND b.item_id IS NOT NULL
                GROUP BY a.item_id, a.size
                ORDER BY total_qty DESC
                LIMIT 5";
                $popularItems = $db->rst($popular_item);
                
                $popularitemall = array();
                
                $popularItemFlag = true;
                
                if( $popularItems &&  (count($popularItems) == 5) ){
                	$popularitemall = array(
            	        'cat_id' => '987654321',
                        'category_name' => 'Popular Items',
                        'category_description' =>'Popular Items', 
                        'category_pic' => 'https://dindin.site/protected/modules/mobileappv2/assets/images/mobile-default-logo.png',
                        'item' => array()
            	       );
                    
                    foreach($popularItems as $keypop => $popularItem){
                    
                        if(empty($popularItem['item_id'])){
                            $popularItemFlag = false;
                            continue;
                        }                    
                    
                        $popular['item_id'] = $popularItem['item_id'];
                        $popular['merchant_id'] = $popularItem['merchant_id'];
                        $popular['item_name'] = $popularItem['item_name'];
                        $popular['item_description'] = self::limitText($p, $popularItem['item_description']);;
                        if($popularItem['item_description'] == ''){
                            $popular['item_description'] = '';
                        }
                        
                        $popular['item_name_trans'] = $popularItem['item_name_trans'];
                        if(empty($popular['item_name_trans'])){
                            $popular['item_name_trans'] = "";
                        }
                        $popular['item_description_trans'] = $popularItem['item_description_trans'];
                        if(empty($popular['item_description_trans'])){
                            $popular['item_description_trans'] = "";
                        }
                        $popular['status'] = $popularItem['status'];
                        $popular['price'] = $popularItem['price'];
                        
                		$popular['photo']=mobileWrapper::getImage($popularItem['photo'],$default_image,$disabled_default_image);                    
                        
                        $popular['discount'] = $popularItem['discount'];
                        $popular['dish'] = $popularItem['dish'];
                        if($popularItem['dish'] == ''){
                            $popular['dish'] = '';
                        }                    
                        if (json_decode($popularItem['price'])){
    					    $price = json_decode($popularItem['price'],true);					
    					        foreach ($price as $size_id=>$priceval) {
    					            $price=''; $prices = array();
            						if($popularItem['discount']>=0.001){
            							$priceval = $priceval-$popularItem['discount'];
            						}					
            						if(array_key_exists($size_id,(array)self::$sizes)){
            						    if(strval($size_id) == 0){
                						    $set = array(
                						            'id'=>'standard',
                						            'name'=>'regular',
                						            'price'=>FunctionsV3::prettyPrice($priceval)
                						        );						        
            						    }
            						    else{
            						    $set = array(
            						            'id'=> strval($size_id),
            						            'name'=>self::$sizes[$size_id],
            						            'price'=>FunctionsV3::prettyPrice($priceval)
            						        );						        
            						    }
            							$prices[]=$set;
            						} else {					
            						    $set = array(
            						            'id'=>'standard',
            						            'name'=>'regular',
            						            'price'=>FunctionsV3::prettyPrice($priceval)
            						        );
            							$prices[]=$set;
            							
            							
            						}
    					        }					
    				    } 
                        $popular['prices'] = $prices;
        
        
                        $category = json_decode($popularItem['category']);
        
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $popular['cat_id'] = reset($category);
                        }
                        else{
                            $popular['cat_id'] = $popularItem['item_id'];
                        }
                        $popular['dish_image'] ='';
                        
                        array_push($popularitemall['item'], $popular);
                    }
                    
                    
    		    }
		    
		    }
		    
// 	print_r($popularitemall);	    exit('mkjhg');
    	   // popular_item end

            $start_date = $day. '_start_time';
            $end_date = $day. '_end_time';
           
			$stmt="
			SELECT SQL_CALC_FOUND_ROWS *
			FROM
			{{category}}
			WHERE
			merchant_id = ".FunctionsV3::q($merchant_id)."
			AND status in ('publish','published')
			$and
			ORDER BY sequence,date_created ASC
			LIMIT $pagenumber,$pagelimit
			";
            
            // dump($stmt);
            
			if($res = $db->rst($stmt)){
				$total_records=0;
				$stmtc="SELECT FOUND_ROWS() as total_records  ";
				if ($resp=$db->rst($stmtc)){			 			
					$total_records=$resp[0]['total_records'];
				}
				
				// dump($stmt);
				
				$merchant_category_stmt_count = "SELECT $day FROM {{category}} WHERE merchant_id = ".FunctionsV3::q($merchant_id)." AND cat_id = ".$val['cat_id']." AND $day = 1 LIMIT 0,1" ;
                $mechant_res_cat_count = $db->rst($merchant_category_stmt);
                
                //$total_records = count($mechant_res_cat_count);
				// print_r($total_records); exit('dsff');
				
				$paginate_total = ceil( $total_records / $pagelimit );
	
				$popular = array();
			        
				foreach ($res as $val) {
				   
				    
				  
                    $merchant_category_stmt = "SELECT $day FROM {{category}} WHERE merchant_id = ".FunctionsV3::q($merchant_id)." AND cat_id = ".$val['cat_id']." AND $day = 1 LIMIT 0,1" ;
                    $mechant_res_cat = $db->rst($merchant_category_stmt);
                    
                //   print_r( $mechant_res_cat[0][$day]); exit('dscds');
                    if($mechant_res_cat){
                        
                             
                          
                            if($mechant_res_cat[0][$day] == 1){
                                
                                $today_formatted_data = date('Y-m-d h:i A', strtotime($today_formatted));
                                
                                $start_date_data = date('Y-m-d h:i A', strtotime($val[$start_date])); 
                                
                                $end_date_data = date('Y-m-d h:i A', strtotime($val[$end_date])); 

                                    if( strtotime($today_formatted_data)  < strtotime($start_date_data)){
                                        continue;
                                    }
                                    
                                    if( strtotime($today_formatted_data)  > strtotime($end_date_data)){
                                        continue;
                                    }
                                    
                                    //  if($today_formatted_data  == $end_date_data){
                                        
                                    //     print_r('equal'); exit('equal');
                                        
                                    //  }
                                    
                            }
                    }
                    
                    
               
                    
					$new_data['cat_id']=$val['cat_id'];
					$new_data['category_name']=$val['category_name'];
					
					
					    
			
					$new_data['category_description']=$val['category_description'];
					$new_data['category_pic']=mobileWrapper::getImage($val['photo'],$default_image,$disabled_default_image);
					
					if(self::$enabled_trans==TRUE){
					  $category_name['category_name_trans']=!empty($val['category_name_trans'])?json_decode($val['category_name_trans'],true):'';
					  $new_data['category_name'] = qTranslate($new_data['category_name'],'category_name',$category_name);
					  
					  $category_description['category_description_trans']=!empty($val['category_description_trans'])?json_decode($val['category_description_trans'],true):'';
					  $new_data['category_description'] = qTranslate($new_data['category_description'],'category_description',$category_description);					  
					}

					$new_data['category_description'] = self::limitText($p,$new_data['category_description']);

					//dump($menu_type);					
				 	if($menu_type==1){
				 	   $item_data = self::getItemByCategory($merchant_id,$val['cat_id'],false);
				 	    $new_data['item'] = is_array($item_data['data'])?$item_data['data']:array();
					}
					
					$data[]=$new_data;
				}
				
				// print_r($new_data); exit('zxcds');


                // print_r($popularitemall); exit('zxcds');
                // print_r($data); exit('zxcds');
				// popular_item condition
                if($popularItemFlag){
                    
    				if(!empty($popularitemall) && !empty($data)){
    				    array_unshift($data,$popularitemall);  
    				}
                }
	
				// popular_item condition
				
				if(empty($data)){
				    
				   
				   
				    $data = array();
				}
				
				return array(
				  'paginate_total'=>$paginate_total,
				  'list'=>$data
				);
			}
			unset($db);
		}
		return false;
	}
	

	public static function getMenuTest($merchant_id='', $pagenumber=0, $pagelimit=10, $cat_id='', $timezone='')  //was not showing the corrcect at Don Chicos Menu
	{
	    $tz = $timezone;
        $tz_obj = new DateTimeZone($tz);
        $today = new DateTime("now", $tz_obj);
        $format = $today->modify('+1 hour');
        $today_formatted = $today->format('h:i A');
        
        //testing purpose
        // $today_formatted = '09:00 AM';
        
        $tz = $timezone;
        $tz_obj = new DateTimeZone($tz);
        $today = new DateTime("now", $tz_obj);
        $day = $today->format('l');
        $day = strtolower($day);
        
        // echo " hu"; echo "<pre>"; print_r($day); exit('d'); 
        
        $p = new CHtmlPurifier();
  
		$paginate_total=0;
		$menu_type = mobileWrapper::getMenuType();
		
		$default_image='';
		
		$disabled_default_image = getOptionA('mobile2_disabled_default_image');
		$merchant_menu_type = getOptionA('mobileapp2_merchant_menu_type');		
		if($merchant_menu_type==3){
			$default_image='resto_banner.jpg';
			$disabled_default_image=false;
		}
		
		if($merchant_id>0){
			self::$sizes = self::getSize($merchant_id);
			
			$todays_day = date("l");
            $todays_day = !empty($todays_day)?strtolower($todays_day):'';
            
            $and='';
            
            $enabled_category_sked = getOption($merchant_id,'enabled_category_sked'); 
            if($enabled_category_sked==1){
    		    $and .= " AND $todays_day='1' ";
    	    }   
            if($enabled_category_sked==1){
    		    $and .= " AND $todays_day='1' ";
    	    }   
    	    if($cat_id !=''){
    	        $and .= " AND cat_id= '$cat_id' ";
    	    }
    	   
    	    $db = new DbExt();
    	   
    	   // popular_item start
    	 
            if($pagenumber == 0){
        	    $popular_item = "SELECT SUM(a.qty) as total_qty,
                a.item_id, a.item_name,a.discounted_price,a.size, b.*
                FROM
                mt_view_order_details a     
            	left join mt_item b
            	ON
                a.item_id=b.item_id
                WHERE
                a.merchant_id = ".FunctionsV3::q($merchant_id)."
                AND a.status NOT IN ('initial_order')
                GROUP BY a.item_id, a.size
                ORDER BY total_qty DESC
                LIMIT 5";
                $popularItems = $db->rst($popular_item);
                
                $popularitemall = array();
                
                $popularItemFlag = true;
                
                if( $popularItems &&  (count($popularItems) == 5) ){
                	$popularitemall = array(
            	        'cat_id' => '987654321',
                        'category_name' => 'Popular Items',
                        'category_description' =>'Popular Items', 
                        'category_pic' => 'https://dindin.site/protected/modules/mobileappv2/assets/images/mobile-default-logo.png',
                        'item' => array()
            	       );
                    
                    foreach($popularItems as $keypop => $popularItem){
                    
                        if(empty($popularItem['item_id'])){
                            $popularItemFlag = false;
                            continue;
                        }                    
                    
                        $popular['item_id'] = $popularItem['item_id'];
                        $popular['merchant_id'] = $popularItem['merchant_id'];
                        $popular['item_name'] = $popularItem['item_name'];
                        $popular['item_description'] = self::limitText($p, $popularItem['item_description']);;
                        if($popularItem['item_description'] == ''){
                            $popular['item_description'] = '';
                        }
                        
                        $popular['item_name_trans'] = $popularItem['item_name_trans'];
                        if(empty($popular['item_name_trans'])){
                            $popular['item_name_trans'] = "";
                        }
                        $popular['item_description_trans'] = $popularItem['item_description_trans'];
                        if(empty($popular['item_description_trans'])){
                            $popular['item_description_trans'] = "";
                        }
                        $popular['status'] = $popularItem['status'];
                        $popular['price'] = $popularItem['price'];
                        
                		$popular['photo']=mobileWrapper::getImage($popularItem['photo'],$default_image,$disabled_default_image);                    
                        
                        $popular['discount'] = $popularItem['discount'];
                        $popular['dish'] = $popularItem['dish'];
                        if($popularItem['dish'] == ''){
                            $popular['dish'] = '';
                        }                    
                        if (json_decode($popularItem['price'])){
    					    $price = json_decode($popularItem['price'],true);					
    					        foreach ($price as $size_id=>$priceval) {
    					            $price=''; $prices = array();
            						if($popularItem['discount']>=0.001){
            							$priceval = $priceval-$popularItem['discount'];
            						}					
            						if(array_key_exists($size_id,(array)self::$sizes)){
            						    if(strval($size_id) == 0){
                						    $set = array(
                						            'id'=>'standard',
                						            'name'=>'regular',
                						            'price'=>FunctionsV3::prettyPrice($priceval)
                						        );						        
            						    }
            						    else{
            						    $set = array(
            						            'id'=> strval($size_id),
            						            'name'=>self::$sizes[$size_id],
            						            'price'=>FunctionsV3::prettyPrice($priceval)
            						        );						        
            						    }
            							$prices[]=$set;
            						} else {					
            						    $set = array(
            						            'id'=>'standard',
            						            'name'=>'regular',
            						            'price'=>FunctionsV3::prettyPrice($priceval)
            						        );
            							$prices[]=$set;
            							
            							
            						}
    					        }					
    				    } 
                        $popular['prices'] = $prices;
        
        
                        $category = json_decode($popularItem['category']);
        
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $popular['cat_id'] = reset($category);
                        }
                        else{
                            $popular['cat_id'] = $popularItem['item_id'];
                        }
                        $popular['dish_image'] ='';
                        
                        array_push($popularitemall['item'], $popular);
                    }
                    
                    
    		    }
		    
		    }
		    
// 	print_r($popularitemall);	    exit('mkjhg');
    	   // popular_item end

            $start_date = $day. '_start_time';
            $end_date = $day. '_end_time';
           
			$stmt="
			SELECT SQL_CALC_FOUND_ROWS *
			FROM
			{{category}}
			WHERE
			merchant_id = ".FunctionsV3::q($merchant_id)."
			AND status in ('publish','published')
			$and
			ORDER BY sequence,date_created ASC
			LIMIT $pagenumber,$pagelimit
			";
            
            // dump($stmt);
            
			if($res = $db->rst($stmt)){
				$total_records=0;
				$stmtc="SELECT FOUND_ROWS() as total_records  ";
				if ($resp=$db->rst($stmtc)){			 			
					$total_records=$resp[0]['total_records'];
				}
				
				// dump($stmt);
				// $merchant_category_stmt_count = "SELECT $day FROM {{category}} WHERE merchant_id = ".FunctionsV3::q($merchant_id)." AND cat_id = ".$val['cat_id']." AND $day = 1 LIMIT 0,1" ;
				$merchant_category_stmt_count = "SELECT $day FROM {{category}} WHERE merchant_id = ".FunctionsV3::q($merchant_id)."  AND $day = 1 LIMIT 0,1" ;
                
                $mechant_res_cat_count = $db->rst($merchant_category_stmt);
                
                $total_records = count($mechant_res_cat_count);
				// print_r($total_records); exit('dsff');
				
				$paginate_total = ceil( $total_records / $pagelimit );
	
				$popular = array();
			        
				foreach ($res as $val) {
				   
                    $merchant_category_stmt = "SELECT $day FROM {{category}} WHERE merchant_id = ".FunctionsV3::q($merchant_id)." AND cat_id = ".$val['cat_id']." AND $day = 1 LIMIT 0,1" ;

                    $mechant_res_cat = $db->rst($merchant_category_stmt);
                    
                  
                    if($mechant_res_cat){
                        
                             
                          
                            if($mechant_res_cat[0][$day] == 1){
                                
                                $today_formatted_data = date('Y-m-d h:i A', strtotime($today_formatted));
                                
                                $start_date_data = date('Y-m-d h:i A', strtotime($val[$start_date])); 
                                
                                $end_date_data = date('Y-m-d h:i A', strtotime($val[$end_date])); 
                                
                                // echo strtotime($today_formatted_data);
                                
                                // // echo strtotime($end_date_data);
                                // exit('huhx');
                                
                                        // if($val['cat_id'] == '1828'){
                                            
                                        //     echo strtotime($today_formatted_data); echo " jijij";  
                                        //     echo strtotime($end_date_data);
                                            
                                        //     // echo $today_formatted_data; echo "<br>";echo $start_date_data; echo "<br>";
                                        //     // echo $end_date_data;
                                        //     exit('in');    
                                        // }                                
                                
                                    if( strtotime($today_formatted_data)  < strtotime($start_date_data)){
                                        // if($val['cat_id'] == '1828'){
                                        //     exit('in');    
                                        // }
                                        
                                        continue;
                                    }
                                    
                                    if( strtotime($today_formatted_data)  > strtotime($end_date_data)){
                                        // if($val['cat_id'] == '1828'){
                                        //     exit('xin');    
                                        // }                                        
                                        continue;
                                    }
                                    
                                    //  if($today_formatted_data  == $end_date_data){
                                        
                                    //     print_r('equal'); exit('equal');
                                        
                                    //  }
                                    
                            }
                    }
                    
                    
               
                    
					$new_data['cat_id']=$val['cat_id'];
					$new_data['category_name']=$val['category_name'];
					
					
					    
			
					$new_data['category_description']=$val['category_description'];
					$new_data['category_pic']=mobileWrapper::getImage($val['photo'],$default_image,$disabled_default_image);
					
					if(self::$enabled_trans==TRUE){
					  $category_name['category_name_trans']=!empty($val['category_name_trans'])?json_decode($val['category_name_trans'],true):'';
					  $new_data['category_name'] = qTranslate($new_data['category_name'],'category_name',$category_name);
					  
					  $category_description['category_description_trans']=!empty($val['category_description_trans'])?json_decode($val['category_description_trans'],true):'';
					  $new_data['category_description'] = qTranslate($new_data['category_description'],'category_description',$category_description);					  
					}

					$new_data['category_description'] = self::limitText($p,$new_data['category_description']);

					//dump($menu_type);					
				 	if($menu_type==1){
				 	   $item_data = self::getItemByCategory($merchant_id,$val['cat_id'],false);
				 	    $new_data['item'] = is_array($item_data['data'])?$item_data['data']:array();
					}
					
					$data[]=$new_data;
				}
				
				// print_r($new_data); exit('zxcds');


                // print_r($popularitemall); exit('zxcds');
                // print_r($data); exit('zxcds');
				// popular_item condition
                if($popularItemFlag){
                    
    				if(!empty($popularitemall) && !empty($data)){
    				    array_unshift($data,$popularitemall);  
    				}
                }
	
				// popular_item condition
				
				if(empty($data)){
				    
				   
				   
				    $data = array();
				}
				
				return array(
				  'paginate_total'=>$paginate_total,
				  'list'=>$data
				);
			}
			unset($db);
		}
		return false;
	}
//testing purpose 	
	public static function getMerchantCategoryNew($merchant_id='', $timezone='')
	{ 
	    $tz = $timezone;
        $tz_obj = new DateTimeZone($tz);
        $today = new DateTime("now", $tz_obj);
        $format = $today->modify('+1 hour');
        $today_formatted = $today->format('h:i A');
        
        $tz = $timezone;
        $tz_obj = new DateTimeZone($tz);
        $today = new DateTime("now", $tz_obj);
        $day = $today->format('l');
        $day = strtolower($day);
        
        $start_date = $day. '_start_time';
        $end_date = $day. '_end_time';
        
		$data = array();
		
		if($merchant_id>0){
			
			$todays_day = date("l");
            $todays_day = !empty($todays_day)?strtolower($todays_day):'';
            
            $and='';
            
            $enabled_category_sked = getOption($merchant_id,'enabled_category_sked'); 
            if($enabled_category_sked==1){
    		    $and .= " AND $todays_day='1' ";
    	    }    	 
    	    
    	    $cart_theme = getOptionA('mobileapp2_cart_theme');
    	    
    	    $db = new DbExt();
    	    
    	   $popular_category_id = "SELECT SUM(a.qty) as total_qty,
            a.item_id, a.item_name,a.discounted_price,a.size, b.item_id, b.category
            FROM
            mt_view_order_details a     
        	left join mt_item b
        	ON
            a.item_id=b.item_id
            WHERE
            a.merchant_id = ".FunctionsV3::q($merchant_id)."
			AND b.item_id IS NOT NULL
            AND a.status NOT IN ('initial_order')
            GROUP BY a.item_id, a.size
            ORDER BY total_qty DESC
            LIMIT 5";            
            $res1 = $db->rst($popular_category_id);
			//print_r($res1); exit('usssssu');
			//popular category
			if($res1){
				$count = 0;

				foreach($res1 as $item){
					$count++;
					$json_cat = json_decode($item['category']);
				}

        	    $popular_category = "SELECT * FROM {{category}}
                                WHERE
                                cat_id in ($json_cat[0])";
                                
    	        $res2 = $db->rst($popular_category);
            
            
              
            
	            $popular_category_list = array();
            
            
						
				// 		print_r($item_count); exit('category');

				foreach($res2 as $category){
					if(empty($category['category_name'])){
						$category['category_name'] = '';
					}

					if(empty($category['category_description'])){
						$category['category_description'] = '';
					}

					if(empty($category['photo'])){
						$category['photo'] = '';
					}

					if(empty($category['status'])){
						$category['status'] = '';
					}

					if(empty($category['category_name_trans'])){
						$category['category_name_trans'] = '';
					}

					if(empty($category['category_name_trans'])){
						$category['category_name_trans'] = '';
					}

					if(empty($category['item_count'])){
						$category['item_count'] = '';
					}

					if(empty($category['count'])){
						$category['count'] = '';
					}

					$item_count = self::getItemCountByCategory($category['cat_id']);
					$category['item_count']= mt("[count] item",array(
							  '[count]'=>$item_count
							));


					// if($item_count >= 5){
					$popular_category_list['cat_id'] = $category['cat_id'];
					$popular_category_list['category_name'] = 'Popular Items';
					$popular_category_list['category_description'] = $category['category_description'];
					$popular_category_list['photo'] = $category['photo'];
					$popular_category_list['status'] = $category['status'];
					$popular_category_list['category_name_trans'] = $category['category_name_trans'];
					$popular_category_list['item_count'] = $count .' '. 'item';
					$popular_category_list['count'] = "$count";
					// }
				}
			}
			
            // popular items end
			$stmt="
			SELECT 
			cat_id,
			category_name,
			category_description,
			photo,
			status,
			category_name_trans,
			category_description_trans
			FROM
			{{category}}
			WHERE
			merchant_id = ".FunctionsV3::q($merchant_id)."
			AND status in ('publish','published')
			$and
			ORDER BY sequence,date_created ASC			
			";					
			if($res = $db->rst($stmt)){			
				
				if($cart_theme==2){
					self::$enabled_trans = true;
				}
					
				if(self::$enabled_trans!=TRUE){
					return $res;
				}				
				foreach ($res as $val) {
				    
				    $merchant_category_stmt = "SELECT $day FROM {{category}} WHERE merchant_id = ".FunctionsV3::q($merchant_id)." AND cat_id = ".$val['cat_id']." AND $day = 1 LIMIT 0,1" ;
                    $mechant_res_cat = $db->rst($merchant_category_stmt);
					//echo " next";
                    if($mechant_res_cat){
                            if($mechant_res_cat[0][$day] == 1){
                                $today_formatted_data = date('Y:m:d h:i A', strtotime($today_formatted));
                                $start_date_data = date('Y:m:d h:i A', strtotime($val[$start_date]));
                                $end_date_data = date('Y:m:d h:i A', strtotime($val[$end_date]));
                                    if(strtotime($today_formatted_data)  < strtotime( $start_date_data) ) {
                                        continue;
                                    }
                                    
                                    if( strtotime( $today_formatted_data)  > strtotime( $end_date_data )){
                                        continue;
                                    }
                                    
                            }
                    }
				    
					$val['category_name'] = qTranslate($val['category_name'],'category_name',array(
					  'category_name_trans'=>json_decode($val['category_name_trans'],true)
					));				
					$val['category_description'] = qTranslate($val['category_description'],'category_description',array(
					  'category_description_trans'=>json_decode($val['category_description_trans'],true)
					));					
					
					if($cart_theme==2){						
						$item_count = self::getItemCountByCategory($val['cat_id']);
						$val['item_count']= mt("[count] item",array(
						  '[count]'=>$item_count
						));
						$val['count']=$item_count;
					}
					
					
					
					$data[]=$val;
					
					if(empty($data)){
					    $data = [];
					}
				}
				if($popular_category_list){
					    array_unshift($data, $popular_category_list);
					}
				return $data;
			}
		}
		return false;
	}	
//testing purpose end
   
	

	
	public static function getMerchantCategory($merchant_id='', $timezone='')
	{
	    $tz = $timezone;
        $tz_obj = new DateTimeZone($tz);
        $today = new DateTime("now", $tz_obj);
        $format = $today->modify('+1 hour');
        $today_formatted = $today->format('h:i A');
        
        $tz = $timezone;
        $tz_obj = new DateTimeZone($tz);
        $today = new DateTime("now", $tz_obj);
        $day = $today->format('l');
        $day = strtolower($day);
        
        $start_date = $day. '_start_time';
        $end_date = $day. '_end_time';
        
		$data = array();
		if($merchant_id>0){
			
			$todays_day = date("l");
            $todays_day = !empty($todays_day)?strtolower($todays_day):'';
            
            $and='';
            
            $enabled_category_sked = getOption($merchant_id,'enabled_category_sked'); 
            if($enabled_category_sked==1){
    		    $and .= " AND $todays_day='1' ";
    	    }    	 
    	    
    	    $cart_theme = getOptionA('mobileapp2_cart_theme');
    	    
    	    $db = new DbExt();
    	    
    	   $popular_category_id = "SELECT SUM(a.qty) as total_qty,
            a.item_id, a.item_name,a.discounted_price,a.size, b.item_id, b.category
            FROM
            mt_view_order_details a     
        	left join mt_item b
        	ON
            a.item_id=b.item_id
            WHERE
            a.merchant_id = ".FunctionsV3::q($merchant_id)."
			AND b.item_id IS NOT NULL
            AND a.status NOT IN ('initial_order')
            GROUP BY a.item_id, a.size
            ORDER BY total_qty DESC
            LIMIT 5";
            
            
            $res1 = $db->rst($popular_category_id);
			// popular items
			if($res1)
			{         
				$count = 0;

				foreach($res1 as $item){
					$count++;
					$json_cat = json_decode($item['category']);
				}

				$popular_category = "SELECT * FROM {{category}}
									WHERE
									cat_id in ($json_cat[0])";

				$res2 = $db->rst($popular_category);




				$popular_category_list = array();



					// 		print_r($item_count); exit('category');

				foreach($res2 as $category){
					if(empty($category['category_name'])){
						$category['category_name'] = '';
					}

					if(empty($category['category_description'])){
						$category['category_description'] = '';
					}

					if(empty($category['photo'])){
						$category['photo'] = '';
					}

					if(empty($category['status'])){
						$category['status'] = '';
					}

					if(empty($category['category_name_trans'])){
						$category['category_name_trans'] = '';
					}

					if(empty($category['category_name_trans'])){
						$category['category_name_trans'] = '';
					}

					if(empty($category['item_count'])){
						$category['item_count'] = '';
					}

					if(empty($category['count'])){
						$category['count'] = '';
					}

					$item_count = self::getItemCountByCategory($category['cat_id']);
					$category['item_count']= mt("[count] item",array(
							  '[count]'=>$item_count
							));


					// if($item_count >= 5){
					$popular_category_list['cat_id'] = $category['cat_id'];
					$popular_category_list['category_name'] = 'Popular Items';
					$popular_category_list['category_description'] = $category['category_description'];
					$popular_category_list['photo'] = $category['photo'];
					$popular_category_list['status'] = $category['status'];
					$popular_category_list['category_name_trans'] = $category['category_name_trans'];
					$popular_category_list['item_count'] = $count .' '. 'item';
					$popular_category_list['count'] = "$count";
					// }
				}
			}
			$stmt="
			SELECT 
			cat_id,
			category_name,
			category_description,
			photo,
			status,
			category_name_trans,
			category_description_trans
			FROM
			{{category}}
			WHERE
			merchant_id = ".FunctionsV3::q($merchant_id)."
			AND status in ('publish','published')
			$and
			ORDER BY sequence,date_created ASC			
			";					
			if($res = $db->rst($stmt)){			
				if($cart_theme==2){
					self::$enabled_trans = true;
				}
					
				if(self::$enabled_trans!=TRUE){
					return $res;
				}				
				foreach ($res as $val) {
				    
				    $merchant_category_stmt = "SELECT $day FROM {{category}} WHERE merchant_id = ".FunctionsV3::q($merchant_id)." AND cat_id = ".$val['cat_id']." AND $day = 1 LIMIT 0,1" ;
                    $mechant_res_cat = $db->rst($merchant_category_stmt);
                    if($mechant_res_cat){
                            if($mechant_res_cat[0][$day] == 1){
                                $today_formatted_data = date('Y:m:d h:i A', strtotime($today_formatted));
                                $start_date_data = date('Y:m:d h:i A', strtotime($val[$start_date]));
                                $end_date_data = date('Y:m:d h:i A', strtotime($val[$end_date]));
                                    if(strtotime($today_formatted_data)  < strtotime($start_date_data)){
                                        continue;
                                    }
                                    
                                    if(strtotime($today_formatted_data)  > strtotime($end_date_data)){
                                        continue;
                                    }
                                    
                            }
                    }
				    
					$val['category_name'] = qTranslate($val['category_name'],'category_name',array(
					  'category_name_trans'=>json_decode($val['category_name_trans'],true)
					));				
					$val['category_description'] = qTranslate($val['category_description'],'category_description',array(
					  'category_description_trans'=>json_decode($val['category_description_trans'],true)
					));					
					
					if($cart_theme==2){						
						$item_count = self::getItemCountByCategory($val['cat_id']);
						$val['item_count']= mt("[count] item",array(
						  '[count]'=>$item_count
						));
						$val['count']=$item_count;
					}
					
					
					
					$data[]=$val;
					
					if(empty($data)){
					    $data = [];
					}
				}
				if($popular_category_list){
					    array_unshift($data, $popular_category_list);
					}
				return $data;
			}
		}
		return false;
	}
	
	public static function getCategoryByID($cat_id='')
	{
		$db = new DbExt();
		
		if($cat_id>0){
			
			$stmt="SELECT 
			cat_id,
			category_name,
			category_description,
			photo,
			status,
			category_name_trans,
			category_description_trans
			 FROM
			{{category}}
			WHERE cat_id=".FunctionsV3::q($cat_id)."
			AND status in ('publish')
			LIMIT 0,1
			";
			if($res = $db->rst($stmt)){
				return $res[0];
			}			
		}
		return false;
	}
	
	public static function getItemByCategory($merchant_id='',$category_id='', $paginate=false, 
	$pagenumber=0, $pagelimit=10, $filter_dishes = array() )
	{		
		$p = new CHtmlPurifier();
		
		$paginate_total=0; 
		$limit="LIMIT $pagenumber,$pagelimit";
		
		if($merchant_id>0 && $category_id>0){
			
		if(!$paginate){
			$limit='';
		}
		
		$and = '';
		
		$default_image='';
				
		$disabled_default_image = getOptionA('mobile2_disabled_default_image');
		$merchant_menu_type = getOptionA('mobileapp2_merchant_menu_type');
		if($merchant_menu_type==3){
			$default_image='resto_banner.jpg';
			$disabled_default_image=false;
		}
		
		$food_option_not_available = getOption($merchant_id,'food_option_not_available');
		if($food_option_not_available==1){
			$and = "AND not_available <> '2' ";
		}
		
		if(!empty($filter_dishes)){			
			$and.=" AND dish like ".FunctionsV3::q('%"'.$filter_dishes.'"%')." ";
		}
			
		$db = new DbExt();
		$stmt="
		SELECT SQL_CALC_FOUND_ROWS 
		item_id,
		merchant_id,
		item_name,
		item_description,
		item_name_trans,
		item_description_trans,
		status,
		price,
		photo,
		discount,
		dish
		FROM
		{{item}}
		WHERE
		category like ".FunctionsV3::q('%"'.$category_id.'"%')."
		AND
		status IN ('publish','published')
		AND merchant_id = ".FunctionsV3::q($merchant_id)."
		$and
		ORDER BY sequence ASC
		$limit
		";		
		 
		//dump($stmt);				
		if ($res = $db->rst($stmt)){
		
			$total_records=0;
			$stmtc="SELECT FOUND_ROWS() as total_records";
			if ($resp=$db->rst($stmtc)){			 			
				$total_records=$resp[0]['total_records'];
			}		
			
			$paginate_total = ceil( $total_records / $pagelimit );
			
			$data = array();
			foreach ($res as $val) {				
				$price=''; $prices = array();
				if ( json_decode($val['price'])){
					$price = json_decode($val['price'],true);					
					foreach ($price as $size_id=>$priceval) {
						if($val['discount']>=0.001){
							$priceval = $priceval-$val['discount'];
						}					
						if(array_key_exists($size_id,(array)self::$sizes)){
						    if(strval($size_id) == 0){
    						    $set = array(
    						            'id'=>'standard',
    						            'name'=>'regular',
    						            'price'=>FunctionsV3::prettyPrice($priceval)
    						        );						        
						    }
						    else{
						    $set = array(
						            'id'=> strval($size_id),
						            'name'=>self::$sizes[$size_id],
						            'price'=>FunctionsV3::prettyPrice($priceval)
						        );						        
						    }
							$prices[]=$set;
						} else {					
						    $set = array(
						            'id'=>'standard',
						            'name'=>'regular',
						            'price'=>FunctionsV3::prettyPrice($priceval)
						        );
							$prices[]=$set;
							
						}
					}					
				} 
				
				if(self::$enabled_trans==TRUE){
					$val['item_name'] = qTranslate($val['item_name'],'item_name',array(
					  'item_name_trans'=>json_decode($val['item_name_trans'],true)
					));
					
					$val['item_description'] = qTranslate($val['item_description'],'item_description',array(
					  'item_description_trans'=>json_decode($val['item_description_trans'],true)
					));
				}
								
				$val['photo']=mobileWrapper::getImage($val['photo'],$default_image,$disabled_default_image);												
				$val['item_description']=strip_tags($val['item_description']);				
				// $val['item_description']=self::limitText($p,$val['item_description']);				
				
				$val['prices']=$prices;
				$val['cat_id']=$category_id;
				
				$icon_dish= array();
				if(!empty($val['dish'])){				
					if (method_exists("FunctionsV3","getDishIcon")){	   
				       $icon_dish = FunctionsV3::getDishIcon($val['dish']);
					} else $icon_dish='';
				} else $icon_dish='';
				
				$val['dish_image'] = $icon_dish;
				
				$data[] = $val;
			}
			
			
			return array(
			  'data'=>$data,
			  'paginate_total'=>$paginate_total
			);
		}
		unset($db);
		}
		return false;			
	}
	
	public static function getSize($merchant_id='')
	{		
		$db = new DbExt();
		$stmt="SELECT 
		size_id,
		size_name,
		size_name_trans
		FROM
		{{size}}
		WHERE
		merchant_id = ".FunctionsV3::q($merchant_id)."
		AND status IN ('publish')
		";
		if($res=$db->rst($stmt)){
			$data = array();			
		   	foreach ($res as $val) {		   
		   		if(self::$enabled_trans==TRUE){
		   			$val['size_name'] = qTranslate($val['size_name'],'size_name',array(
					  'size_name_trans'=>json_decode($val['size_name_trans'],true)
					));
		   		}
		   		$data[$val['size_id']]=$val['size_name'];
		   	}
		   	return $data;
		}
		return false;
	}
	
	public static function searchItemByName($merchant_id='',$item_name='')
	{
		$db = new DbExt();
		if($merchant_id>0 && !empty($item_name)){
		   
			$stmt="
			SELECT 
			item_id,
			merchant_id,
			item_name,
			category,
			item_name_trans,
			item_description,
			item_description_trans,
			photo
			FROM {{item}}
			WHERE merchant_id = ".FunctionsV3::q($merchant_id)."
			AND item_name like ".FunctionsV3::q( "%$item_name%" )."
			AND not_available <> '2'
			ORDER BY item_name ASC
			";								
			if($res = $db->rst($stmt)){
				return $res;
			}
		}
		return false;
	}
	
	public static function searchByCategoryByName($merchant_id='',$category_name='')
	{
		$db = new DbExt();
		if($merchant_id>0 && !empty($category_name)){		
			
			$and='';
			$todays_day = date("l");
            $todays_day = !empty($todays_day)?strtolower($todays_day):'';                        
            $enabled_category_sked = getOption($merchant_id,'enabled_category_sked'); 
            if($enabled_category_sked==1){
    		    $and .= " AND $todays_day='1' ";
    	    }    	    	    
			
			$stmt="
			SELECT 
			cat_id,
			merchant_id,
			category_name,
			category_description,
			photo,
			status,
			category_name_trans,
			category_description_trans			
			FROM {{category}}
			WHERE merchant_id = ".FunctionsV3::q($merchant_id)."				
			$and		
			AND ( category_name like ".FunctionsV3::q( "%$category_name%" )." OR 
			category_description LIKE ".FunctionsV3::q( "%$category_name%" )."  )			
			ORDER BY category_name ASC
			";					
			//dump($stmt);			
			if($res = $db->rst($stmt)){
				return $res;
			}
		}
		return false;
	}
	
	
	public static function translateCookingRef($data=array())
	{
		$new_data = array();
		if(self::$enabled_trans==TRUE){	
			if(is_array($data) && count($data)>=1){
				foreach ($data as $cook_id=>$val) {
					if($res = Yii::app()->functions->getCookingRef($cook_id)){
						$val = qTranslate($res['cooking_name'],'cooking_name',array(
						  'cooking_name_trans'=>json_decode($res['cooking_name_trans'],true)
						));
					}
					$new_data[$cook_id]=$val;
				}
				return $new_data;
			}			
		}
		return $data;
	}
	
	public static function translateIngredients($data=array())
	{
		$new_data = array();
		if(self::$enabled_trans==TRUE){
			if(is_array($data) && count($data)>=1){
				foreach ($data as $ingredients_id=>$val) {
					if($res = Yii::app()->functions->getIngredients($ingredients_id)){
						$val = qTranslate($res['ingredients_name'],'ingredients_name',array(
						  'ingredients_name_trans'=>json_decode($res['ingredients_name_trans'],true)
						));
					}
					$new_data[$ingredients_id]=$val;
				}
				return $new_data;
			}
		} 
		return $data;
	}
	
	public static function dishesList()
	{
		$data = array();
		$db = new DbExt();
		$stmt="
		SELECT 
		dish_id,dish_name
		FROM {{dishes}}
		WHERE
		status IN ('publish')
		ORDER BY dish_id ASC
		";
		if($res = $db->rst($stmt)){
			foreach ($res as $val) {
				$data[]=array(
				  'dish_id'=>$val['dish_id'],
				  'dish_name'=>$val['dish_name'],
				);
			}
		}
		return $data;
	}
	
	public static function getItemCountByCategory($category_id='')
	{
		$db = new DbExt();
		$stmt="
		SELECT COUNT(*) AS total
		FROM {{item}}
		WHERE category like ".FunctionsV3::q('%"'.$category_id.'"%')."
		";
		if($res = $db->rst($stmt)){
			return $res[0]['total'];
		}
		return 0;
	}
	
	public static function limitText($p='',$text='', $limit = 100)
	{
		if(strlen($text)>=100){
			$text = $p->purify( strip_tags($text) );
			$text = substr($text,0,100)."...";
		}
		return $text;
	}
	
	public static function limitTextItem($p='',$text='', $limit = 100)
	{
		if(strlen($text)>=100){
			$text = $p->purify( strip_tags($text) );
			$text = substr($text,0,200)."...";
		}
		return $text;
	}
		
}
/*end class*/