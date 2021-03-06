<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';
class Api extends REST_Controller {
	function __construct()
	{
		parent::__construct();	
        $this->methods['user_get']['limit'] = 500; // 500 requests per hour per user/key
        $this->methods['user_post']['limit'] = 100; // 100 requests per hour per user/key
        $this->methods['user_delete']['limit'] = 50; // 50 requests per hour per user/key
        $this->methods['apartment_get']['limit'] = 10;
        $this->methods['itemtype_get']['limit'] = 10;
      }
      /****************************************************/
      /********* get apartments list **********************/
      /****************************************************/
      public function apartments_get(){
      	$this->_em = $this->doctrine->em;
      	$qb = $this->_em->createQueryBuilder();
      	$apartments = $qb->select('a')->from('Entity\Apartment','a')->getQuery()->getArrayResult();
      	$this->response($apartments, REST_Controller::HTTP_OK); 
      }
      /****************************************************/
      /************* get blocks list **********************/
      /****************************************************/
      public function blocks_post(){
      	$input = file_get_contents("php://input");
      	$data = json_decode($input);
      	$this->_em = $this->doctrine->em;
      	$qb = $this->_em->createQueryBuilder();
      	$apartmentId = 0;
      	if(property_exists($data,'apartmentId')){
      		$apartmentId 		= $data->apartmentId;
      	}
      	if($apartmentId){
      		$blocks = $qb->select('b')->from('Entity\Block','b')->innerJoin('b.apt_id','Entity\Apartment')->where('b.apt_id=:aptId')->setParameter('aptId',$apartmentId)->getQuery()->getArrayResult();
      		$this->response($blocks, REST_Controller::HTTP_OK); 
      	}else{
      		$this->response(NULL, REST_Controller::HTTP_BAD_REQUEST); 
      	}
      }
      /****************************************************/
      /************* get blocks list **********************/
      /****************************************************/
      public function blocks_put(){
		//$input = file_get_contents("php://input");
		//$data = json_decode($input);
      	$input = $this->put('apartmentId');
      	$this->_em = $this->doctrine->em;
      	$qb = $this->_em->createQueryBuilder();
		if($input){ //property_exists($data,'apartmentId')
			$apartmentId  = $input; //		= $data->apartmentId;	
			if($apartmentId){
				$blocks = $qb->select('b')->from('Entity\Block','b')->innerJoin('b.apt_id','Entity\Apartment')->where('b.apt_id=:aptId')->setParameter('aptId',$apartmentId)->getQuery()->getArrayResult();
				$this->response($blocks, REST_Controller::HTTP_OK); 
			}else{
				$blocks = $qb->select('b')->from('Entity\Block','b')->getQuery()->getArrayResult();
				$this->response($blocks, REST_Controller::HTTP_OK); 
			}
		}else{
			$this->response(NULL, REST_Controller::HTTP_BAD_REQUEST); 
		}
	}
	/****************************************************/
	/**************** get flats list ********************/
	/****************************************************/
	public function flats_post(){
		$input = file_get_contents("php://input");
		$data = json_decode($input);
		$blockId =0;
		if(property_exists($data,'blockId')){
			$blockId 		= $data->blockId;
		}
		$this->_em = $this->doctrine->em;
		$qb = $this->_em->createQueryBuilder();
		if($blockId){
			$flats = $qb->select('f.id as flat_id','f.name as name')->from('Entity\Flat','f')->innerJoin('f.block_id','Entity\Block')->where('f.block_id=:blockId')->setParameter('blockId',$blockId)->getQuery()->getArrayResult();
			$this->response($flats, REST_Controller::HTTP_OK); 
		}else{
			$this->response(NULL, REST_Controller::HTTP_BAD_REQUEST); 
		}
	}
	/****************************************************/
	/************* get owner flats list *****************/
	/****************************************************/
	public function ownerflats_post(){
		$input = file_get_contents("php://input");
		$data = json_decode($input);
		$customerId =0;
		if(property_exists($data,'customerId')){
			$customerId 		= $data->customerId;
		}
		$this->_em = $this->doctrine->em;
		$qb = $this->_em->createQueryBuilder();
		if($customerId){
			$ownerObj = $qb->select('c')->from('Entity\Customer','c')->innerJoin('c.flat_id','Entity\Flat')->where('c.owner_id =:ownerId')->setParameter('ownerId',$customerId)->getQuery()->getResult();
			$flats = array();
			foreach($ownerObj as $ow){
				$flat = array();
				$flat['flat_id'] = $ow->getFlatId()->getId();
				$flat['flat'] = $ow->getFlatId()->getName();
				$flat['block'] = $ow->getBlockId()->getName();
				$flat['apartment'] = $ow->getApartmentId()->getName();
				$flats['flats'] = $flat;
			}
			$this->response($flats, REST_Controller::HTTP_OK); 
		}else{
			$this->response(NULL, REST_Controller::HTTP_BAD_REQUEST); 
		}
	}
	/****************************************************/
	/************* get flat profile  ********************/
	/****************************************************/
	public function flat_post(){
		$input = file_get_contents("php://input");
		$data = json_decode($input);
		$flatId =0;
		if(property_exists($data,'flatId'))
			$flatId 		= $data->flatId;
		$this->_em = $this->doctrine->em;
		$qb = $this->_em->createQueryBuilder();
		if($flatId){
			//$flats = $qb->select('f')->from('Entity\Flat','f')->innerJoin('f.block_id','Entity\Block')->where('f.id=:flatId')->setParameter('flatId',$flatId)->getQuery()->getArrayResult();
			$flats = $qb->select('f','Entity\Block','Entity\Apartment','Entity\Customer')->from('Entity\Flat','f')->innerJoin('f.block_id','Entity\Block')->innerJoin('Entity\Block.apt_id','Entity\Apartment')->leftJoin('f.customer','Entity\Customer')->where('f.id=:flatId')->setParameter('flatId',$flatId)->getQuery()->getArrayResult();
			if(sizeof($flats)){
				$flatObj = $flats[0];
				//print_r($flatObj); die();
				$flat['flat_id'] = $flatObj['id'];
				$flat['flat'] = $flatObj['name'];
				$flat['intercom'] = $flatObj['intercom'];
				$flat['eusn'] = $flatObj['eusn'];
				$flat['bhk'] = $flatObj['bhk'];
				$flat['size'] = $flatObj['size'];
				$flat['facing'] = $flatObj['facing'];
				$flat['readyToSale'] = $flatObj['readyToSale'];
				$flat['readyToOccupy'] = $flatObj['readyToOccupy'];
				$flat['salePrice'] = $flatObj['salePrice'];
				$flat['rentPrice'] = $flatObj['rentPrice'];
				$flat['nofpplStay'] = $flatObj['nofpplStay'];
				$flat['cntOneName'] = $flatObj['cntOneName'];
				$flat['cntOneMobile'] = $flatObj['cntOneMobile'];
				$flat['cntTwoName'] = $flatObj['cntTwoName'];
				$flat['cntTwoMobile'] = $flatObj['cntTwoMobile'];
				$flat['block'] = $flatObj['block_id']['name'];
				$flat['apartment'] = $flatObj['block_id']['apt_id']['name'];
				$familyHead='';
				foreach($flatObj['customer'] as $c){
					if($c['subType']=='tenant'){
						$familyHead = $c['firstname'].' '.$c['lastname'];
					}elseif($c['subType']=='owner'){
						$familyHead = $c['firstname'].' '.$c['lastname'];
					}else{
						//$familyHead = $c['firstname'].' '.$c['lastname'];
					}
				}
				$flat['familyHead'] = $familyHead;
				$this->response($flat, REST_Controller::HTTP_OK); 
			}else{
				$this->response(NULL, REST_Controller::HTTP_BAD_REQUEST);
			}	
		}else{
			$this->response(NULL, REST_Controller::HTTP_BAD_REQUEST); 
		}
	}
	/****************************************************/
	/************* update flat profle *******************/
	/****************************************************/
	public function flatupdate_post(){
		$input = file_get_contents("php://input");
		$data = json_decode($input);
		$flatId =0;
		$sale ='';
		$rent ='';
		if(property_exists($data,'flatId'))
			$flatId 		= $data->flatId;
		if(property_exists($data,'sale'))
			$sale 		= $data->sale;
		if(property_exists($data,'rent'))
			$rent 		= $data->rent;
		$this->_em = $this->doctrine->em;
		$qb = $this->_em->createQueryBuilder();
		if($flatId){
			$flatObj = $this->_em->getRepository('Entity\Flat')->findOneById($flatId);
			if(is_object($flatObj)){
				$flatObj->setSale($sale);
				$flatObj->setRoccupy($rent);
				$this->_em->persist($flatObj);
				$this->_em->flush();
			}else{
				$message =[ 'message' => 'sorry your flat doesn\'t exits our database'];
				$this->response($message, REST_Controller::HTTP_NOT_ACCEPTABLE); 
			}
			$message =[ 'message' => 'Your Flat updated sucessfully'];
			$this->response($message, REST_Controller::HTTP_OK); 
		}else{
			$message =[ 'message' => 'Your un-authorized user.'];
			$this->response(NULL, REST_Controller::HTTP_BAD_REQUEST); 
		}
	}
	/****************************************************/
	/************* get itme types  **********************/
	/****************************************************/
	public function itemtypes_get(){
		$this->_em = $this->doctrine->em;
		$qb = $this->_em->createQueryBuilder();
		$itemtype = $qb->select('a')->from('Entity\ItemType','a')->getQuery()->getArrayResult();
		$this->response($itemtype, REST_Controller::HTTP_OK); 
	}	
	/****************************************************/
	/************* get catelog items  *******************/
	/****************************************************/	
	public function catalogitems_post(){
		$input = file_get_contents("php://input");
		$data = json_decode($input);
		$custId 		= $data->customerId; 
		$this->_em = $this->doctrine->em;
		$qb = $this->_em->createQueryBuilder();
		if($custId){
			$cust = $this->_em->find('Entity\Customer',$custId);
			if(is_object($cust)){
			$this->load->library('cbs',$this->_em);
		 	$catalogId = $this->cbs->getCatalogId($cust); // customer obj
			$items = $qb->select('cp','Entity\Item','Entity\ItemType','Entity\Service')->from('Entity\CatalogPrice','cp')->innerJoin('cp.item_id','Entity\Item')->innerJoin('cp.itype_id','Entity\ItemType')->innerJoin('cp.service_id','Entity\Service')->where('cp.catalog_id = :catalogId and Entity\Item.status=:status')->setParameter('catalogId',$catalogId)->setParameter('status',1)->addOrderBy('Entity\Item.id','asc')->getQuery()->getArrayResult();
					$catalogItems = array();
					foreach($items as $it){
						if(array_key_exists('item_id',$it)){
							if($it['item_id']['status']){
								$catalogItem = array();
								$catalogItem['item_id'] 		= $it['item_id']['id'];
								$catalogItem['item_name'] 		= $it['item_id']['name'];
								$catalogItem['item_image'] 		= $it['item_id']['image'];
								$catalogItem['item_cost'] 		= $it['cost'];
								$catalogItem['item_discount'] 	= $it['discount'];
								$catalogItem['item_rpoints'] 	= $it['rpoints'];
								$catalogItem['item_status'] 	= $it['status'];
								$catalogItem['item_itype_id'] 	= $it['itype_id']['id'];
								$serviceId = $catalogItem['item_service_id'] = $it['service_id']['id'];
								if($serviceId){
									$serviceObj = $this->_em->find('Entity\Service',$serviceId); 
									if(is_object($serviceObj)){
										$addons = $serviceObj->getAddons();
									}
								}
								if(is_array($addons)){
									$catalogItem['item_addons'] = $addons;
								}
								$catalogItems[] = $catalogItem;
							}
						}
					}
					$this->response($catalogItems, REST_Controller::HTTP_OK); 
				}else{
					$message =[ 'message' => 'Your un-authorized user.'];
					$this->response($message, REST_Controller::HTTP_UNAUTHORIZED); 
				}
			}else{
				$message =[ 'message' => 'Your un-authorized user.'];
				$this->response($message, REST_Controller::HTTP_UNAUTHORIZED); 
			}
		}
		/***************************************************/
		/***********  SERVICE ITEMS POST *******************/
		/***************************************************/
		public function serviceitems_post(){
			$input = file_get_contents("php://input");
			$data = json_decode($input);
			if(!is_object($data))
				$data = new stdClass();
			if(property_exists($data,'customerId'))	
				$custId 		= $data->customerId; 
			if(property_exists($data,'serviceId'))	
				$serviceId 		= $data->serviceId;
			$this->_em = $this->doctrine->em;
			$qb = $this->_em->createQueryBuilder();
			if(isset($custId)){
				$cust = $this->_em->find('Entity\Customer',$custId);
				if(is_object($cust)){
					if(is_object($cust->getApartmentId())){
						$aptId = $cust->getApartmentId()->getId();
						if($aptId)
							$apart = $this->_em->find('Entity\Apartment',$aptId);
						if(is_object($catalogId =$apart->getCatalogId()))
							$catalogId = $apart->getCatalogId()->getId(); 
						else{
							$cat = $this->_em->getRepository('Entity\Catalog')->findOneByName('default');
							$catalogId = $cat->getId();
						};
					}else{
						$cat = $this->_em->getRepository('Entity\Catalog')->findOneByName('default');
						$catalogId = $cat->getId();
					}
					$items = $qb->select('cp','Entity\Item','Entity\ItemType','Entity\Service')->from('Entity\CatalogPrice','cp')->innerJoin('cp.item_id','Entity\Item')->innerJoin('cp.itype_id','Entity\ItemType')->innerJoin('cp.service_id','Entity\Service')->where('cp.catalog_id = :catalogId and cp.service_id =:serviceId and Entity\Item.status=:status')->setParameter('catalogId',$catalogId)->setParameter('serviceId',$serviceId)->setParameter('status',1)->getQuery()->getArrayResult();
					$catalogItems = array();
					foreach($items as $it){
		//	print_r($it);
						if(array_key_exists('item_id',$it)){
							if($it['item_id']['status']){
								$catalogItem = array();
								$catalogItem['item_id'] 		= $it['item_id']['id'];
								$catalogItem['item_name'] 		= $it['item_id']['name'];
								$catalogItem['item_image'] 		= $it['item_id']['image'];
								$catalogItem['item_cost'] 		= $it['cost'];
								$catalogItem['item_discount'] 	= $it['discount'];
								$catalogItem['item_rpoints'] 	= $it['rpoints'];
								$catalogItem['item_itype_id'] 	= $it['itype_id']['id'];
								$serviceId = $catalogItem['item_service_id'] = $it['service_id']['id'];
								if($serviceId){
									$serviceObj = $this->_em->find('Entity\Service',$serviceId); 
									if(is_object($serviceObj)){
										$addons = $serviceObj->getAddons();
									}
								}
								if(is_array($addons)){
									$catalogItem['item_addons'] = $addons;
								}
								$catalogItems[] = $catalogItem;
							}
						}
					}
					$this->response($catalogItems, REST_Controller::HTTP_OK); 
				}else{
					$message =[ 'message' => 'Your un-authorized user.'];
					$this->response($message, REST_Controller::HTTP_UNAUTHORIZED); 
				}
			}else{
				$message =[ 'message' => 'Your un-authorized user.'];
				$this->response($message, REST_Controller::HTTP_UNAUTHORIZED); 
			}
		}
		public function catalog_put(){
			$input = file_get_contents("php://input");
			$data = json_decode($input);
		$custId 		= 8; //$data->custId;
		$aptId 			= 1; //$data->aptId;
		//$itemId 		= $data->itemId;
		$serviceId 		= $data->serviceId;
		$itemTypeId 	= $data->itemTypeId;
		$this->_em = $this->doctrine->em;
		$qb = $this->_em->createQueryBuilder();
		$aprt = $this->_em->getRepository('Entity\Apartment')->findOneBy(array('id'=>$aptId));
		$catalogId = $aprt->getCatalogId()->getId();
		//		$cps = $qb->select('cp')->from('Entity\CatalogPrice','cp')->where('cp.catalog_id =:catalogId and cp.item_id =:itemId and cp.service_id =:serviceId and cp.itype_id=:itypeId')->setParameters(array('catalogId'=>$catalogId,'itemId'=>$itemId,'serviceId'=>$serviceId,'itypeId'=>$itemTypeId))->getQuery()->getResult();
		$cps = $qb->select('cp')->from('Entity\CatalogPrice','cp')->where('cp.catalog_id =:catalogId and cp.service_id =:serviceId and cp.itype_id=:itypeId')->setParameters(array('catalogId'=>$catalogId,'serviceId'=>$serviceId,'itypeId'=>$itemTypeId))->getQuery()->getResult();
		$data = ['cost'=>$cps[0]->getCost(),'discount'=>$cps[0]->getDiscount()];
		$this->response($data, REST_Controller::HTTP_OK); 
	}
	/***************************************************/
	/***********  PLACE ORDER IDS **********************/
	/***************************************************/
	public function placeorderids_post(){
		$input = file_get_contents("php://input");
		$data = json_decode($input);
		if(!is_object($data))
			$data = new stdClass();
		if(property_exists($data,'customerId')){
			$customerId = $data->customerId;
			if($customerId){
				$this->_em = $this->doctrine->em;
				$qb = $this->_em->createQueryBuilder();
				$placeOrderIds = $qb->select('poi')->from('Entity\PlaceOrderId','poi')->where('poi.customer_id =:customerId and poi.isDelete=0')->setParameter('customerId',$customerId)->orderBy('poi.orderDate','desc')->getQuery()->getArrayResult();
				$this->response($placeOrderIds, REST_Controller::HTTP_OK); 		
			}else{
				log_message('error',' must use customerId');	
				$message = ['message'=>'your not authorized '];
				$this->response($message, REST_Controller::HTTP_NOT_ACCEPTABLE); 			
			}
		}else{
			log_message('error','your customerId is your payload');
			$message = ['message'=>'your not authorized '];
			$this->response($message, REST_Controller::HTTP_NOT_ACCEPTABLE); 			
		}
	}
	/***************************************************/
	/***********  PLACE ORDER HISTORY  *****************/
	/***************************************************/
	public function placeorderhistory_post(){
		$input = file_get_contents("php://input");
		$data = json_decode($input);
		if(!is_object($data))
			$data = new stdClass();
		if(property_exists($data,'orderId'))	
			$orderId = $data->orderId;
		if(isset($orderId)){
			$this->_em = $this->doctrine->em;
			$qb = $this->_em->createQueryBuilder();
			$qb2 = $this->_em->createQueryBuilder();
			$placeOrders = $qb->select('po','Entity\Item','Entity\ItemType','Entity\Service','Entity\PlaceOrderAddon','Entity\Addon')->from('Entity\PlaceOrder','po')->leftJoin('po.item_id','Entity\Item')->leftJoin('Entity\Item.itype_id','Entity\ItemType')->leftJoin('po.service_id','Entity\Service')->leftJoin('po.placeOrderAddons','Entity\PlaceOrderAddon')->leftJoin('Entity\PlaceOrderAddon.addon_id','Entity\Addon')->where('po.order_id =:orderId')->setParameter('orderId',$orderId)->getQuery()->getArrayResult();
			$orderHistory = $qb2->select('o')->from('Entity\PlaceOrderId','o')->where('o.order_id =:orderId')->setParameter('orderId',$orderId)->getQuery()->getArrayResult();
			$poa = array();
			foreach($placeOrders as $k => $v){
				$po =  array();
				$po['item_name'] 	= $v['item_id']['name'];
				$po['item_type'] 	= $v['item_id']['itype_id']['name'];;
				$po['item_service'] = $v['service_id']['name'];;
				$po['item_count'] 	= $v['icount'];
				$netCost = $itemCost = $v['cost'];
				$this->load->database();
				$pq = 'select count(prco_id) as icount, itemStatus from process_orders where item_id='.$v['item_id']['id'].' and service_id='.$v['service_id']['id'].' and order_id = "'.$orderId.'" and itemStatus= "returned"';
				$query = $this->db->query($pq);
				$procObj = $query->result();
				foreach ($procObj as $key => $val) {
					if($val->icount == 0){
						$po['status']= '';
					}else{
						$po['status']= $val->itemStatus.'('.$val->icount.')';
					}
				}
				$ads = array();
				foreach($v['placeOrderAddons'] as $ak => $av){
					$ad = array();
					$ad['addon_name'] = $av['addon_id']['name'];
					$ad['addon_cost'] = $av['addon_id']['price'];
					$ad['addon_count'] = $av['poa_count'];
					$ads[]  = $ad;
					$netCost = $netCost - $av['poa_count']*$av['addon_id']['price'];
				}
				$netCost = $netCost/$v['icount'];
				$po['item_cost'] 	= $netCost;
				$po['item_addons'] = $ads;
				$po['item_rpoints'] = $v['rpoints'];	
				$poa['items'][] = $po;
			}
			if(sizeof($orderHistory)){
				$poa['subTotal'] = number_format($orderHistory[0]['subtotal'],2,'.','');
				$poa['serviceTax'] = number_format($orderHistory[0]['serviceTax'],2,'.','');
				$poa['totalAmount'] = number_format($orderHistory[0]['totalAmount'],2,'.','');
				$poa['rPointsUsed'] = $orderHistory[0]['rPointsUsed'];
				$poa['redeemAmount'] = number_format($orderHistory[0]['redeemAmount'],2,'.','');
				$poa['balanceAmount'] = number_format(($orderHistory[0]['balanceAmount']),2,'.','');
				$poa['reFundAmount'] = number_format($orderHistory[0]['reFundAmount'],2,'.','');
				$poa['closingBalance'] = number_format($orderHistory[0]['closingBalance'],2,'.','');
				$poa['paidAmount'] = number_format($orderHistory[0]['paidAmount'],2,'.','');
				$poa['adminDiscountAmount'] = number_format($orderHistory[0]['adminDiscountAmount'],2,'.','');
			}
			$this->response($poa, REST_Controller::HTTP_OK); 				
		}else{
			$message = ['message'=>'your not authorized '];
			$this->response($message, REST_Controller::HTTP_NOT_ACCEPTABLE); 					
		}
	}
	/***************************************************/
	/*************  PLACE ORDER POST *******************/
	/***************************************************/
		private function doOrder($result, $addressObj, $customerObj, $storeCode='cbs', $rPointsUsed=false,$couponCode=false, $so){
		$this->_em = $this->doctrine->em;
		$n = rand(1001,9999);
		$orderId 	= $storeCode.'-'.$so.'-M-'.date('dmY').'-'.$n;
		$rrpoints = 0; $subTotal = 0; $totalAmount = 0;
		try{
			$placeOrderId = new \Entity\PlaceOrderId();	
			$placeOrderId->setAddressId($addressObj);
			$placeOrderId->setOrderDate(date('Y-m-d H:i:s'));
			$totalItems = 0;
			foreach($result as $data){
				log_message('error','loop started ');
				$itemCost =0;
				$itemId 	= $data->itemId;
				$serviceId 	= $data->serviceId;
				//$customerId = $data->custId;
				$icount 	= $data->icount;
				$addons 	= $data->addons; 
				$cost		= $data->cost;
				$rpoints	= $data->rpoints;
				$itemCost   = $cost*$icount;
				$totalItems += $icount;
				$placeOrder = new \Entity\PlaceOrder();
				$item = $this->_em->find('Entity\Item',$itemId);
				$service = $this->_em->find('Entity\Service',$serviceId);
				//$cust = $this->_em->find('Entity\Customer',$customerId);
				$placeOrder->setItemId($item);
				$placeOrder->setServiceId($service);
				$placeOrder->setCustomerId($customerObj);
				$placeOrder->setOrderId($orderId);
				$placeOrder->setIcount($icount);
				$placeOrder->setRpoints($rpoints);
				foreach($addons as $ad){
					if($ad->acount){
						$poa = new Entity\PlaceOrderAddon();
						$addon = $this->_em->find('Entity\Addon',$ad->addon);
						$poa->setAddonId($addon);
						$poa->setCount($ad->acount);
						$itemCost = $itemCost + $ad->acount*$addon->getPrice(); 
						$placeOrder->addPlaceOrderAddon($poa);	
					}
				}
				$rrpoints +=$rpoints;
				$subTotal += $itemCost;
				$placeOrder->setCost($itemCost);
				$this->_em->persist($placeOrder);
				$this->_em->flush();		
				log_message('error','loop end ');
			}
			$ss = $this->_em->getRepository('Entity\Settings')->findOneById(1);
			$serviceTax = 0;
			$isServiceTax = $addressObj->getAreaId()->getIsServiceTax();
			if(is_object($ss) && $isServiceTax){
				$refPoints 			= $ss->getRefPoints();
				$serviceTax 		= $ss->getServiceCharge();
				$serviceCharge 	= $subTotal*$serviceTax/100;
			}else{
				$serviceCharge = 0; 	
			}
			$totalAmount = $subTotal + $serviceCharge;
			$reWardCost = 0; $redeemAmount = 0;  $reWardPoints = 0;
			$adminDiscount = 0;			$adminDiscountAmount = 0;
			if($customerObj){
				$custId = $customerObj->getId();
				$this->load->database();						
				$date = date('Y-m-d');
				$mq = "select discountPercent from customers where DATE(discountExpiry) >= DATE('$date') and cust_id = '$custId'";            
				$query = $this->db->query($mq);
				$empObj = $query->result();
				$defaultDis = 0;
				if($empObj){
					$defaultDis = $empObj[0]->discountPercent;
				}
			}
			if($defaultDis>0){
				$discount = $defaultDis;								
				$defaultDiscountAmount = number_format(floatval(($discount*$totalAmount)/100),2,'.','');
				$defaultDiscount = number_format($discount,2,'.','');
				$totalAmount = $totalAmount - $defaultDiscountAmount;
				$adminDiscount 				= $defaultDiscount;
				$adminDiscountAmount 		= $defaultDiscountAmount;
			}else{		
				if($rPointsUsed==TRUE){
					$reWardPoints 	= $customerObj->getRpoints();
					$reWardCost 	= $reWardPoints*$ss->getPointsCost()*0.0001; 
					if($totalAmount<$reWardCost){
						$reWardPoints = ($reWardCost - $totalAmount)*(100/$ss->getPointsCost());
						$redeemAmount = $totalAmount;
					}else{
						$reWardPoints = 0;
						$redeemAmount = $reWardCost;
					}
					$customerObj->setRpoints($reWardPoints+$rrpoints);
				//	log_message('error','rPointsUsed'.$reWardPoints);	
				}else{
					$customerObj->addRpoints($rrpoints);
				}
				$totalAmount = $totalAmount - $redeemAmount;
				$vendorDiscount = 0;		$vendorDiscountAmount = 0;
				$couponDiscount = 0;		$couponDiscountAmount = 0;
				/* Vendor Discount Start */
				if($customerObj){
					$custId = $customerObj->getId();
					$this->load->database();						
					$date = date('Y-m-d');
					$vq = "select vendorId from customers where cust_id = '$custId'";
					$query = $this->db->query($vq);
					$vdObj = $query->result();
					$vid = $vdObj[0]->vendorId;
					if($vid){
						$date = date('Y-m-d');
						$vid = $vdObj[0]->vendorId;
						$dq = "select discountPercent from vendors where DATE(discountExpiry) >= DATE('$date') and vendor_id = '$vid' and status = '1'";            
						$query = $this->db->query($dq);
						$disObj = $query->result();
						if($disObj){							
							$discount = $disObj[0]->discountPercent;
							$vendorDiscountAmount = number_format(floatval(($discount*$totalAmount)/100),2,'.','');
							$vendorDiscount = number_format($discount,2,'.','');
							$totalAmount = $totalAmount - $vendorDiscountAmount;
							$placeOrderId->setVendorId($vid);
						}
					}
				}
				/* Vendor Discount End */
				/* Coupon Discount Start */
				if($couponCode){				
					$couponCodeObj = $this->_em->getRepository('Entity\Coupon')->findOneBy(array('code'=>$couponCode));
					if(is_object($couponCodeObj)){
						$today = new \DateTime('today');
						if(($couponCodeObj->getStartDate()<=$today && $couponCodeObj->getExpDate()>=$today)){
							$couponCost 			= $couponCodeObj->getCost();
							$couponCount 			= $couponCodeObj->getCount();
							$minOrderVal 			= $couponCodeObj->getMinOrdVal();
							$orderCouponcode 	= $placeOrderId->getCouponCode();
							$couponDiscountAmount = 0; $couponDiscount = 0;
							if(($couponCount>0) && ($totalAmount>=$minOrderVal)){
								$couponDiscountAmount = number_format(floatval(($couponCost*$totalAmount)/100),2,'.','');
								$couponDiscount = number_format($couponCost,2,'.','');
								$totalAmount = $totalAmount - $couponDiscountAmount;
								if ($couponCode == $orderCouponcode) {
								}else{
									$couponCnt = $couponCount - 1 ;
									$couponCodeObj->setCount($couponCnt);
									$placeOrderId->setCouponCode($couponCode);
								}
							}else{
								$totalAmount = $totalAmount ;
							}							
						}
					}
				}
				/* Coupon Discount End */	
				$adminDiscount 			= 100 - (($totalAmount/$subTotal)*100);	
				//$adminDiscount 			= $vendorDiscount + $couponDiscount;
				$adminDiscountAmount 	= $vendorDiscountAmount + $couponDiscountAmount;
			}			
			if(is_object($customerObj)){
				$placeOrderId->setOrderId($orderId);
				$placeOrderId->setSubtotal($subTotal);
				$placeOrderId->setServiceTax($serviceCharge);
				$placeOrderId->setTotalAmount($totalAmount);
				$placeOrderId->setRPointsUsed($redeemAmount*1000);
				$placeOrderId->setTotalItems($totalItems);
				$placeOrderId->setPaidAmount(0);
				$placeOrderId->setBalanceAmount($totalAmount);
				$placeOrderId->setClosingBalance($totalAmount);
				$placeOrderId->setReFundAmount(0);
				$placeOrderId->setQd(0);
				$placeOrderId->setQdAmount(0);
				$placeOrderId->setAdminDiscount($adminDiscount);
				$placeOrderId->setAdminDiscountAmount($adminDiscountAmount);
				$placeOrderId->setRedeemAmount($redeemAmount);
				$placeOrderId->setCustomerId($customerObj);
				$this->_em->persist($placeOrderId);
				//$this->load->library('cbs','');
				//$this->cbs->sendSMS($placeOrderId,'mobile');
				$ord = array();
				$ord['orderId'] = $orderId;
				$ord['adminDiscountAmount'] = $adminDiscountAmount;
				return $ord;
				log_message('error',' order completed ');
			}else{
				log_message('error',' cust obj missed ');
			}
		}catch(Exception $e){
			log_message('error',$e->getMessage());
			$this->set_response($e->getMessage(), REST_Controller::HTTP_BAD_REQUEST);
		}
		return 0;
	}
	public function placeorder_post(){
		$this->_em = $this->doctrine->em;
		$input = file_get_contents("php://input");
		$result = json_decode($input);
		$cust =0;
		if(is_object($result)){
			try{
				$rPointsUsed = false;
				if(property_exists($result,'rPointsUsed')){
					$rPointsUsed = $result->rPointsUsed;
				}	
				log_message('error','rPointsUsed'.$rPointsUsed);
				$couponCode = false;
				if(property_exists($result,'couponCode')){
					$couponCode = $result->couponCode;
				}		
				$address = 0; $addressObj = new stdClass;
				$storeCode = 'cbs';
				if(property_exists($result,'addressId')){
					$address = (int)$result->addressId;
					$addressObj = $this->_em->getRepository('Entity\CustomerAddress')->findOneById($address);
					if(is_object($addressObj)){
						$storeCode = $addressObj->getAreaId()->getCode();
					}else{
						$storeCode = 'cbs';
					}
				}
				$customerId = 1;
				if(property_exists($result,'customerId')){
					$customerId = (int)$result->customerId;
					$customerObj = $this->_em->find('Entity\Customer',(int)$customerId);
					if($storeCode=='cbs'){
						$storeCode = is_object($customerObj->getApartmentId())?$customerObj->getApartmentId()->getCode():'cbs';
					}
				}else{
					$customerObj = $this->_em->find('Entity\Customer',1);
					if($storeCode=='cbs'){
						$storeCode = $customerObj->getApartmentId()->getCode();
					}
				}
				$steamIronOrders = array(); $normalOrders = array();
				foreach($result->data as $data){
					$serviceId 	= $data->serviceId;
					if($serviceId==1){
						$steamIronOrders[] = $data;
					}else{
						$normalOrders[] = $data;
					}
				}
				//$nOrderId = 0; $siOrderId = 0;
				$nOrderId = array("orderId"=>"0","adminDiscountAmount"=>"0");
				$siOrderId = array("orderId"=>"0","adminDiscountAmount"=>"0");
				if(sizeof($normalOrders)){
					$nOrderId 	=	$this->doOrder($normalOrders,$addressObj, $customerObj, $storeCode,$rPointsUsed,$couponCode,'WD');
				}
				if(sizeof($steamIronOrders)){
					$siOrderId 	=	$this->doOrder($steamIronOrders,$addressObj, $customerObj, $storeCode,$rPointsUsed,$couponCode,'SI');
				}
				if(is_object($customerObj)){
					$refId = $customerObj->getRefId();
					if(isset($refId) && $refId){
						$cust2 = $this->_em->getRepository('Entity\Customer')->findOneByEmail($refId);
						if(!is_object($cust2)){
							$cust2 = $this->_em->getRepository('Entity\Customer')->findOneByMobile($refId);
						}
						if(is_object($cust2) && $customerObj->getFirstOrder()){
							$cust2->addRpoints($refPoints);
							$this->_em->persist($cust2);
							$this->_em->flush();
						}				
					}else{
						log_message('error',' refrence id missed ');	
					}
					$customerObj->setFirstOrder(0);
					$this->_em->persist($customerObj);
					$this->_em->flush();
					$message =[ 'message' => 'Order Successfully Placed, Our Pickup boy reach you soon.','normalOrderId'=>$nOrderId,'siOrderId'=>$siOrderId];
					$this->set_response($message, REST_Controller::HTTP_CREATED);
				}else{
					$message = ['message'=>'Something went wrong customid missed...'];
					$this->set_response($message, REST_Controller::HTTP_BAD_REQUEST);
				}
			}catch(Exception $e){
				$this->set_response($e->getMessage(), REST_Controller::HTTP_BAD_REQUEST);
			}
		}else{
			$message = ['message'=>'Something went wrong...'];
			$this->set_response($message, REST_Controller::HTTP_BAD_REQUEST);
		}
	}
	public function sendSMS_post(){
		$input = file_get_contents("php://input");
		$data = json_decode($input);
		if(is_object($data)){
			if(property_exists($data, 'orderId') && $data->orderId){
				$orderId = $data->orderId;
				$this->_em = $this->doctrine->em;
				$orderIdObj = $this->_em->getRepository('Entity\PlaceOrderId')->findOneBy(array('order_id'=>$orderId));
				$this->load->library('Sms','');
				$pos = strpos($orderId, '-M-');
				if($pos===false)
					$this->sms->sendSMS($orderIdObj,'store');
				else 	
					$this->sms->sendSMS($orderIdObj,'mobile');
				$message = ['message'=>'you will receive sms '];
				$this->set_response($message, REST_Controller::HTTP_OK);
			}else{
				$message = ['message'=>'Something went wrong'];
				$this->set_response($message, REST_Controller::HTTP_BAD_REQUEST);
			}
		}else{
			$message = ['message'=>'Something went wrong'];
			$this->set_response($message, REST_Controller::HTTP_BAD_REQUEST);
		}
	}
	public function placeorder_get(){
		$input = file_get_contents("php://input");
		$data = json_decode($input);
		if(!is_object($data))
			$data = new stdClass();
		if(property_exists($data,'custId'))	
			$custId 	= $data->custId;
		$this->_em = $this->doctrine->em;
		//$this->_em->persist($cust);
		$this->_em->flush();	
		$message =[ 'message' => 'Order Successfully Placed, Our Pickup boy reach you soon' ];
		$this->set_response($message, REST_Controller::HTTP_CREATED);
	}
	public function services_post(){
		$input = file_get_contents('php://input');
		$data = json_decode($input);
		if(is_object($data)){
			try{
				$this->_em = $this->doctrine->em;
				$qb = $this->_em->createQueryBuilder();
				if(property_exists($data, 'areaId')){
					$areaId = $data->areaId;
					$areaId = $this->_em->find('Entity\Area',$areaId);
					if(is_object($areaId)){
						$services = $areaId->getServices();
					}else{
						$services = $qb->select('s')->from('Entity\Service','s')->where('s.status=:status')->setParameter('status',1)->getQuery()->getArrayResult();
					}
				}else{
					$services = $qb->select('s')->from('Entity\Service','s')->where('s.status=:status')->setParameter('status',1)->getQuery()->getArrayResult();	
				}
				$this->response($services, REST_Controller::HTTP_OK); 
			}catch(Exception $e){
				$this->set_response($e->getMessage(),REST_Controller::HTTP_BAD_REQUEST);	
			}
		}else{
			$message = ['message'=>'Something went wrong'];
			$this->set_response($message,REST_Controller::HTTP_BAD_REQUEST);
		}
	}
	public function collectionBoys_get(){
		$this->_em = $this->doctrine->em;
		$qb = $this->_em->createQueryBuilder();
		$cboys = $qb->select('cb')->from('Entity\Employee','cb')->where('cb.role_id=:role_id')->setParameter('role_id',6)->getQuery()->getArrayResult();
		$this->response($cboys, REST_Controller::HTTP_OK); 
	}
	public function accountants_get(){
		$this->_em = $this->doctrine->em;
		$qb = $this->_em->createQueryBuilder();
		$accountants = $qb->select('cb')->from('Entity\Employee','cb')->where('cb.role_id=:role_id')->setParameter('role_id',7)->getQuery()->getArrayResult();
		$this->response($accountants, REST_Controller::HTTP_OK); 
	}
	public function couponCheck_post(){
		$input = file_get_contents('php://input');
		$data = json_decode($input);
		if(is_object($data)){
			if(property_exists($data,'coupon') && $data->coupon && 
				property_exists($data,'amount') && $data->amount){
				try{
					$code = $data->coupon;
					$subTotal = $data->amount;
					$this->_em = $this->doctrine->em;
					$qb = $this->_em->createQueryBuilder();
					$this->load->database();
					$couponCodeObj = $this->_em->getRepository('Entity\Coupon')->findOneBy(array('code'=>$code));
					$c = "0";
					if(is_object($couponCodeObj)){
						$today = new \DateTime('today');
						if(($couponCodeObj->getStartDate()<=$today && 
							$couponCodeObj->getExpDate()>=$today)){
							$couponCount = $couponCodeObj->getCount();
						$minOrderVal = $couponCodeObj->getMinOrdVal();
					}
					if(($couponCount>0) && ($subTotal>=$minOrderVal)){
							//$c['id'] 	= $couponCodeObj->getId();
						$c 	= $couponCodeObj->getCost();
					}else{
						$c ="0";
					}						
				}
				$this->response($c, REST_Controller::HTTP_OK);			 
			}catch(Exception $e){
				$this->set_response($e->getMessage(),REST_Controller::HTTP_BAD_REQUEST);	
			}
		}else{
			$d="0";
			$this->set_response($d,REST_Controller::HTTP_OK);
		}
	}else{
		$message = ['message'=>'Something went wrong'];
		$this->set_response($message,REST_Controller::HTTP_BAD_REQUEST);
	}
}
public function vendorCheck_post(){
	$input = file_get_contents('php://input');
	$data = json_decode($input);
	if(is_object($data)){
		if(property_exists($data,'id') && $data->id){
			try{
				$dis = "0";
				$this->load->database();
				$vid = $data->id;
				$date = date('Y-m-d');
				$dq 	= "select discountPercent from vendors where DATE(discountExpiry) >= DATE('$date') and vendor_id = '$vid' and status = '1'";            
				$query = $this->db->query($dq);
				$disObj = $query->result();
				if($disObj){
					$dis = $disObj[0]->discountPercent;
				}	        
				$this->response($dis, REST_Controller::HTTP_OK);		 
			}catch(Exception $e){
				$this->set_response($e->getMessage(),REST_Controller::HTTP_BAD_REQUEST);	
			}
		}else{
			$dis="0";
			$this->set_response($dis,REST_Controller::HTTP_OK);
		}
	}else{
		$message = ['message'=>'Something went wrong'];
		$this->set_response($message,REST_Controller::HTTP_BAD_REQUEST);
	}
}
	public function vendor_post(){
		$input = file_get_contents('php://input');
		$data = json_decode($input);
		if(is_object($data)){
			try{
				$this->_em = $this->doctrine->em;
				$qb = $this->_em->createQueryBuilder();
				$this->load->database();
				if(property_exists($data, 'code')){
					$code = $data->code;
					$vendor = $this->_em->getRepository('Entity\Vendor')->findOneBy(array('code'=>$code));
					$v = array();
					if($vendor){
						$v['id'] = $vendor->getId();
						$v['name'] = $vendor->getName();
					}else{
						$v['name'] = "No records found";
					}
				}
				$this->response($v, REST_Controller::HTTP_OK); 
			}catch(Exception $e){
				$this->set_response($e->getMessage(),REST_Controller::HTTP_BAD_REQUEST);	
			}
		}else{
			$message = ['message'=>'Something went wrong'];
			$this->set_response($message,REST_Controller::HTTP_BAD_REQUEST);
		}
	}
	public function vendors_get(){
		$this->_em = $this->doctrine->em;
		$qb = $this->_em->createQueryBuilder();
		$this->load->database();
		$vdQ = "select vendor_id, name, email, mobile, code, discountPercent, discountExpiry, commissionPercent, commissionExpiry, vendorgroup, status from vendors";
		$vdQuery = $this->db->query($vdQ);
		$vendorObj = $vdQuery->result();
		$vendor = array();
		$vendors = array();
		foreach ($vendorObj as $k => $val) {
			$vendor['id'] = $val->vendor_id;
			$vendor['name'] = $val->name;
			$vendor['email'] = $val->email;
			$vendor['mobile'] = $val->mobile;
			$vendor['code'] = $val->code;
			$vendor['status'] = (boolval($val->status) ? 'true' : 'false')."\n";
			$vendor['vgroupId'] = $val->vendorgroup;
			$vendor['discountPercent'] = $val->discountPercent;
			$vendor['discountExpiry'] = date('d-m-Y',strtotime($val->discountExpiry));
			$vendor['commissionPercent'] = $val->commissionPercent;
			$vendor['commissionExpiry'] = date('d-m-Y',strtotime($val->commissionExpiry));
			$vendors[] = $vendor;
		}
		$this->response($vendors, REST_Controller::HTTP_OK); 
	}
	public function vendorsGroup_get(){
		$this->_em = $this->doctrine->em;
		$qb = $this->_em->createQueryBuilder();
		$this->load->database();
		$vgQ = "select vgroup_id, name, code, status from vendorsgroup";
		$vgQuery = $this->db->query($vgQ);
		$vgroupObj = $vgQuery->result();
		$vgroup = array();
		$vgroups = array();
		foreach ($vgroupObj as $k => $val) {
			$vgroup['id'] 		= $val->vgroup_id;
			$vgroup['name'] 	= $val->name;
			$vgroup['code'] 	= $val->code;
			$vgroup['status'] 	= (boolval($val->status) ? 'true' : 'false')."\n";
			$vgroups[] = $vgroup;
		}
		$this->response($vgroups, REST_Controller::HTTP_OK); 
	}
	public function areas_get(){
		$this->_em = $this->doctrine->em;
		$qb = $this->_em->createQueryBuilder();
		$areas = $qb->select('a')->from('Entity\Area','a')->where('a.status=:status')->setParameter('status',1)->getQuery()->getArrayResult();
		$this->response($areas, REST_Controller::HTTP_OK); 
	}
	public function areas_post(){
		$this->_em = $this->doctrine->em;
		$qb = $this->_em->createQueryBuilder();
		$areas = $qb->select('a')->from('Entity\Area','a')->getQuery()->getArrayResult();
		$this->response($areas, REST_Controller::HTTP_OK); 
	}
	public function roles_get(){
		$this->_em = $this->doctrine->em;
		$qb = $this->_em->createQueryBuilder();
		$roleObj = $qb->select('r')->from('Entity\Role','r')->getQuery()->getResult();
		$roles = array();
		foreach ($roleObj as $key => $obj) {
			$role = array();
			$role['id'] 	= $obj->getId();
			$role['name']	= $obj->getName();
			$roles['roles'][] = $role;
		}
		$this->response($roles, REST_Controller::HTTP_OK); 
	}
	public function premiumCustomers_get(){
		$this->_em = $this->doctrine->em;
		$qb = $this->_em->createQueryBuilder();
		$areas = $qb->select('c.id','c.firstname','c.lastname','c.package')->from('Entity\Customer','c')->getQuery()->getArrayResult();
		$this->response($areas, REST_Controller::HTTP_OK); 
	}
	public function authentication_post(){
		$this->_em = $this->doctrine->em;
		$qb = $this->_em->createQueryBuilder();
		$input = file_get_contents("php://input");
		$data = json_decode($input);
		$mobile =0;
		$password=0;
		$os_player_id = '';
		$os_push_token = '';
		if(property_exists($data, 'OsID')){
			$osId = $data->OsID;
			if(is_object($osId) && property_exists($osId, 'userId')){
				$os_player_id = $osId->userId;
				log_message('error','os_player_id'.$os_player_id); 
			}else{
				log_message('error','push notification value  doesn\'t object');
			}
			if(is_object($osId) && property_exists($osId, 'pushToken')){
				$os_push_token = $osId->pushToken;
				log_message('error','os_push_token'.$os_push_token); 
			}
		}else{
			log_message('error','push notification object is not available');
		}
		try{
			if(property_exists($data,'mobile'))	
				$mobile 		= $data->mobile;
			if(property_exists($data,'password'))	
				$password 	= md5(trim($data->password));
			$cust = $this->_em->getRepository('Entity\Customer')->findOneBy(array('mobile'=>$mobile));
			if(is_object($cust)){
				$cust1 = $this->_em->getRepository('Entity\Customer')->findOneBy(array('mobile'=>$mobile,'password'=>$password));				
				if(is_object($cust1)){
					$cust2 = $this->_em->getRepository('Entity\Customer')->findOneBy(array('mobile'=>$mobile,'password'=>$password,'status'=>1));
					if(is_object($cust2)){
						$customerId = $cust2->getId();
						$areaObj = $cust2->getAreaId();
						if(is_object($areaObj)){
							$areaId 	= $areaObj->getId();
							$areaName 	= $areaObj->getName();
						}else{
							$areaId = '';
							$areaName = '';	
						}
						$custObj = $this->_em->find('Entity\Customer',$customerId);
						if(is_object($custObj)){									
							$token = array();
							$token['id'] = $custObj->getId();
							$jwttoken  = JWT::encode($token,$this->config->item('jwt_key'));
							$custObj->setOauthId($jwttoken);
							$custObj->setOsPlayerId($os_player_id);
							$custObj->setOsPushToken($os_push_token);
							$oauth_id = $cust2->getOauthId();
							$this->_em->persist($custObj);
							$this->_em->flush();
							$message =[ 'message' => 'Authentication successfull' ,'id'=>$customerId,'areaId'=>$areaId, 'mobile'=>$mobile, 'areaName'=>$areaName,'token'=>$jwttoken];
							$this->response($message, REST_Controller::HTTP_ACCEPTED); 
						}
					}else{
						$message =[ 'message' => ' please wait for your family head approval '];
						$this->response($message, REST_Controller::HTTP_NOT_ACCEPTABLE); 		
					}
				}else{
					$message =[ 'message' => 'in correct login details , please try again '];
					$this->response($message, REST_Controller::HTTP_NOT_ACCEPTABLE); 	
				}
			}else{
				$message =[ 'message' => 'you have no account, please register '];
				$this->response($message, REST_Controller::HTTP_NOT_ACCEPTABLE); 
			}
			//$cust = $qb->select('c.id as customerId, c.oauth_id as oauth_id')->from('Entity\Customer','c')->where('c.mobile =:mobile and c.password =:pwd and c.status =:status')->setParameter('mobile',$mobile)->setParameter('pwd',$password)->setParameter('status',1)->getQuery()->getArrayResult();
			if(count($cust)){
				//$customerId = sizeof($cust)?$cust[0]['customerId']:'';
				//$oauth_id = sizeof($cust)?$cust[0]['oauth_id']:'';
				$custObj = $this->_em->find('Entity\Customer',$customerId);
				if(is_object($custObj)){
					$key = md5($this->getnewpwd());
					$custObj->setOauthId($key);
					$areaId = is_object($custObj->getAreaId())?$custObj->getAreaId()->getId():0;
					$this->_em->persist($custObj);
					$this->_em->flush();
					$message =[ 'message' => 'Authentication successfull' ,'id'=>$customerId,'areaId'=>$areaId,'oauth_id'=>$key];
					$this->response($message, REST_Controller::HTTP_ACCEPTED); 
				}
			}else{
				$message =[ 'message' => 'Authentication failed try again later'];
				$this->response($message, REST_Controller::HTTP_NOT_ACCEPTABLE); 
			}
		}catch(Exception $e){
			$message =[ 'message' => 'Something wrong , pelase contact administrator '];
			$this->response($message, REST_Controller::HTTP_NOT_ACCEPTABLE); 
		}
	}
	/***************************************************/
	/***********  CUSTOMER REGISTRATION ****************/
	/***************************************************/
	public function registration_post(){
		$input = file_get_contents("php://input");
		$data = json_decode($input);
		$this->_em = $this->doctrine->em;
		if(!is_object($data))
			$data = new stdClass();
		$mobile ='';
		$userType 	= '';
		$cust = new Entity\Customer();
		$fname=''; $lname=''; $mobile='';
		if(property_exists($data,'firstName'))	
			$fname 		= $data->firstName;
		if(property_exists($data,'lastName'))	
			$lname 		= $data->lastName;
		if(property_exists($data,'email'))	
			$email 		= $data->email;
		if(property_exists($data,'mobile'))	
			$mobile 	= trim($data->mobile);
		if(property_exists($data,'password'))	
			$password 	= trim($data->password);
		if(property_exists($data,'apartment'))	
			$aprt		= $data->apartment;
		if(property_exists($data,'block'))	
			$block		= $data->block;
		if(property_exists($data,'flat'))	
			$flat		= $data->flat;
		if(property_exists($data,'refId'))	
			$refId		= $data->refId;
		if(property_exists($data, 'areaId')){
			$areaId = $data->areaId;
			$areaId = $this->_em->find('Entity\Area',$areaId);
			if(is_object($areaId)){
				$cust->setAreaId($areaId);
			}
		}
		$ss = $this->_em->getRepository('Entity\Settings')->findOneById(1);
		if(is_object($ss)){
		//	$refPoints = $ss->getRefPoints();
			$regPoints = $ss->getRegPoints();
		}else{
			//$refPoints = 0;
			$regPoints = 0;
		}
		if(isset($mobile)){
			$cust2 = $this->_em->getRepository('Entity\Customer')->findOneByMobile($mobile);
			if(is_object($cust2)){
				$message =[ 'message' =>'you already have account, please login.' ];
				$this->set_response($message, REST_Controller::HTTP_OK);
				return false;
			}
		}
		if(property_exists($data, 'vCode')){
			$vCode = $data->vCode;
		}else{
			$vCode = NULL;
		}
		if($vCode !=''){
			$this->_em = $this->doctrine->em;
			$qb = $this->_em->createQueryBuilder();
			$vendor = $this->_em->getRepository('Entity\Vendor')->findOneBy(array('code'=>$vCode));
			if(is_object($vendor)){
				$vId = $vendor->getId();
				$cust->setVendorId($vId);
			}else{
				$message =[ 'message' =>'Vendor Code not fount try again.' ];
				$this->set_response($message, REST_Controller::HTTP_OK);
				return false;				
			}
		}
		/*$cust2 = $this->_em->getRepository('Entity\Customer')->findOneByEmail($refId);
		if(is_object($cust2)){
			$cust2->addRpoints($refPoints);
			$this->_em->persist($cust2);
			$this->_em->flush();
		}*/
		//$cust = new Entity\Customer();
		$cust->setFirstName($fname);
		$cust->setLastName($lname);
		$cust->setEmail($email);
		$cust->setPhoneNo($mobile);
		$cust->setRpoints($regPoints);
		$cust->setPassword($password);
		$cust->setRefId($refId);
		if(isset($aprt)){
			$apartment = $this->_em->find('Entity\Apartment',$aprt);
			if(is_object($apartment)){
				$cust->setApartmentId($apartment);
				$userType 	= 'apartment';
				$subType 	= 'family member';
				$cust->setUserType($userType);
				$cust->setSubType($subType);
				$cust->setStatus(0);	
			}else{
				$userType 	= 'user';
				$subType 	= '';
				$cust->setUserType($userType);
				$cust->setSubType($subType);
				$cust->setStatus(1);	
			}
		}else{
			$userType 	= 'user';
			$subType 	= '';
			$cust->setUserType($userType);
			$cust->setSubType($subType);
			$cust->setStatus(1);	
		}
		if(isset($block)){
			$blockId = $this->_em->find('Entity\Block',$block);
			if(is_object($blockId)){
				$cust->setBlockId($blockId);
			}
		}
		if(isset($flat)){
			$flatId = $this->_em->find('Entity\Flat',$flat);
			if(is_object($flatId)){
				$cust->setFlatId($flatId);
			}
		}
		$this->load->library('Sms', $this->_em);
		$this->sms->thankYouNDetailsMessage($cust, $password);
		$this->_em->persist($cust);
		$this->_em->flush();
		if($userType=='apartment'){
			$message =[ 'message' => 'You successfully registered, please wait for ' ];			
		}else{
			$message =[ 'message' => 'You successfully registered.' ];
		}
		$this->set_response($message, REST_Controller::HTTP_CREATED);
	}
	public function settings_get(){
		$this->_em = $this->doctrine->em;
		$qb = $this->_em->createQueryBuilder();
		$settings = $qb->select('s')->from('Entity\Settings','s')->getQuery()->getArrayResult();
		$this->response($settings[0], REST_Controller::HTTP_OK); 
	}
	public function getrpoints_post(){
		$input = file_get_contents("php://input");
		$data = json_decode($input);
		if(!is_object($data))
			$data = new stdClass();
		$id=0;
		if(property_exists($data,'id'))	
			$id 		= $data->id;
		$this->_em = $this->doctrine->em;
		$qb = $this->_em->createQueryBuilder();
		$cust = $qb->select('c.rpoints')->from('Entity\Customer','c')->where('c.id=:id')->setParameter('id',$id)->getQuery()->getArrayResult();
		$this->response($cust[0], REST_Controller::HTTP_OK);
	}
	/********************************************************/
	/************* CUSTOMER GET PROFILE ********************/
	/********************************************************/
	public function getprofile_post(){
		$input = file_get_contents("php://input");
		$data = json_decode($input);
		if(!is_object($data))
			$data = new stdClass();
		$id=0;
		if(property_exists($data,'id'))	
			$id 		= $data->id;
		$this->_em = $this->doctrine->em;
		$qb = $this->_em->createQueryBuilder();
		$cust = $qb->select('c.id','c.firstname','c.lastname','c.gender','c.email','c.whatsapp','c.dob','c.showInTele','c.facebook','c.isStaying','c.type','c.mobile','c.address','c.status','c.rpoints','c.ref_id','c.vendorId','c.discountPercent','Entity\Apartment.id as apt_id','Entity\Block.id as block_id','Entity\Block.name as block','Entity\Flat.id as flat_id','Entity\Flat.name as flat')->from('Entity\Customer','c')->leftJoin('c.apt_id','Entity\Apartment')->leftJoin('c.block_id','Entity\Block')->leftJoin('c.flat_id','Entity\Flat')->where('c.id=:id')->setParameter('id',$id)->getQuery()->getArrayResult();
		if(isset($cust)){
			$this->response($cust[0], REST_Controller::HTTP_OK);
		}else{
			$this->response(NULL, REST_Controller::HTTP_OK);
		}
	}
	/********************************************************/
	/************* CUSTOMER UPDATE PROFILE ********************/
	/********************************************************/
	public function updateprofile_post(){
		$input = file_get_contents("php://input");
		$data = json_decode($input);
		$id =0;
		if(property_exists($data,'id'))
			$id 		= $data->id;
		if((int)$id){
			$this->_em = $this->doctrine->em;
			$cust = $this->_em->getRepository('Entity\Customer')->findOneById($id);
			if(property_exists($data,'email')){
				$email 		= $data->email;
				$cust->setEmail($email);
			}
			if(property_exists($data,'gender')){
				$gender 		= $data->gender;
				$cust->setGender($gender);
			}
			if(property_exists($data,'isStaying')){
				$isStaying 		= $data->isStaying;
				$cust->setStaying($isStaying);
			}
			if(property_exists($data,'facebook')){
				$facebook 		= $data->facebook;
				$cust->setFacebook($facebook);
			}
			$nm = '';
			if(property_exists($data,'mobile')){
				$mobile 	= $data->mobile;
				$mobileObj = $this->_em->getRepository('Entity\Customer')->findOneByMobile($mobile);
				if(is_object($mobileObj)){
					$nm = ' but mobile no already exists. it\'s not updated';
				}else{
					$nm = '';
					$cust->setPhoneNo($mobile);	
				}
			}
			if(property_exists($data,'rpoints')){
				$rpoints 	= $data->rpoints;
				$cust->setRpoints($rpoints);
			}
			if(property_exists($data,'userType')){
				$userType 	= $data->userType;
				$cust->setUserType($userType);
			}
			if(property_exists($data,'apartment')){
				$aprt		= $data->apartment;
				$aprtObj = $this->_em->getRepository('Entity\Apartment')->findOneById($aprt);
			//$cust->setApartmentId($aprtObj);
			}
			if(property_exists($data,'block')){
				$block		= $data->block;
				$blockObj = $this->_em->getRepository('Entity\Block')->findOneById($block);
			//$cust->setBlockId($blockObj);
			}
			if(property_exists($data,'flat')){
				$flat		= $data->flat;
				$flatObj = $this->_em->getRepository('Entity\Flat')->findOneById($flat);
			//$cust->setFlatId($flatObj);
			}
			if(property_exists($data,'firstName')){
				$fname 		= $data->firstName;	
				$cust->setFirstName($fname);
			}
			if(property_exists($data,'lastName')){
				$lname 		= $data->lastName;		
				$cust->setLastName($lname);
			}
			if(property_exists($data,'whatsapp')){
				$whatsapp 		= $data->whatsapp;
				$cust->setWhatsapp($whatsapp);
			}
			if(property_exists($data,'dob')){
				$dob 		= $data->dob;
				$cust->setDob($dob);
			}
			if(property_exists($data,'showInTele')){
				$showInTele 		= $data->showInTele;
				$cust->setShowInTele($showInTele);
			}
			$this->_em->persist($cust);
			$this->_em->flush();	
			$message =[ 'message' => 'resource updated '.$nm ];
			$this->set_response($message, REST_Controller::HTTP_OK);
		}else{
			$message =[ 'message' => ' your unauthorized person to update the data' ];
			$this->set_response($message, REST_Controller::HTTP_UNAUTHORIZED);
		}
	}
	public function getaddress_post(){
		$input = file_get_contents("php://input");
		$data = json_decode($input);
		if(!is_object($data))
			$data = new stdClass();
		$id=0;
		if(property_exists($data,'customerId'))	
			$id 		= $data->customerId;
		if((int)$id){
			$this->_em = $this->doctrine->em;
			$qb = $this->_em->createQueryBuilder();
			$custs = $qb->select('ca','Entity\Customer','Entity\Area')->from('Entity\CustomerAddress','ca')->leftJoin('ca.cust_id','Entity\Customer')->leftJoin('ca.area_id','Entity\Area')->where('ca.cust_id=:id')->setParameter('id',$id)->getQuery()->getArrayResult();
			$address = array();
			foreach($custs as $k=>$v){
				$ad =  array();
				$ad['address_id'] = $v['id'];
				$ad['address'] = $v['address'];
				$ad['landmark'] = $v['landmark'];
				$ad['pincode'] = $v['pincode'];
				$ad['area_id'] = $v['area_id']['id'];
				$ad['serviceTax'] = $v['area_id']['isServiceTax'];
				$ad['area'] = $v['area_id']['name'];
				$address[] = $ad;
			}
			if(empty($custs)){
				$msg = [];
				$this->response($msg, REST_Controller::HTTP_OK);					
			}
			$this->response($address, REST_Controller::HTTP_OK);
		}
		$msg = [];
		$this->response($msg, REST_Controller::HTTP_OK);			
	}
	public function postaddress_post(){
		$input = file_get_contents("php://input");
		$data = json_decode($input);
		$id 		= '';
		$address 		= '';
		$landmark 		= '';
		$pincode 		= '';
		$areaId			= 0;
		if(property_exists($data,'customerId')){
			$id 		= $data->customerId;
		}
		if(property_exists($data,'address')){
			$address 		= $data->address;
		}
		if(property_exists($data,'landmark')){
			$landmark 		= $data->landmark;
		}
		if(property_exists($data,'pincode')){
			$pincode 		= $data->pincode;
		}
		if(property_exists($data,'areaId')){
			$areaId			= $data->areaId;
		}
		if((int)$id){
			$this->_em = $this->doctrine->em;
			$customerId = $this->_em->find('Entity\Customer',$id);
			if(!is_object($customerId)){
				$msg = ['message'=>'Your not recognized to save your address'];
				$this->response($msg, REST_Controller::HTTP_OK);						
			}
			$areaObj = $this->_em->find('Entity\Area',$areaId);
			if(!is_object($areaObj)){
				$msg = ['message'=>'Your have not selected area'];
				$this->response($msg, REST_Controller::HTTP_OK);						
			}
			$ca = new Entity\CustomerAddress();
			$ca->setAddress($address);
			$ca->setLandmark($landmark);
			$ca->setPincode($pincode);
			$ca->setCustomerId($customerId);
			$ca->setAreaId($areaObj);
			$this->_em->persist($ca);
			$this->_em->flush();
			$msg = ['message'=>'Your address saved successfully Mr./Ms: '.$customerId->getFirstname()];
			$this->response($msg, REST_Controller::HTTP_OK);
		}
		$msg = ['message'=>'Your not recognized to save your address'];
		$this->response($msg, REST_Controller::HTTP_OK);			
	}
	public function updateaddress_post(){
		$input = file_get_contents("php://input");
		$data = json_decode($input);
		if(!is_object($data))
			$data = new stdClass();
		$addressId 		= 0;
		$address 		= '';
		$landmark 		= '';
		$pincode 		= '';
		$areaId			= 0;
		if(property_exists($data,'addressId')){
			$addressId 		= $data->addressId;
		}
		if(property_exists($data,'address')){
			$address 		= $data->address;
		}
		if(property_exists($data,'landmark')){
			$landmark 		= $data->landmark;
		}
		if(property_exists($data,'pincode')){
			$pincode 		= $data->pincode;
		}
		if(property_exists($data,'areaId')){
			$areaId			= $data->areaId;
		}
		if((int)$addressId){
			$this->_em = $this->doctrine->em;
			$ca = $this->_em->find('Entity\CustomerAddress',$addressId);
			if(!is_object($ca)){
				$msg = ['message'=>'Your not recognized to save your address'];
				$this->response($msg, REST_Controller::HTTP_NOT_ACCEPTABLE);						
			}
			$areaObj = $this->_em->find('Entity\Area',$areaId);
			if(!is_object($areaObj)){
				$msg = ['message'=>'Your have not selected area'];
				$this->response($msg, REST_Controller::HTTP_OK);						
			}
			$ca->setAddress($address);
			$ca->setLandmark($landmark);
			$ca->setPincode($pincode);
			if(is_object($areaObj))
				$ca->setAreaId($areaObj);
			$this->_em->persist($ca);
			$this->_em->flush();
			$msg = ['message'=>'Your address suceessfully updated'];
			$this->response($msg, REST_Controller::HTTP_OK);
		}
		$msg = ['message'=>'Your not recognized to save your address'];
		$this->response($msg, REST_Controller::HTTP_NOT_ACCEPTABLE);			
	}
	public function trashAddress_post(){
		$input = file_get_contents("php://input");
		$data = json_decode($input);
		if(!is_object($data))
			$data = new stdClass();
		if(property_exists($data,'addressId')){
			$addressId 		= $data->addressId;
			$this->_em = $this->doctrine->em;
			$ca = $this->_em->find('Entity\CustomerAddress',$addressId);
			if(is_object($ca)){
				$ca->setStatus(0);
				$this->_em->persist($ca);
				$this->_em->flush();
				$msg = ['message'=>'Your address suceessfully updated'];
				$this->response($msg, REST_Controller::HTTP_OK);
			}else{
				$msg = ['message'=>'Your address not recognized '];
				$this->response($msg, REST_Controller::HTTP_NOT_ACCEPTABLE);
			}
		}else{
			$msg = ['message'=>'Your not recognized to save your address'];
			$this->response($msg, REST_Controller::HTTP_NOT_ACCEPTABLE);		
		}
	}
	public function forgotpwd_put(){
		$input = file_get_contents("php://input");
		$data = json_decode($input);
		$email 		= $data->email;
		$this->_em = $this->doctrine->em;
		if($email){
			$cust = $this->_em->getRepository('Entity\Customer')->findOneByEmail($email);
			$password = $this->getnewpwd();
			$cust->setPassword($password);
			$cust->setResetPassword(1);
			$this->_em->persist($cust);
			$this->_em->flush();
			$this->load->library('email');		
			$this->email->from('admin@laundrywaves.com', 'Laundry Waves');
			$this->email->to($email,$name); 
			$this->email->bcc('sandypublic@gmail.com'); 
			$this->email->subject('Laundry Waves: Find your new password from Laundry Waves');
			$body = '<br>
			Dear '.$name.'
			<br/>
			Your password is : '.$password;
			$this->email->message($body);	
			$this->email->send();
			$name = $cust->getFirstName($fname).' '.$cust->getLastName($lname);
			$email = $cust->getEmail($email);
			$mobile = $cust->getPhoneNo($mobile);
			$message =[ 'message' => 'Your password is successfully updated' ];
			$this->set_response($message, REST_Controller::HTTP_OK);
		}else{
			$message =[ 'message' =>'your not authorised person, please contact administrator.'];
			$this->set_response($message, REST_Controller::HTTP_UNAUTHORIZED);
		}
	}
	public function resetPassword_post(){
		$input = file_get_contents("php://input");
		$data = json_decode($input);
		$mobile 		= $data->mobile;
		$this->_em = $this->doctrine->em;
		if($mobile){
			$customerObj = $this->_em->getRepository('Entity\Customer')->findOneByMobile($mobile);
			if(is_object($customerObj)){
				$password = $this->getnewpwd();
				$customerObj->setPassword($password);
				$customerObj->setResetPassword(1);
				$this->_em->persist($customerObj);
				$this->_em->flush();
				$this->load->library('cbs','');		
				$this->cbs->regSMS($customerObj,$password);	
				$message =[ 'message' => 'Your password is sent to registered mobile' ];
				$this->set_response($message, REST_Controller::HTTP_OK);	
			}else{
				$message =[ 'message' =>'your not authorised person, please contact administrator.'];
				$this->set_response($message, REST_Controller::HTTP_UNAUTHORIZED);
			}
		}else{
			$message =[ 'message' =>'your not authorised person, please contact administrator.'];
			$this->set_response($message, REST_Controller::HTTP_UNAUTHORIZED);
		}
	}
	public function changepwd_post(){
		$input 	= file_get_contents("php://input");
		$data 	= json_decode($input);
		$email = 0;
		$password =0;
		if(property_exists($data,'customerId')){	
			$email 		= $data->customerId;
		}
		if(property_exists($data,'password')){	
			$password	= $data->password;
		}
		$this->_em = $this->doctrine->em;
		if($email){
			$cust = $this->_em->getRepository('Entity\Customer')->findOneById((int)$email);
			if(is_object($cust)){
				$cust->setPassword($password);
				$cust->setResetPassword(0);
				$this->_em->persist($cust);
				$this->_em->flush();
				$message =[ 'message' => 'Your password is successfully updated and sent back to respective email id.' ];
				$this->set_response($message, REST_Controller::HTTP_OK);
			}else{
				$message =[ 'message' =>'lol, please logout. your not authorised your.'];
				$this->set_response($message, REST_Controller::HTTP_UNAUTHORIZED);						
			}
		}else{
			$message =[ 'message' =>'your not authorised person, please contact administrator.'];
			$this->set_response($message, REST_Controller::HTTP_UNAUTHORIZED);		
		}
	}
	public function vehicle_post(){
	}
	public function itemImageUpload_post(){
		try{
			$root = $this->config->item('base_url');
			$config = array(
				'upload_path' => APPPATH."../uploads/items",
				'absolute_path' => $root."uploads/items",
				'allowed_types' => "gif|jpg|png|jpeg|pdf",
				'overwrite' => TRUE,
				'max_size' => "2048000"
				//'max_height' => "768",
				//'max_width' => "1024"
			);
			$this->load->library('upload', $config);
			if($this->upload->do_upload('image')){
				if($this->upload->display_errors()!=''){
					$error = $this->upload->display_errors(); 
					$this->set_response($error, REST_Controller::HTTP_BAD_REQUEST);	
				}else{
					$image =  $this->upload->data();
					$message = ['image'=>$image];
					$this->set_response($message, REST_Controller::HTTP_OK);	
				}
			}else{
				$error = $this->upload->display_errors(); 
				$this->set_response($error, REST_Controller::HTTP_BAD_REQUEST);	
			}
		}catch(Exception $e){
			$this->set_response($e->getMessage(), REST_Controller::HTTP_BAD_REQUEST);	
		}
	}
	public function serviceImageUpload_post(){
		try{
			$root = $this->config->item('base_url');
			$path = APPPATH."../uploads/services";
			if(!is_dir($path)){
				mkdir($path);
			}
			$config = array(
				'upload_path' =>$path,
				'absolute_path' => $root."uploads/services",
				'allowed_types' => "gif|jpg|png|jpeg|pdf",
				'overwrite' => TRUE,
				'max_size' => "2048000"
				//'max_height' => "768",
				//'max_width' => "1024"
			);
			$this->load->library('upload', $config);
			if($this->upload->do_upload('image')){
				if($this->upload->display_errors()!=''){
					$error = $this->upload->display_errors(); 
					$this->set_response($error, REST_Controller::HTTP_BAD_REQUEST);	
				}else{
					$image =  $this->upload->data();
					$message = ['image'=>$image];
					$this->set_response($message, REST_Controller::HTTP_OK);	
				}
			}else{
				$error = $this->upload->display_errors(); 
				$this->set_response($error, REST_Controller::HTTP_BAD_REQUEST);	
			}
		}catch(Exception $e){
			$this->set_response($e->getMessage(), REST_Controller::HTTP_BAD_REQUEST);	
		}
	}
	public function visitorImageUpload_post(){
		try{
			$root = $this->config->item('base_url');
			$path = APPPATH."../uploads/visitor";
			if(!is_dir($path)){
				mkdir($path);
			}
			$config = array(
				'upload_path' =>$path ,
				'absolute_path' => $root."uploads/visitor",
				'allowed_types' => "gif|jpg|png|jpeg|pdf",
				'overwrite' => TRUE,
				'max_size' => "2048000"
				//'max_height' => "768",
				//'max_width' => "1024"
			);
			$this->load->library('upload', $config);
			if($this->upload->do_upload('image')){
				if($this->upload->display_errors()!=''){
					$error = $this->upload->display_errors(); 
					$this->set_response($error, REST_Controller::HTTP_BAD_REQUEST);	
				}else{
					$image =  $this->upload->data();
					$message = ['image'=>$image];
					$this->set_response($message, REST_Controller::HTTP_OK);	
				}
			}else{
				$error = $this->upload->display_errors(); 
				$this->set_response($error, REST_Controller::HTTP_BAD_REQUEST);	
			}
		}catch(Exception $e){
			$this->set_response($e->getMessage(), REST_Controller::HTTP_BAD_REQUEST);	
		}
	}
	public function adImageUpload_post(){
		try{
			$root = $this->config->item('base_url');
			$path = APPPATH."../uploads/ads";
			if(!is_dir($path)){
				mkdir($path);
			}
			$config = array(
				'upload_path' => $path,
				'absolute_path' => $root."uploads/ads",
				'allowed_types' => "gif|jpg|png|jpeg|pdf",
				'overwrite' => TRUE,
				'max_size' => "2048000"
				//'max_height' => "768",
				//'max_width' => "1024"
			);
			$this->load->library('upload', $config);
			if($this->upload->do_upload('image')){
				if($this->upload->display_errors()!=''){
					$error = $this->upload->display_errors(); 
					$this->set_response($error, REST_Controller::HTTP_BAD_REQUEST);	
				}else{
					$image =  $this->upload->data();
					$message = ['image'=>$image];
					$this->set_response($message, REST_Controller::HTTP_OK);	
				}
			}else{
				$error = $this->upload->display_errors(); 
				$this->set_response($error, REST_Controller::HTTP_BAD_REQUEST);	
			}
		}catch(Exception $e){
			$this->set_response($e->getMessage(), REST_Controller::HTTP_BAD_REQUEST);	
		}
	}
	public function notificationFileUpload_post(){
		try{
			$root = $this->config->item('base_url');
			$filePath = APPPATH."../uploads/notifications";
			if(!is_dir($filePath)){
				mkdir($filePath,TRUE);
			}
			$config = array(
				'upload_path' => APPPATH."../uploads/notifications",
				'absolute_path' => $root."uploads/notifications",
				'allowed_types' => "doc|docx|xls|xlsx|ppt|gif|jpg|png|jpeg|pdf",
				'overwrite' => TRUE,
				'max_size' => "2048000"
				//'max_height' => "768",
				//'max_width' => "1024"
			);
			$this->load->library('upload', $config);
			if($this->upload->do_upload('noti_file')){
				if($this->upload->display_errors()!=''){
					$error = $this->upload->display_errors(); 
					$this->set_response($error, REST_Controller::HTTP_BAD_REQUEST);	
				}else{
					$image =  $this->upload->data();
					$message = ['image'=>$image];
					$this->set_response($message, REST_Controller::HTTP_OK);	
				}
			}else{
				$error = $this->upload->display_errors(); 
				$this->set_response($error, REST_Controller::HTTP_BAD_REQUEST);	
			}
		}catch(Exception $e){
			$this->set_response($e->getMessage(), REST_Controller::HTTP_BAD_REQUEST);	
		}
	}
	public function customerPackages_post(){
		$input = file_get_contents("php://input");
		$data = json_decode($input);
		if(is_object($data)){
			if(property_exists($data, 'customerId') && $customerId = $data->customerId){
				$this->_em = $this->doctrine->em;
				$customerObj = $this->_em->find('Entity\Customer',$customerId);
				if(property_exists($data, 'package') && $package = $data->package){
					$customerObj->setPackage($package);
					$code = random_string('alnum', 10);
					$customerObj->setPackageCode($code);
					$customerObj->setPackageStatus(0);
					$this->_em->persist($customerObj);
					$this->_em->flush();
					$message = ['message'=>'package added sucessfully'];
					$this->response($message, REST_Controller::HTTP_OK); 
				}
			}else{
				$message = ['message'=>'your not authorized '];
				$this->response($message, REST_Controller::HTTP_NOT_ACCEPTABLE);
			}
		}else{
			$message = ['message'=>'your not authorized '];
			$this->response($message, REST_Controller::HTTP_NOT_ACCEPTABLE); 			
		}
	}
	public function getPackage_post(){
		$input = file_get_contents("php://input");
		$data = json_decode($input);
		if(is_object($data)){
			if(property_exists($data, 'pid')){
				$this->_em = $this->doctrine->em;
				$pid = $data->pid;
				$packageObj = $this->_em->find('Entity\Package',$pid);
				$package = array();
				$package['pid'] 		= $packageObj->getId();
				$package['name'] 		= $packageObj->getName();
				$package['cost'] 		= $packageObj->getCost();
				$package['duration'] 	= $packageObj->getDurationInDays();
				$package['details']		= $packageObj->getPackageDetails();		
				$catalogId =1;
				$qb = $this->_em->createQueryBuilder();
				$cps = $qb->select('cp')->from('Entity\CatalogPrice','cp')->where('cp.catalog_id =:catalogId')->setParameters(array('catalogId'=>$catalogId))->getQuery()->getResult();
				$catlogItems = array();
				foreach ($cps as $key => $obj){
					$item = array();
					$item['ikey'] = $obj->getServiceId()->getId().'-'.$obj->getItemTypeId()->getId().'-'.$obj->getItemId()->getId();
					$item['ival'] = $obj->getServiceId()->getName().'-'.$obj->getItemTypeId()->getName().'-'.$obj->getItemId()->getName();
					$catlogItems[] = $item;
				}
				$package['catalogItems'] = $catlogItems;
				$addObj = $this->_em->getRepository('Entity\Addon')->findAll();
				$addons = array();
				foreach ($addObj as $key => $obj) {
					$addon = array();
					$addon['id'] 	= (string)$obj->getId();
					$addon['name'] 	= $obj->getName();
					$addons[] = $addon;
				}
				$package['addons'] = $addons;
				$this->response($package, REST_Controller::HTTP_OK); 
			}else{
				$message = ['message'=>'your not authorized '];
				$this->response($message, REST_Controller::HTTP_NOT_ACCEPTABLE);
			}
		}else{
			$message = ['message'=>'your not authorized '];
			$this->response($message, REST_Controller::HTTP_NOT_ACCEPTABLE); 			
		}
	}
	private function getnewpwd() {
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		return substr(str_shuffle($chars),0,8);
	}
}