<?php
/**
 * Bancha Project : Seamlessly integrates CakePHP with ExtJS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2012 StudioQ OG
 *
 * @package       Bancha
 * @category      tests
 * @copyright     Copyright 2011-2012 StudioQ OG
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 0.9.0
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 */

App::uses('BanchaResponseTransformer', 'Bancha.Bancha/Network');

/**
 * BanchaResponseTransformerTest
 *
 * @package       Bancha
 * @category      tests
 */
class BanchaResponseTransformerTest extends CakeTestCase {

/**
 * Tests the transform() method for multiple return records
 *
 */
	public function testTransformMultipleRecords() {
		// Response generated by CakePHP
		$cakeResponse = array(
			array(
				'Article'	=> array(
					'id'	=> 304,
					'title'	=> 'foo',
				),
			),
			array(
				'Article'	=> array(
					'id'	=> 305,
					'title'	=> 'bar',
				),
			)
		);

		$request = new CakeRequest();
		$request->addParams(array(
			'controller'	=> 'Articles',
			'action'		=> 'index',
		));

		// Response expected by Ext JS (in JSON).
		$expectedResponse = array(
			'success' => true,
			'data' => array(
				array(
					'id'	=> 304,
					'title'	=> 'foo',
				),
				array(
					'id'	=> 305,
					'title'	=> 'bar',
				),
			),
		);

		$result = BanchaResponseTransformer::transform($cakeResponse, $request);
		$this->assertTrue($result['success'], 'Expected result to have a sucess property with value true, instead got '.print_r($result,true));
		$this->assertEquals($expectedResponse, $result);
	}

/**
 * Tests the transform() method for a single returned record
 *
 */
	public function testTransformSingleRecord() {
		// Response generated by CakePHP.
		$cakeResponse = array(
			'Article'	=> array(
				'id'	=> 304,
				'title'	=> 'foo',
			),
		);

		$request = new CakeRequest();
		$request->addParams(array(
			'controller'	=> 'Articles',
			'action'			=> 'view'
		));

		// Response expected by Ext JS (in JSON).
		$expectedResponse = array(
			'success' => true,
			'data' => array(
				'id'	=> 304,
				'title'	=> 'foo',
			),
		);

		$result = BanchaResponseTransformer::transform($cakeResponse, $request);
		$this->assertTrue($result['success'], 'Expected result to have a sucess property with value true, instead got '.print_r($result,true));
		$this->assertEquals($expectedResponse, $result);
	}

	
/**
 * Bancha also understand simple true/false values
 *
 * @dataProvider getTrueFalse
 */
	public function testTransformPrimitives($primitiveResult) {
		// Response generated by CakePHP.
		$cakeResponse = $primitiveResult;

		$request = new CakeRequest();
		$request->addParams(array(
			'controller'	=> 'Users',
			'action'		=> 'delete'
		));

		// Response expected by Ext JS (in JSON).
		$expectedResponse = array(
			'success' => $primitiveResult,
		);

		$result = BanchaResponseTransformer::transform($cakeResponse, $request);
		$this->assertTrue(isset($result['success']), 'Expected result to have a sucess property, instead got '.print_r($result,true));
		$this->assertEquals($expectedResponse, $result);
	}
	// data provider
	public function getTrueFalse() {
		return array(array(true),array(false));
	}
	
/**
 * Bancha understands cake responses with pagination data
 * @param $paginatedRecords cake response to transform
 * @param $expectedResponse Response expected by Ext JS (in JSON).
 *
 * @dataProvider getCakeRecords
 */
	public function testTransformPaginated($paginatedRecords, $expectedResponse) {
		$request = new CakeRequest();
		$request->addParams(array(
			'controller'	=> 'Articles',
			'action'		=> 'delete'
		));

		$result = BanchaResponseTransformer::transform($paginatedRecords, $request);
		$this->assertTrue($result['success'], 'Expected result to have a sucess property with value true, instead got '.print_r($result,true));
		$this->assertEquals($expectedResponse, $result);
	}
	
	// data provider
	public function getCakeRecords() {
		return array(
			array( array('count'=>0,'records'=>array()),  					array('success'=>true,'total'=>0,'data'=>array())   ),
			array( array('count'=>9,'records'=>array(
				array('Article'=>array('id'=>5,'name'=>'whatever')),
				array('Article'=>array('id'=>6,'name'=>'whatever2')))),     array('success'=>true,'total'=>9,'data'=>array(
																						array('id'=>5,'name'=>'whatever'),
																						array('id'=>6,'name'=>'whatever2')))    ),
		);
	}

/**
 * Unrecognizable structures which include a success property
 * are just passed through, otherwise only the wrapper is added
 * @param $cakeResponse cake response to transform
 * @param $expectedResponse the expected sencha response
 * 
 * @dataProvider getArbitraryData
 */
	public function testTransformArbitraryData($cakeResponse, $expectedResponse) {
		$request = new CakeRequest();
		$request->addParams(array(
			'controller'	=> 'Articles',
			'action'		=> 'delete'
		));

		$result = BanchaResponseTransformer::transform($cakeResponse, $request);
		$this->assertTrue(isset($result['success']), 'Expected result to have a sucess property, instead got '.print_r($result,true));
		$this->assertEquals($expectedResponse, $result);
	}

	// data provider
	public function getArbitraryData() {
		return array(
			array( array('success'=>true,'msg'=>'lala'), array('success'=>true,'msg'=>'lala') ), // there is a success, nothing to change
			array( array('success'=>true), array('success'=>true) ), // already in ext(-like) structure
			array( array('success'=>'true'), array('success'=>true) ), // already in ext(-like) structure, only convert property into a boolean
			array( array('success'=>'false'), array('success'=>false) ), // already in ext(-like) structure, only convert property into a boolean
			array( array('success'=>'true','message'=>'lala'), array('success'=>true,'message'=>'lala') ), // already in ext(-like) structure
			// primitive responses are the success value
			array( true, array('success'=>true) ),
			array( false, array('success'=>false) ),
			array( 'lala', array('success'=>true,'data'=>'lala') ),
			array( -1, array('success'=>true,'data'=>-1) ),
			array( 0, array('success'=>true,'data'=>0) ),
			array( 1, array('success'=>true,'data'=>1) ),
			// arbitary data is wrapped into the data property
			array( array('lala','lolo'), array('success'=>true,'data'=>array('lala','lolo')) ),
		);
	}
}
