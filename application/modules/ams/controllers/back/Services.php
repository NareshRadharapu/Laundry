<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Services extends CI_Controller {

    function __construct()

    {
  		 parent::__construct();

 	}

	public function index(){
		$this->_em = $this->doctrine->em;
		$data['services'] = $this->_em->getRepository('Entity\Service')->findAll();
		$data['body'] = 'services';
		$this->load->view('admin',$data);
	}
	public function lists(){

		$this->_em = $this->doctrine->em;
		$qb = $this->_em->createQueryBuilder();
		$services = $qb->select('s','Entity\Addon')->from('Entity\Service','s')->addOrderBy('s.id','desc')->leftJoin('s.serviceAddons','Entity\Addon')->getQuery()->getArrayResult();

		echo json_encode($services);

		die();
	}

	public function listsz(){

		$this->_em = $this->doctrine->em;

		$qb = $this->_em->createQueryBuilder();

		$services = $qb->select('s')->from('Entity\Service','s')->where('s.status=:status')->setParameter('status',1)->getQuery()->getArrayResult();

		echo json_encode($services);

		die();

		

	}


	public function addons(){

		$input = file_get_contents("php://input");
		$data = json_decode($input);
		$itid = $data;
		$this->_em = $this->doctrine->em;
		$qb = $this->_em->createQueryBuilder();
		$items = array();
		
		$service  = $this->_em->find('Entity\Service',$itid);
			if(is_object($service)){
		$addons = $service->getAddons();
		}else{
			$addons = array();
			log_message('warning', $itid.' doesn\'t have addons ');
		}
		echo json_encode($addons);
		die();
		

	}

	

	public function items(){

		$input = file_get_contents("php://input");
		$data = json_decode($input);

		//$itid = $data->itemtype;
		$itid = $data;
		$this->_em = $this->doctrine->em;
		$qb = $this->_em->createQueryBuilder();
		$items = array();

		$items = $qb->select('i')->from('Entity\Item','i')->innerJoin('i.itype_id','Entity\ItemType')->where('i.itype_id=:itype_id')->setParameter('itype_id',$itid)->getQuery()->getArrayResult();

		echo json_encode($items);

		die();

	}

	

	public function add(){

		

	}

	public function store(){

		

		$input = file_get_contents("php://input"); 

		$data = json_decode($input);

		$serviceId	=  $data->id;

		$name 	     = $data->name;

		$image       = $data->image;

		$addons      = $data->saddons;
		$code		=  $data->code;

		$description  = $data->description;

				

		$this->_em = $this->doctrine->em;

		

		if($serviceId){

			$service = $this->_em->find('Entity\Service',$serviceId);

		}else{

			$service = new Entity\Service();

		}

		

		$service->removeAddon();

		

		foreach($addons as $ad){

				$addon = $this->_em->find('Entity\Addon',$ad);

				$service->addAddon($addon);

		 }

			 	

		$service->setName($name);
		$service->setCode($code);

		$service->setDescription($description);

		

		$service->setImage($image);

		$this->_em->persist($service);

		$this->_em->flush();

	      die();

	}

	public function update($id){

	

			

	}

	public function edit(){

		

		$input = file_get_contents("php://input");

		$data = json_decode($input);

		$id = $data;

		$this->_em = $this->doctrine->em;

		$qb = $this->_em->createQueryBuilder();

		 $services = $qb->select('s','Entity\Addon')->from('Entity\Service','s')->leftJoin('s.serviceAddons','Entity\Addon')->where('s.id=:id')->setParameter('id',$id)->getQuery()->getArrayResult();

		if(sizeof($services))

		echo json_encode($services[0]);

		die();	

	}

	public function status(){

		$input = file_get_contents("php://input"); 

		$data = json_decode($input);

		

		$this->_em = $this->doctrine->em;

		

		if($data){

			$service = $this->_em->find('Entity\Service',$data);

		}else{

			return json_encode(false);

		}

		

		$service->setStatus(!$service->getStatus());

		$this->_em->persist($service);

		$this->_em->flush();

	      die();

	}

	

	public function upload(){

		if ( !empty( $_FILES ) ) {

			$name = $_POST['name'];

			$tempPath = $_FILES['file']['tmp_name'];

			$filename = $_FILES['file']['name'];

			$uploadPath = 'uploads/services' . DIRECTORY_SEPARATOR . $_FILES['file']['name'];

			$ext = pathinfo($filename, PATHINFO_EXTENSION);

// 		    $uploadPath = 'uploads' . DIRECTORY_SEPARATOR . $_GET['name'].'.jpg';

			move_uploaded_file( $tempPath, $uploadPath );

			$answer = array( 'answer' => 'File transfer completed' );

			$json = json_encode( $answer );

			echo $json;

		

		} else {

			echo 'No files';

		}

	}

	

}