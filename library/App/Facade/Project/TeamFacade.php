<?php
namespace App\Facade\Project;

use Doctrine\DBAL\Schema\Visitor\RemoveNamespacedAssets;

class TeamFacade {
	/** @var Doctrine\Orm\EntityManager */
	private $em;

	public function __construct(\Doctrine\ORM\EntityManager $em){	
		$this->em = $em;
	}	
	
	/**
	 * Return question for role widget
	 * @param unknown_type $project_id
	 * @throws \Exception
	 */
	public function findAllProjectRoleWidgetQuestions($project_id){
		$project = $this->em->getRepository ('\App\Entity\Project')->findOneById($project_id);
		if(!$project){
			throw new \Exception("Can't find this project.");
		}
		
		$stmt = 'SELECT u FROM App\Entity\ProjectRoleWidgetQuestion u WHERE u.project = ?1';
		$stmt .= 'ORDER BY u.id ASC';
		
		$query = $this->em->createQuery($stmt);
		$query->setParameter(1, $project_id);
		
		return $query->getResult();	
	}
	
	
	/**
	 * Return all project widget question
	 * @param unknown_type $user_id
	 * @param unknown_type $project_id
	 * @param unknown_type $id
	 * @throws \Exception
	 * @return object
	 */
	public function findOneProjectWidgetQuestion($user_id,$project_id,$id){
	
		$user = $this->em->getRepository ('\App\Entity\User')->findOneById ( $user_id );
		if(!$user){
			throw new \Exception("Member doesn't exists");
		}
	
		$project = $this->em->getRepository ('\App\Entity\Project')->findOneById ($project_id);
		if(!$project){
			throw new \Exception("Can't find this project.");
		}
	
		$q = $this->em->getRepository ('\App\Entity\ProjectRoleWidgetQuestion')->findOneBy(array("id" => $id));
		if($q){
			return $q;
		}else {
			throw new \Exception("This question doesn't exists");
		}
	
	}
	
	/**
	 * Delete Project widget question
	 * @param unknown_type $user_id
	 * @param unknown_type $project_id
	 * @param unknown_type $id
	 * @throws \Exception
	 */
	public function deleteProjectRoleWidgetQuestion($user_id,$project_id,$id){
		$question= $this->findOneProjectWidgetQuestion($user_id, $project_id, $id);
		if($question){
			$this->em->remove($question);
			$this->em->flush();
		}
		else {
			throw new \Exception("This question doesn't exists");
		}
		//TODO delete all answers for this question
	}
	
	/**
	 * Update project widget question
	 * @param unknown_type $user_id
	 * @param unknown_type $project_id
	 * @param unknown_type $update_id
	 * @param unknown_type $data
	 */
	public function updateProjectWidgetQuestion($user_id,$project_id,$update_id,$data = array()){
		if(!isset($data['question']) || (strlen(trim($data['question'])) < 1) ) { throw new \Exception("Question can't be empty."); }

		$q =$this->findOneProjectWidgetQuestion($user_id,$project_id,$update_id);
		if($q){
		$q->setQuestion($data['question']);
		$this->em->flush();
		} else {
			throw new \Exception("This question can't be updated");
		}
	}
	
	/**
	 * Create new question for the project role widget
	 * @param unknown_type $project_id
	 * @param unknown_type $data
	 * @throws \Exception
	 */
	public function createProjectWidgetQuestion($user_id,$project_id,$data){
		$user = $this->em->getRepository ('\App\Entity\User')->findOneById ( $user_id );
		if(!$user){
			throw new \Exception("Member doesn't exists");
		}

		$project = $this->em->getRepository ('\App\Entity\Project')->findOneById ($project_id);
		if(!$project){
			throw new \Exception("Can't find this project.");
		}

		if(count($this->findAllProjectRoleWidgetQuestions($project_id)) < 5){
			// validate data
			if(!isset($data['question']) || (strlen(trim($data['question'])) < 1) ) { throw new \Exception("Question can't be empty."); }

			$newQuestion = new \App\Entity\ProjectRoleWidgetQuestion($data['question']);
			$newQuestion->setProject($project);
			$this->em->persist($newQuestion);
			$this->em->flush();
		
		}else{
			throw new \Exception("Project can have maximum 5 questions.");
		}
	}
	
	/**
	 * Enable or disable project widget
	 * @param unknown_type $user_id
	 * @param unknown_type $project_id
	 * @param unknown_type $data
	 */
	public function updateProjectRoleWidgetVisibility($user_id,$project_id,$data = array()){
		$user = $this->em->getRepository ('\App\Entity\User')->findOneById ( $user_id );
		if(!$user){
			throw new \Exception("Member doesn't exists");
		}
		
		$project = $this->em->getRepository ('\App\Entity\Project')->findOneById($project_id);
		if(!$project){
			throw new \Exception("Can't find this project.");
		}
		
		if($data['role_widget_disable'] == 1){
			$project->setDisableRoleWidget(true);
		}else {
			$project->setDisableRoleWidget(false);	
		}
		
		$this->em->flush();
		
	}
	
	/*
	 * Return the first layer of commments
	*/
	public function findUpdatesForProjectPaginator($project_id){
		$project = $this->em->getRepository ('\App\Entity\Project')->findOneById($project_id);
		if(!$project){
			throw new \Exception("Can't find this project.");
		}
	
		$stmt = 'SELECT u FROM App\Entity\ProjectUpdate u WHERE u.project = ?1';
		$stmt .= 'ORDER BY u.created DESC  ';
	
		$query = $this->em->createQuery($stmt);
		$query->setParameter(1, $project_id);
			
		$paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($query);
	
		$iterator = $paginator->getIterator();
	
		$adapter = new \Zend_Paginator_Adapter_Iterator($iterator);
		return new \Zend_Paginator($adapter);
	}
	

	
	

	/**
	 * Get data in Paginator
	 * @param unknown_type $user_id
	 * @param unknown_type $project_id
	 * @throws \Exception
	 */
	public function findProjectUpdates($user_id,$project_id){
		// checking errors
		$user = $this->em->getRepository ('\App\Entity\User')->findOneById ( $user_id );
		if(!$user){
			throw new \Exception("Member doesn't exists");
		}
		
		$project = $this->em->getRepository ('\App\Entity\Project')->findOneById ($project_id);
		if(!$project){
			throw new \Exception("Can't find this project.");
		}		
	
 		$stmt = 'SELECT a FROM App\Entity\ProjectUpdate a WHERE a.project = ?1';
 		$stmt .= 'ORDER BY a.id ASC';
		
 		$query = $this->em->createQuery($stmt);
 		$query->setParameter(1, $project_id);

 		$paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($query);
 		$iterator = $paginator->getIterator();
 		
 		$adapter = new \Zend_Paginator_Adapter_Iterator($iterator);
 		return new \Zend_Paginator($adapter);
 		
	}
	
	/**
	 * Update project survey
	 * @param unknown_type $user_id
	 * @param unknown_type $project_id
	 * @param unknown_type $data
	 */
	public function createProjectUpdate($user_id,$project_id,$data = array()){
		// checking errors
		$user = $this->em->getRepository ('\App\Entity\User')->findOneById ( $user_id );
		if(!$user){
			throw new \Exception("Member doesn't exists");
		}
		
		$project = $this->em->getRepository ('\App\Entity\Project')->findOneById ($project_id);
		if(!$project){
			throw new \Exception("Can't find this project.");
		}
		
		$newUpdate = new \App\Entity\ProjectUpdate($project, $data['title'], $data['content']);
		$this->em->persist($newUpdate);
		$this->em->flush();
	}
	
	


}

?>