<?php
class ReceiptClass
{
	
	public static function formathccReceipt($html='',$data='', $item_details='',$order_info=array())
	{
			
		//$html=getOptionA('printer_receipt_tpl');
// 		$details='';
        
		if(is_array($data) && count($data)>=1){
			foreach ($data as $val) {
			    
				
				$details.=str_pad("\n"."\x1B\x61\x00\x1b\x4d\x00\x1b\x21\x10".$val['label'],25," ",STR_PAD_RIGHT).str_pad($val['value'],25," ",STR_PAD_LEFT)."\n";
				
				// $details.=str_pad($val['label'],25," ",STR_PAD_RIGHT).stripslashes($val['value'])."/n";
				// $details.=str_pad($val['label'],25," ",STR_PAD_BOTH).stripslashes($val['value'])."<BR>";
			}
		}
		
// 		echo "<pre>"; 
// 		print_r($details); 
// 		exit('when');
				
		$br="\n";
		$details.=$br;
		
		$mid=isset($item_details['total']['mid'])?$item_details['total']['mid']:'';
		
		/*ITEM STARTS HERE*/		
		if (isset($item_details['item'])){
			if (is_array($item_details['item']) && count($item_details['item'])>=1){
				foreach ($item_details['item'] as $item) {
					$notes='';
					$item_total=$item['qty']*$item['discounted_price'];
					if (!empty($item['order_notes'])){
						$notes=$item['order_notes'];
					} 
					$cookref='';
					if (!empty($item['cooking_ref'])){
						$cookref=qTranslate($item['cooking_ref'],'cooking_name',$item['cooking_name_trans']);
					}
					$size='';
					if (!empty($item['size_words'])){
						$size_words=qTranslate($item['size_words'],'size_name',$item['size_name_trans']);
						$size=$size_words;
					}		
					
					$ingredients=array();
					if (isset($item['ingredients'])){
						if (is_array($item['ingredients']) && count($item['ingredients'])>=1){							
							foreach ($item['ingredients'] as $ingredients_val) {
								if($details_ingredients=FunctionsV3::getIngredientsByName($ingredients_val,$mid)){
									$_ingredients['ingredients_name_trans']=json_decode($details_ingredients['ingredients_name_trans'],true);			          		    
									$ingredients[]="- ".qTranslate($ingredients_val,'ingredients_name',$_ingredients);
									
								} else $ingredients[]="- $ingredients_val";						
							}							
						}
					}
										
					if (!empty($item['category_id'])){
					    $details.= "\x1B\x61\x00\x1b\x4d\x00\x1b\x21\x30".qTranslate($item['category_name'],'category_name',$item['category_name_trans']).$br;
					}				
					
					$item_name=qTranslate($item['item_name'],'item_name',$item['item_name_trans']);	
					
				// 	$details.=str_pad("\x1B\x61\x00\x1b\x4d\x00\x1b\x21\x10".$val['label'],25," ",STR_PAD_RIGHT).str_pad($val['value'],25," ",STR_PAD_LEFT)."\n\n";
															
					$details.= "\x1B\x61\x00\x1b\x4d\x00\x1b\x21\x30".self::threeCol($item['qty'], $item_name,'' );
					
					if(!empty($size)){
					   $details.="\x1B\x61\x00\x1b\x4d\x00\x1b\x21\x30".self::oneCol( "($size)" );
					}
					if(!empty($notes)){
					   $details.="\x1B\x61\x00\x1b\x4d\x00\x1b\x21\x30".self::oneCol( t("Note"). ": ".$notes);
					}
					if(!empty($cookref)){
					   $details.="\x1B\x61\x00\x1b\x4d\x00\x1b\x21\x30".self::oneCol($cookref);
					}
					
					if(is_array($ingredients) && count($ingredients)>=1){
						$details.= "\x1B\x61\x00\x1b\x4d\x00\x1b\x21\x30".self::oneCol( t("Ingredients") );
					    foreach ($ingredients as $val_ingredients) {
					   	  $details.="\x1B\x61\x00\x1b\x4d\x00\x1b\x21\x30".self::oneCol($val_ingredients);
					    }
					}
										
					/*ADDON STARTS HERE*/
					if (isset($item['new_sub_item'])){
						if (is_array($item['new_sub_item']) && count($item['new_sub_item'])>=1){
							foreach ($item['new_sub_item'] as $subcategory_name=> $subcategory_item) {
								if(!isset($subcategory_item[0])){
								$subcategory_item[0]['subcategory_name_trans']='';
								}								
								$subcategory_name_trans=qTranslate($subcategory_name,'subcategory_name',
								$subcategory_item[0]['subcategory_name_trans']);
								
								$details.="\x1B\x61\x00\x1b\x4d\x00\x1b\x21\x30"."$subcategory_name_trans".$br;
								
								foreach ($subcategory_item as $itemsub) {
									$addon_name=qTranslate($itemsub['addon_name'],'sub_item_name',
								    $itemsub['sub_item_name_trans']);
								    
								    $subitem_total=$itemsub['addon_qty']*$itemsub['addon_price'];
								    
								    $details.="\x1B\x61\x00\x1b\x4d\x00\x1b\x21\x30".self::threeCol($itemsub['addon_qty'],
								    PrinterClass::prettyPrice($itemsub[''])." ".$addon_name
								    //PrinterClass::prettyPrice($subitem_total)
								    );
								    
								}
								
							}
						}
					}
					/*ADDON ENDS HERE*/
													
					$details.= str_pad("\x1B\x21\x00",'30',"-",STR_PAD_RIGHT)."\n";
					
				}/* END FOREACH*/
			}
		}
		
		$details.=$br;		
		
		/*TOTAL*/		
		if (isset($item_details['total'])){
			if(!isset($item_details['total']['less_voucher'])){
			$item_details['total']['less_voucher']='';
			}
			if(!isset($item_details['total']['pts_redeem_amt'])){
				$item_details['total']['pts_redeem_amt']='';
			}
			if(!isset($item_details['total']['tips'])){
				$item_details['total']['tips']='';
			}
			
			if ($item_details['total']['less_voucher']>0.001){				
				$details.="\x1D\x21\x01".self::twoCol( t("Less Voucher")." ".$item_details['total']['voucher_type'], 
				"(".PrinterClass::prettyPrice($item_details['total']['less_voucher']).")"
				 );
			}
			
			if ($item_details['total']['pts_redeem_amt']>0.001){			    
			    $details.="\x1D\x21\x01".self::twoCol( t("Points discount") , "(".PrinterClass::prettyPrice($item_details['total']['pts_redeem_amt']).")" );
			}
			
			if(!isset($item_details['total']['discounted_amount'])){
			   $item_details['total']['discounted_amount']=0;
		    }
		    
		    if($item_details['total']['calculation_method']==1){
		    	if($item_details['total']['discounted_amount']>0.001){		    		
		    		$details.="\x1D\x21\x01".self::twoCol(  t("Discount")." ".$item_details['total']['merchant_discount_amount']."%" , PrinterClass::prettyPrice($item_details['total']['discounted_amount'])  );
		    	}
		    }
		    
		    $details.= "\x1D\x21\x01".self::twoCol(  t("Sub Total"), PrinterClass::prettyPrice($item_details['total']['subtotal']) );
		    
		    if (!empty($item_details['total']['delivery_charges'])){
		    	$details.="\x1D\x21\x01".self::twoCol( t("Delivery Fee"), PrinterClass::prettyPrice($item_details['total']['delivery_charges']) );
		    }
		    
		    if (!empty($item_details['total']['merchant_packaging_charge'])):
			if ($item_details['total']['merchant_packaging_charge']>0.0001){				
				$details.= "\x1D\x21\x01".self::twoCol( t("Packaging"),  PrinterClass::prettyPrice($item_details['total']['merchant_packaging_charge']));
			}
			endif;
			
			if(isset($item_details['total']['tax_amt'])){				
				$details.= "\x1D\x21\x01".self::twoCol( t("Tax")." ".$item_details['total']['tax_amt']."%",
				 PrinterClass::prettyPrice($item_details['total']['taxable_total']) );
			}
			
			if (!isset($item_details['total']['card_fee'])){
			    $item_details['total']['card_fee']='';
		    }
		    
		    if ($item_details['total']['card_fee']>0.001):			
			   $details.= "\x1D\x21\x01".self::twoCol( t("Card Fee"), PrinterClass::prettyPrice($item_details['total']['card_fee']) );
			endif;
			
			if ($item_details['total']['tips']>0.001){				
				$details.= "\x1D\x21\x01".self::twoCol( t("Tips")." ".$item_details['total']['tips_percent'],
				 PrinterClass::prettyPrice($item_details['total']['tips']) );
			}
						
		    $details.= "\x1D\x21\x01".self::twoCol( t("Total"),  PrinterClass::prettyPrice($item_details['total']['total']));
			
		}/* END TOTAL*/
		
		
		$line='-';
		$details=FunctionsV3::smarty("line", str_pad($line,'20',"-",STR_PAD_RIGHT),$details);
		$details.= "\x1d\x56\x42\x00";
		$html=FunctionsV3::smarty("line", str_pad($line,'20',"-",STR_PAD_RIGHT),$html);
		
		if(isset($order_info['date_created'])){
			$html=FunctionsV3::smarty("transaction_date", 
			FunctionsV3::prettyDate($order_info['date_created'])." ".FunctionsV3::prettyTime($order_info['date_created'])
			 ,$html);
		}
		if(isset($order_info['trans_type'])){
			$html=FunctionsV3::smarty("transaction_type", strtoupper(t($order_info['trans_type']))  ,$html);
		}
		
		$html=FunctionsV3::smarty('order_details',$details,$html);
		
		$html=str_replace("&amp;","&",$html);
		
		/*CHANGE TAGS*/
// 		echo "this";
// 		echo $html;
// 		echo FunctionsV3::smarty("sitename",getOptionA('website_title'),$html, 'hcc');
// 		exit('man'); 
		 $html=FunctionsV3::smarty("sitename",getOptionA('website_title'),$html, 'hcc');
// 		exit('out'); 
		$html=FunctionsV3::smarty("site_address",getOptionA('website_address'),$html);
		$html=FunctionsV3::smarty("contact_number",getOptionA('website_contact_phone'),$html);
		$html=FunctionsV3::smarty("siteurl", websiteUrl(),$html);
		$html=FunctionsV3::smarty("order_id",  isset($order_info['order_id'])?$order_info['order_id']:'' ,$html);
		
						
		if(!empty($html)){
		  return $html;
		}
		return false;
	}
	
	public static function formatReceipt($html='',$data='', $item_details='',$order_info=array())
	{
			
		//$html=getOptionA('printer_receipt_tpl');
		$details='';
		if(is_array($data) && count($data)>=1){
			foreach ($data as $val) {
				$details.=str_pad($val['label'],25,"\x1B\x21\x00",STR_PAD_RIGHT).stripslashes($val['value'])."<BR>";
			}
		}
				
		$br='<BR>';		
		
		$details.=$br;
		
		$mid=isset($item_details['total']['mid'])?$item_details['total']['mid']:'';
		
		/*ITEM STARTS HERE*/		
		if (isset($item_details['item'])){
			if (is_array($item_details['item']) && count($item_details['item'])>=1){
				foreach ($item_details['item'] as $item) {
					$notes='';
					$item_total=$item['qty']*$item['discounted_price'];
					if (!empty($item['order_notes'])){
						$notes=$item['order_notes'];
					} 
					$cookref='';
					if (!empty($item['cooking_ref'])){
						$cookref=qTranslate($item['cooking_ref'],'cooking_name',$item['cooking_name_trans']);
					}
					$size='';
					if (!empty($item['size_words'])){
						$size_words=qTranslate($item['size_words'],'size_name',$item['size_name_trans']);
						$size=$size_words;
					}		
					
					$ingredients=array();
					if (isset($item['ingredients'])){
						if (is_array($item['ingredients']) && count($item['ingredients'])>=1){							
							foreach ($item['ingredients'] as $ingredients_val) {
								if($details_ingredients=FunctionsV3::getIngredientsByName($ingredients_val,$mid)){
									$_ingredients['ingredients_name_trans']=json_decode($details_ingredients['ingredients_name_trans'],true);			          		    
									$ingredients[]="- ".qTranslate($ingredients_val,'ingredients_name',$_ingredients);
									
								} else $ingredients[]="- $ingredients_val";						
							}							
						}
					}
										
					if (!empty($item['category_id'])){
						$details.= qTranslate($item['category_name'],'category_name',$item['category_name_trans']).$br;
					}				
					
					$item_name=qTranslate($item['item_name'],'item_name',$item['item_name_trans']);	
															
					$details.= self::threeCol($item['qty'], $item_name,PrinterClass::prettyPrice($item_total) );
					
					if(!empty($size)){
					   $details.=self::oneCol( "($size)" );
					}
					if(!empty($notes)){
					   $details.=self::oneCol( t("Note"). ": ".$notes);
					}
					if(!empty($cookref)){
					   $details.=self::oneCol($cookref);
					}
					
					if(is_array($ingredients) && count($ingredients)>=1){
						$details.= self::oneCol( t("Ingredients") );
					    foreach ($ingredients as $val_ingredients) {
					   	  $details.=self::oneCol($val_ingredients);
					    }
					}
										
					/*ADDON STARTS HERE*/
					if (isset($item['new_sub_item'])){
						if (is_array($item['new_sub_item']) && count($item['new_sub_item'])>=1){
							foreach ($item['new_sub_item'] as $subcategory_name=> $subcategory_item) {
								if(!isset($subcategory_item[0])){
								$subcategory_item[0]['subcategory_name_trans']='';
								}								
								$subcategory_name_trans=qTranslate($subcategory_name,'subcategory_name',
								$subcategory_item[0]['subcategory_name_trans']);
								
								$details.="$subcategory_name_trans".$br;
								
								foreach ($subcategory_item as $itemsub) {
									$addon_name=qTranslate($itemsub['addon_name'],'sub_item_name',
								    $itemsub['sub_item_name_trans']);
								    
								    $subitem_total=$itemsub['addon_qty']*$itemsub['addon_price'];
								    
								    $details.=self::threeCol($itemsub['addon_qty'],
								    PrinterClass::prettyPrice($itemsub['addon_price'])." ".$addon_name,
								    PrinterClass::prettyPrice($subitem_total)
								    );
								    
								}
								
							}
						}
					}
					/*ADDON ENDS HERE*/
													
					$details.= str_pad("\x1B\x21\x00",'24',"-",STR_PAD_RIGHT)."<BR>";
					
				}/* END FOREACH*/
			}
		}
		
		$details.=$br;		
		
		/*TOTAL*/		
		if (isset($item_details['total'])){
			if(!isset($item_details['total']['less_voucher'])){
			$item_details['total']['less_voucher']='';
			}
			if(!isset($item_details['total']['pts_redeem_amt'])){
				$item_details['total']['pts_redeem_amt']='';
			}
			if(!isset($item_details['total']['tips'])){
				$item_details['total']['tips']='';
			}
			
			if ($item_details['total']['less_voucher']>0.001){				
				$details.=self::twoCol( t("Less Voucher")." ".$item_details['total']['voucher_type'], 
				"(".PrinterClass::prettyPrice($item_details['total']['less_voucher']).")"
				 );
			}
			
			if ($item_details['total']['pts_redeem_amt']>0.001){			    
			    $details.=self::twoCol( t("Points discount") , "(".PrinterClass::prettyPrice($item_details['total']['pts_redeem_amt']).")" );
			}
			
			if(!isset($item_details['total']['discounted_amount'])){
			   $item_details['total']['discounted_amount']=0;
		    }
		    
		    if($item_details['total']['calculation_method']==1){
		    	if($item_details['total']['discounted_amount']>0.001){		    		
		    		$details.=self::twoCol(  t("Discount")." ".$item_details['total']['merchant_discount_amount']."%" , PrinterClass::prettyPrice($item_details['total']['discounted_amount'])  );
		    	}
		    }
		    
		    $details.= self::twoCol(  t("Sub Total"), PrinterClass::prettyPrice($item_details['total']['subtotal']) );
		    
		    if (!empty($item_details['total']['delivery_charges'])){
		    	$details.= self::twoCol( t("Delivery Fee"), PrinterClass::prettyPrice($item_details['total']['delivery_charges']) );
		    }
		    
		    if (!empty($item_details['total']['merchant_packaging_charge'])):
			if ($item_details['total']['merchant_packaging_charge']>0.0001){				
				$details.= self::twoCol( t("Packaging"),  PrinterClass::prettyPrice($item_details['total']['merchant_packaging_charge']));
			}
			endif;
			
			if(isset($item_details['total']['tax_amt'])){				
				$details.= self::twoCol( t("Tax")." ".$item_details['total']['tax_amt']."%",
				 PrinterClass::prettyPrice($item_details['total']['taxable_total']) );
			}
			
			if (!isset($item_details['total']['card_fee'])){
			    $item_details['total']['card_fee']='';
		    }
		    
		    if ($item_details['total']['card_fee']>0.001):			
			   $details.= self::twoCol( t("Card Fee"), PrinterClass::prettyPrice($item_details['total']['card_fee']) );
			endif;
			
			if ($item_details['total']['tips']>0.001){				
				$details.= self::twoCol( t("Tips")." ".$item_details['total']['tips_percent'],
				 PrinterClass::prettyPrice($item_details['total']['tips']) );
			}
						
		    $details.= self::twoCol( t("Total"),  PrinterClass::prettyPrice($item_details['total']['total']));
			
		}/* END TOTAL*/
		
		
		$line='-';
		$details=FunctionsV3::smarty("line", str_pad($line,'24',"-",STR_PAD_RIGHT),$details);
		$html=FunctionsV3::smarty("line", str_pad($line,'24',"-",STR_PAD_RIGHT),$html);
		
		if(isset($order_info['date_created'])){
			$html=FunctionsV3::smarty("transaction_date", 
			FunctionsV3::prettyDate($order_info['date_created'])." ".FunctionsV3::prettyTime($order_info['date_created'])
			 ,$html);
		}
		if(isset($order_info['trans_type'])){
			$html=FunctionsV3::smarty("transaction_type", strtoupper(t($order_info['trans_type']))  ,$html);
		}
		
		$html=FunctionsV3::smarty('order_details',$details,$html);
		
		$html=str_replace("&amp;","&",$html);
		
		/*CHANGE TAGS*/
		$html=FunctionsV3::smarty("sitename",getOptionA('website_title'),$html);
		$html=FunctionsV3::smarty("site_address",getOptionA('website_address'),$html);
		$html=FunctionsV3::smarty("contact_number",getOptionA('website_contact_phone'),$html);
		$html=FunctionsV3::smarty("siteurl", websiteUrl(),$html);
		$html=FunctionsV3::smarty("order_id",  isset($order_info['order_id'])?$order_info['order_id']:'' ,$html);
		
						
		if(!empty($html)){
		  return $html;
		}
		return false;
	}
	
	private static function threeCol($col_1='', $col_2='', $col_3='')
	{
		$tab1=5;
		$tab2=20;
		$pad_string=" ";
		$pad_right=STR_PAD_RIGHT;
		$br="\n";
		
		//$t = str_pad($col_1,5,$pad_string,$pad_right). str_pad($col_2,$tab2,$pad_string,$pad_string).$br;
		$t = str_pad($col_1,5,$pad_string,$pad_right)  . str_pad($col_2,$tab2,$pad_string,$pad_right). $col_3 .$br;
		return $t;
	}
	
	private static function twoCol($label='', $value='')
	{
		$t = str_pad($label,25," ",STR_PAD_RIGHT).$value."\n";
		return $t;
	}
	
	private static function oneCol($label='')
	{
		$t=str_pad("",5," ")."$label\n";
		return $t;
	}
	
	
	public static function template_1()
	{
		return '<CB>FOOD REPUBLIC PTE LT</CB><C>Sumber Ayam<BR>51 Bras Basah Road<BR>Manulife Centre<BR>Singapore 189554</C>[line]<BR><CB>[transaction_type]</CB><BR><C>[transaction_date]</C>[line]<BR>[order_details]<BR><C>Thank you for your order</C><BR>';
	}
	
	public static function template_2()
	{
		return '<CB>[sitename]</CB><C>[site_address]<BR>[contact_number]</C>[line]<BR><CB>[transaction_type]</CB><BR><C>[transaction_date]</C>[line]<BR>[order_details]<BR><C>Thank you for your order<BR><BR>[siteurl]</C>';
	}
	
} /*end class */