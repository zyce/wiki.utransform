<?php

/**
 * @author Adam Shorland
 * @covers TitleArrayFromResult
 */
class TitleArrayFromResultTest extends MediaWikiTestCase {

	private function getMockResultWrapper( $row = null, $numRows = 1 ) {
		$resultWrapper = $this->getMockBuilder( 'ResultWrapper' )
			->disableOriginalConstructor();

		$resultWrapper = $resultWrapper->getMock();
		$resultWrapper->expects( $this->atLeastOnce() )
			->method( 'current' )
			->will( $this->returnValue( $row ) );
		$resultWrapper->expects( $this->any() )
			->method( 'numRows' )
			->will( $this->returnValue( $numRows ) );

		return $resultWrapper;
	}

	private function getRowWithTitle( $namespace = 3, $title = 'foo' ) {
		$row = new stdClass();
		$row->page_namespace = $namespace;
		$row->page_title = $title;
		return $row;
	}

	private function getTitleArrayFromResult( $resultWrapper ) {
		return new TitleArrayFromResult( $resultWrapper );
	}

	/**
	 * @covers TitleArrayFromResult::__construct
	 */
	public function testConstructionWithFalseRow() {
		$row = false;
		$resultWrapper = $this->getMockResultWrapper( $row );

		$object = $this->getTitleArrayFromResult( $resultWrapper );

		$this->assertEquals( $resultWrapper, $object->res );
		$this->assertSame( 0, $object->key );
		$this->assertEquals( $row, $object->current );
	}

	/**
	 * @covers TitleArrayFromResult::__construct
	 */
	public function testConstructionWithRow() {
		$namespace = 0;
		$title = 'foo';
		$row = $this->getRowWithTitle( $namespace, $title );
		$resultWrapper = $this->getMockResultWrapper( $row );

		$object = $this->getTitleArrayFromResult( $resultWrapper );

		$this->assertEquals( $resultWrapper, $object->res );
		$this->assertSame( 0, $object->key );
		$this->assertInstanceOf( 'Title', $object->current );
		$this->assertEquals( $namespace, $object->current->mNamespace );
		$this->assertEquals( $title, $object->current->mTextform );
	}

	public function provideNumberOfRows() {
		return array(
			array( 0 ),
			array( 1 ),
			array( 122 ),
		);
	}

	/**
	 * @dataProvider provideNumberOfRows
	 * @covers TitleArrayFromResult::count
	 */
	public function testCountWithVaryingValues( $numRows ) {
		$object = $this->getTitleArrayFromResult( $this->getMockResultWrapper( $this->getRowWithTitle(), $numRows ) );
		$this->assertEquals( $numRows, $object->count() );
	}

	/**
	 * @covers TitleArrayFromResult::current
	 */
	public function testCurrentAfterConstruction() {
		$namespace = 0;
		$title = 'foo';
		$row = $this->getRowWithTitle( $namespace, $title );
		$object = $this->getTitleArrayFromResult( $this->getMockResultWrapper( $row ) );
		$this->assertInstanceOf( 'Title', $object->current() );
		$this->assertEquals( $namespace, $object->current->mNamespace );
		$this->assertEquals( $title, $object->current->mTextform );
	}

	public function provideTestValid() {
		return array(
			array( $this->getRowWithTitle(), true ),
			array( false, false ),
		);
	}

	/**
	 * @dataProvider provideTestValid
	 * @covers TitleArrayFromResult::valid
	 */
	public function testValid( $input, $expected ) {
		$object = $this->getTitleArrayFromResult( $this->getMockResultWrapper( $input ) );
		$this->assertEquals( $expected, $object->valid() );
	}

	//@todo unit test for key()
	//@todo unit test for next()
	//@todo unit test for rewind()
}
