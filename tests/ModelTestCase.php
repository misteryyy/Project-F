<?php

/**
 * Boost Software License 1.0 (BSL1.0)
 * 
 * Permission is hereby granted, free of charge, to any person or organization
 * obtaining a copy of the software and accompanying documentation covered by
 * this license (the "Software") to use, reproduce, display, distribute,
 * execute, and transmit the Software, and to prepare derivative works of the
 * Software, and to permit third-parties to whom the Software is furnished to
 * do so, all subject to the following:
 *
 * The copyright notices in the Software and this entire statement, including
 * the above license grant, this restriction and the following disclaimer, must
 * be included in all copies of the Software, in whole or in part, and all
 * derivative works of the Software, unless such copies or derivative works are
 * solely in the form of machine-executable object code generated by a source
 * language processor.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE, TITLE AND NON-INFRINGEMENT. IN NO EVENT
 * SHALL THE COPYRIGHT HOLDERS OR ANYONE DISTRIBUTING THE SOFTWARE BE LIABLE
 * FOR ANY DAMAGES OR OTHER LIABILITY, WHETHER IN CONTRACT, TORT OR OTHERWISE,
 * ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 */

/**
 * Description of ModelTestCase
 *
 * @author jon
 */
require_once 'PHPUnit/Framework/TestCase.php';
class ModelTestCase extends \PHPUnit_Framework_TestCase {

    /**
     *
     * @var \Bisna\Application\Container\DoctrineContainer
     */
    protected $doctrineContainer;
 

    public function setUp()
    {
    	global $application;
    	$application->bootstrap();
    	$this->doctrineContainer = Zend_Registry::get('doctrine');  
    	
    	$em = $this->doctrineContainer->getEntityManager();
    	$tool = new \Doctrine\ORM\Tools\SchemaTool($em);
    	$tool->dropDatabase();
    	$tool->createSchema($em->getMetadataFactory()->getAllMetadata());
    
    	parent::setUp();
    }
    
    
    public function tearDown()
    {
    	$this->doctrineContainer->getConnection()->close();
    	$em = $this->doctrineContainer->getEntityManager();
    	$tool = new \Doctrine\ORM\Tools\SchemaTool($em);
    	$tool->dropDatabase();
    	parent::tearDown();
    }
 }
    
    



