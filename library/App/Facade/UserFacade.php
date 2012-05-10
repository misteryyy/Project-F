<?php
namespace App\Facade;

use Doctrine\DBAL\Schema\Visitor\RemoveNamespacedAssets;

class UserFacade {
	
	/** @var Doctrine\Orm\EntityManager */
	private $em;
	
	public function __construct(\Doctrine\ORM\EntityManager $em){
		
		$this->em = $em;
	}
	
	/**
	 * Return all users 
	 */
	public function findAllUsers(){
		$users = $this->em->getRepository ('\App\Entity\User')->findThemAll();
		return $users;
		
	}
		
	/*
	 * Creates new account
	 */
	public function createAccount($data){

		$user = new \App\Entity\User();
		$user->setEmail($data['email']);
		$user->setPassword($data['password']);
		$user->setName($data['name']);
		$user->setConfirmed(1); // confirmed // we can find the use for this latter
	
		// setting user info
		$userInfo = new \App\Entity\UserInfo();
		$user->setUserInfo($userInfo);
		
		$this->em->persist($user);
		$this->em->flush();

		// TODO SENDING EMAIL
		// $mailer = new \App\Mailer\HtmlMailer();
		// $mailer->setSubject("Welcome to FLO~ Platform")
		// ->addTo($data['email'])
		// ->setViewParam('name',"Josef Kortan")
		// ->sendHtmlTemplate("welcome.phtml");
		
		
	}
	
	public function makeTagArray($str){
		$str = trim($str);
		$array = explode(",",$str);
		$array = trimArray($array);
		return $array;
	}
	
	// TODO cant be , after tag list
	
	/**
	 * 
	 * @param unknown_type $id
	 * @param unknown_type $data
	 */
	public function updateSkills($id,$data = array()){
		$user = $this->em->getRepository ('\App\Entity\User')->findOneById ( $id );	
		if($user){
			
			$arrayRoles = array(array("name" => \App\Entity\UserRole::MEMBER_ROLE_STARTER, ),
					array("name" => \App\Entity\UserRole::MEMBER_ROLE_LEADER),
					array("name" => \App\Entity\UserRole::MEMBER_ROLE_BUILDER),
					array("name" => \App\Entity\UserRole::MEMBER_ROLE_GROWER),
					array("name" => \App\Entity\UserRole::MEMBER_ROLE_ADVISER)
			);
	
			foreach($arrayRoles as $role){
				//if specific role is set, add it to the user
				if($data ["role_".$role['name']] == "1" ){                   
					// creating one of the 5 specific roles
					$user->addSpecificRole($role['name']);			
					$specRoleObj = $user->getSpecificRole($role['name']);
							
					// Adding tags
					if( strlen(trim( $data["role_".$role['name']."_tags"] )) >0 ){ // if we have some tags
						$oldTags = $specRoleObj->getTagsArray(); // true for returning array
						$newTags = $this->makeTagArray($data["role_".$role['name']."_tags"]);
						debug(	$newTags);
						$tagsToAdd = array_diff($newTags,$oldTags);
						$tagsToDelete= array_diff($oldTags, $newTags);
						
						//debug("to add");
						//debug($tagsToAdd);
						//debug($tagsToDelete);
							
						
						// adding tags
						foreach($tagsToAdd as $tagAdd){
							
							   $t = $this->em->getRepository("\App\Entity\UserSpecificRoleTag")->findOneBy(array("name"=> $tagAdd));
							    if($t){
							   	$specRoleObj->addTag($t);
								}else{
									// add tag
									$newTag = new \App\Entity\UserSpecificRoleTag();
									$newTag->setName($tagAdd);
									$specRoleObj->addTag($newTag);	
							   	}
						 }
						
						foreach($tagsToDelete as $delTag){
							
							// get tag
							$tagDelObj = $specRoleObj->getTag($delTag);
							if($tagDelObj){
								$specRoleObj->removeTag($tagDelObj);
								// if the tag doesn't have any follower
								if($tagDelObj->getCountOfSpecRolesUsingThisTag() == 0){
									$this->em->remove($tagDelObj);
								}
								
							}		
						}
						
						$this->em->flush();
						
					} else {
						
						// delete all tags for the role
						if($specRoleObj){
						
							// remove tags
							$allTags = $specRoleObj->getTags();
							foreach ($allTags as $removeTag){
								$specRoleObj->removeTag($removeTag);
								if($removeTag->getCountOfSpecRolesUsingThisTag() == 0){
									$this->em->remove($removeTag);
								}
							}
						}
						
						
					}
				
				}else {
					
					$roleObj = $user->getSpecificRole($role['name']);
					if($roleObj){
						
						// remove tags
						$allTags = $roleObj->getTags();	
						foreach ($allTags as $removeTag){
							$roleObj->removeTag($removeTag);
							if($removeTag->getCountOfSpecRolesUsingThisTag() == 0){
									$this->em->remove($removeTag);
							}	
						}

						// remove role
						$user->deleteSpecificRole($roleObj);
						$this->em->remove($roleObj);	
					}			
				}
			
			
			}
			
			$this->em->flush();
		
		} else {
			throw new \Exception("Can't find this user.");
		}
	
	}
	
	/**
	 * Update information about user
	 * @param unknown_type $id
	 * @param unknown_type $data
	 */
	public function updateInfo($id,$data = array()){
		
		$user = $this->em->getRepository ('\App\Entity\User')->findOneById ( $id );
			
		if($user){
			//$user = new \App\Entity\User();
			// update basic information in user entity	
			$user->setDescription($data['description']);
			$user->setName($data['name']);
			$user->setCountry($data['country']);
			$user->setDateOfBirthVisibility($data['dateOfBirthVisibility']);
			$user->setEmailVisibility($data['emailVisibility']);
				
			// TODO Parse The date
			$user->setDateOfBirth($data['dateOfBirth']);
				
				// UserInfo already exists just update it
				$info = $user->getUserInfo();
				$info->setSkype($data['skype']);
				$info->setPhone($data['phone']);
				$info->setIm($data['im']);
				$info->setWebsite($data['website']);	
				
				// delete all tags -> empty textfield
				if(strlen(trim($data['fieldOfInterestTag'])) <= 0){
					$userTags = $user->getUserFieldOfInterestTags();
					// Delete previsou tag from the database
					if(!empty($userTags)){
						foreach($userTags as $tag){
								//check if the tag is not the same
								$user->removeUserFieldOfInterestTag($tag);
								// noone else has this tag, delete it from database
								if($tag->getUsers()->count() == 0){
									$this->em->remove($tag); // delete entity tag
								}
						}
					}
					$this->em->flush();
				}
				
				
				// user has some tags
				if(strlen(trim($data['fieldOfInterestTag'])) > 0){
				
					$tags = explode(',', $data['fieldOfInterestTag']);
					$tags = trimArray($tags);
					
				// delete all tags before update them
						$userTags = $user->getUserFieldOfInterestTags();				
						// Delete previsou tag from the database
		  				if(!empty($userTags)){
		  				  	foreach($userTags as $tag){			

		  				  		if(!in_array($tag->getName(), $tags)){ //check if the tag is not the same
		  				  		$user->removeUserFieldOfInterestTag($tag);
		 						// noone else has this tag, delete it from database
		 					 	if($tag->getUsers()->count() == 0){
									//echo "Number of users for this tag " . $tag->getUsers()->Count();
									
		 					 		$this->em->remove($tag); // delete entity tag
								}
		 					 	}
		 				  	}	
		 				}
		 	//	$this->em->flush(); // flush because we need to update current tags, when this are deleted
 				
		 		// addTags
 					
 				foreach ($tags as $tag_string){
					$tag = $this->em->getRepository("\App\Entity\UserFieldOfInterestTag")->findOneBy(array("name"=> $tag_string));
					if($tag){
						echo $tag->getName();
						//$user->addUserTag($tag);
					}else {
						$tagObj = new \App\Entity\UserFieldOfInterestTag();
						$tagObj->setName($tag_string);
						$user->addUserFieldOfInterestTag($tagObj);
					}	
				}	
				
				$this->em->flush();
				
				}	
		
		} else {
			throwException("Can't find this user.");
		}
		
	}
	
	/**
	 * Update picture and create new resolution for picture thumnail
	 * @param unknown_type $id
	 * @param unknown_type $path
	 */
	public function updateProfilePicture($id,$path){
		$resolutions = array(200,100,50);
		$user = $this->em->getRepository ('\App\Entity\User')->findOneById ( $id );
		if($user){
	
		
		$config = new \Zend_Config(\Zend_Registry::get('config'));
		$uploadDir = $config->app->storage->profile;
		
			
		// delete previous picture
		$current =$user->getProfilePicture();
		if($current != null){
			// delete this file
			if( is_file($uploadDir.$current)){
				$ext = substr(strrchr($current, '.'), 1);
				$pre = substr($current,0,strrpos($current, '_'));
				foreach($resolutions as $resolution){	
					//debug($uploadDir.$pre.'_'.$resolution.'.'.$ext);
					unlink($uploadDir.$pre.'_'.$resolution.'.'.$ext);
				}
			}
			
		};	
			
		// Generate profile pictures
		$imageManager = new \Boilerplate_Util_ImageManager($path);
		
		$ext = substr(strrchr($path, '.'), 1);
		$pre = substr($path,0,strrpos($path, '.'));

		
		if(is_array($resolutions)){
			
			foreach($resolutions as $resolution){	
				$fName = $pre."_".$resolution.".".$ext;
				$imageManager->resizeImage($resolution, $resolution, 'crop');
				$imageManager->saveImage($fName, 100);	
			}
			
			// save the name for the file to user profile
			$absolutPath = $pre."_".$resolutions[0].".".$ext;
			$user->setProfilePicture(basename($absolutPath));
			$this->em->flush();
			
			// delete original file
			unlink($path);		
		}
		
		}
		
		else {
			
			throw new \Exception("Can't find this user.");
		}
		    
		
		
		
		
		
	}
	
	/**
	 * 
	 * @param unknown_type $id
	 */
	public function findUserSettings($id){
		$user = $this->em->getRepository ('\App\Entity\User')->findOneById ( $id );
		
		if($user){
			return $user;
		} else {
			throwException("Can't find this user.");
		}
		
	}
	
	
	

}

?>