<?php

class Member_ProfileSettingController extends  Boilerplate_Controller_Action_Abstract
{

    public function indexAction()
    {
    	//$member = Zend_Auth::getInstance()->getIdentity();
    	//$this->view->pageTitle = $member->name . '\'s Dashboard \  ' ;
    }
    
    /**
     * 
     */
    public function memberInfoAction()
    {
    	$id = 1;
    	$error = false;
    	//$member = Zend_Auth::getInstance()->getIdentity();
    	//$this->view->pageTitle = $member->name . '\'s Dashboard \ Member settings' ;
      	 $form = new \App\Form\MemberPersonalInfoForm();
    
       if ($this->_request->isPost()) {
       	if ($form->isValid($this->_request->getPost())) {
       		$facadeUser = new \App\Facade\UserFacade($this->_em);	
       		// fetch values
       		$values = $form->getValues();
       		// storing data
       		try{
       			$facadeUser->updateInfo($id,$values);
       			$this->_helper->FlashMessenger( array('success' => "Updated successfully :D"));
       		}
       		catch (Exception $e){
       			   $this->_helper->FlashMessenger( array('error' => $e->getMessage()));  		
       		}
       	 
       	}
       	// not validated properly
       	else {
       		$this->_helper->FlashMessenger( array('error' => "Please check your input."));
       		$error = true;
       	}
       }
    	
       // leave the old values, if user already sended form
       if(!$error){
       $facadeUser = new \App\Facade\UserFacade($this->_em); 
       // fetch values
       $values = $form->getValues();	      
       // retriving data for form
        $user = $facadeUser->findUserSettings($id);
        // if its not initialize
        ($user->getDateOfBirth() != null) ?  $dateOfBirth = $user->getDateOfBirth()->format('Y/m/d') : $dateOfBirth = '';
        
       $data = array(
       		'name' => $user->getName(),
       		'email' => $user->getEmail(),
       		'emailVisibility' => $user->getEmailVisibility(),
       		'im' => $user->getUserInfo()->getIm(),
       		'country' => $user->getCountry(),
       		'dateOfBirth'=> $dateOfBirth, 
       		'dateOfBirthVisibility'=> $user->getDateOfBirthVisibility(),
       		'skype' => $user->getUserInfo()->getSkype(),
       		'website' => $user->getUserInfo()->getWebsite(),
       		'phone' => $user->getUserInfo()->getPhone(),
       		'fieldOfInterestTag' => $user->getUserFieldOfInterestTagsString()
     	);
       
      	$form->setDefaults($data);
       }
      $this->view->form = $form;
       
    
       
    }
    
    /**
     * Change profile picture
     */
    public function memberPictureAction()
    {
    	
    	$this->view->pageTitle = $this->_member['name'] . '\'s Dashboard \ Profile Picture' ;
    	$form = new \App\Form\MemberChangeProfilePicture(); 	
    	//then process your file, it's path is found by calling $upload->getFilename()
    	$this->view->form = $form;
    	// Checking the file
    	
    	if($this->_request->isPost()){	
    		
    			$adapter = new Zend_File_Transfer_Adapter_Http();
    			$config = new Zend_Config(Zend_Registry::get('config'));
    			$uploadDir = $config->app->storage->profile;
    			
    			
    			// setting upload file
    			$adapter->setDestination($uploadDir);
    			$adapter->addValidator('Size', false, 4*10*102400)
    			->addValidator('Count', false, 1)
    			->addValidator('Extension', false, 'jpg,jpeg,png')
    			->addValidator('IsImage', false);

    			$i= 1;
    			foreach ($adapter->getFileInfo() as $file => $info) {
    				
    				// check if uploaded
    				if (!$adapter->isUploaded($file)) {
    					$errorMessage = "You haven't choose the file. Try it again :D.";
    					$this->_helper->FlashMessenger($errorMessage);
    					break;
    				}
		
    				// validators are ok ?
    				if (!$adapter->isValid($file)) {
    					$errorMessage = "Please check the file: ".$info["name"] . ". \n<br />";
    					$errorMessage .= implode("\n<br\>", $adapter->getMessages());
    					$this->_helper->FlashMessenger( array('error' =>  $errorMessage));
    					break;
    				}
    			
    			// rename the file	
    			$ext = findexts($info['name']);
    			$fileName = 'profile'.sha1("s@4d".$this->_member_id);
    			
    			// resolution path
    			$path = $uploadDir.$fileName.'.'.$ext;	 
    			$web_path = $this->_users_web_folder_path.$fileName.'.'.$ext;
    			
    			
    			$adapter->addFilter('Rename', 
     								array('target' => $path,	
    										'overwrite' => true));				
    			
    			// receiving files
    			if(!$adapter->receive($file)){
     					debug($adapter->getMessages());	
    					$this->_helper->FlashMessenger( array('error' => "Can't upload image to the server."));   
    					break;
    				}	
    				
     			$i++;	
     		
    			// Add Profile Picture and process picture
    			$facadeUser = new \App\Facade\UserFacade($this->_em);
    			$facadeUser->updateProfilePicture($this->_member_id,$path); // default 3 resolution

    			$this->_helper->FlashMessenger( array('success' => "Profile picture has been changed."));
    			$this->_redirect('/member/profile-setting/member-picture');
    			
    			
    	} // end foreach through all files
	    
    	}// end if post
    
    }
 
    
    public function memberPasswordAction()
    {
    	$member = Zend_Auth::getInstance()->getIdentity();
    	$this->view->pageTitle = $member->name . '\'s Dashboard \ Change password' ;
    }
    
    
    
    /**
     * Administration of Member Skills
     */
    public function memberSkillsAction()
    {
    	
    	$this->view->pageTitle = $this->_member['name'] . '\'s Dashboard \ Member skills' ;
    	$form = new \App\Form\MemberSkill();
     	$this->view->form = $form;
 	
     	$id = 1;
     	$error = false;

     	if ($this->_request->isPost()) {
     		if ($form->isValid($this->_request->getPost())) {
     			$facadeUser = new \App\Facade\UserFacade($this->_em);
     			// fetch values
     			$values = $form->getValues();
     			// storing data
     			try{
     				$facadeUser->updateSkills($id,$values);
     				$this->_helper->FlashMessenger( array('success' => "Updated successfully :D"));
     			}
     			catch (Exception $e){
     				$this->_helper->FlashMessenger( array('error' => $e->getMessage()));
     			}
     			 
     		}
     		// not validated properly
     		else {
     			$this->view->messages = array('error', 'Please control your input!'); // extra message on top
     			$error = true;
     		}
     		 
     	}
     	 
     	// leave the old values, if user already sended form
     	if(!$error){
     		$facadeUser = new \App\Facade\UserFacade($this->_em);
     		// fetch values
     		$values = $form->getValues();
     		// retriving data for form
     		$user = $facadeUser->findUserSettings($id);
     		// filling up form with data	
     		$arrayRoles = array(array("name" => \App\Entity\UserRole::MEMBER_ROLE_STARTER, ),
     				array("name" => \App\Entity\UserRole::MEMBER_ROLE_LEADER),
     				array("name" => \App\Entity\UserRole::MEMBER_ROLE_BUILDER),
     				array("name" => \App\Entity\UserRole::MEMBER_ROLE_GROWER),
     				array("name" => \App\Entity\UserRole::MEMBER_ROLE_ADVISER)
     		);
     		

     		 $data = array();
      		 foreach($arrayRoles as $role){
        			//if specific role is set, add it to the user
      		 		$specRole = $user->getSpecificRole($role['name']);
      		 	 
         			if($specRole){
         				// getting the value
         				$data ["role_".$role['name']] = "1" ;
      					$data ["role_".$role['name']."_tags"] = $specRole->getTagsString(); 
      			}
    		} 
   			
     		$form->setDefaults($data);
     	
     	
     	}
     	
     	
    

    }
    
    
    
    
}





