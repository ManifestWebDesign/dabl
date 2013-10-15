<?php

/**
 * Test class for StringFormat.
 */
class StringFormatTest extends PHPUnit_Framework_TestCase {

	public function testClassName() {
		$this->assertEquals('MyClassName', StringFormat::className('myClassName'));
		$this->assertEquals('MyClassName', StringFormat::className('my-class-name'));
		$this->assertEquals('MyClassName', StringFormat::className('my Class name'));
		$this->assertEquals('MyClassName', StringFormat::className('my %^&* Class name'));
		$this->assertEquals('MyClassName', StringFormat::className('my____Class name'));
		$this->assertEquals('MyClassName', StringFormat::className('my/Class name'));
	}

	public function testClassMethod() {
		$this->assertEquals('myClassMethod', StringFormat::classMethod('myClassMethod'));
		$this->assertEquals('myClassMethod', StringFormat::classMethod('my-class-Method'));
		$this->assertEquals('myClassMethod', StringFormat::classMethod('my Class Method'));
		$this->assertEquals('myClassMethod', StringFormat::classMethod('my %^&* Class Method'));
		$this->assertEquals('myClassMethod', StringFormat::classMethod('my____Class Method'));
		$this->assertEquals('myClassMethod', StringFormat::classMethod('my/Class Method'));
	}

	public function testClassProperty() {
		$this->assertEquals('myClassProperty', StringFormat::classProperty('myClassProperty'));
		$this->assertEquals('myClassProperty', StringFormat::classProperty('my-class-Property'));
		$this->assertEquals('myClassProperty', StringFormat::classProperty('my Class Property'));
		$this->assertEquals('myClassProperty', StringFormat::classProperty('my %^&* Class Property'));
		$this->assertEquals('myClassProperty', StringFormat::classProperty('my____Class Property'));
		$this->assertEquals('myClassProperty', StringFormat::classProperty('my/Class Property'));
	}

	public function testUrl() {
		$this->assertEquals('my-url', StringFormat::URL('myURL'));
		$this->assertEquals('my-url', StringFormat::URL('my--URL'));
		$this->assertEquals('my-url', StringFormat::URL('my  URL'));
		$this->assertEquals('my-url', StringFormat::URL('my %^&*  URL'));
		$this->assertEquals('my-url', StringFormat::URL('my____ URL--'));
		$this->assertEquals('my-url', StringFormat::URL('my/ \URL'));
	}

	public function testPluralURL() {
		$this->assertEquals('my-urls', StringFormat::pluralURL('myURL'));
		$this->assertEquals('my-urls', StringFormat::pluralURL('my--URL'));
		$this->assertEquals('my-urls', StringFormat::pluralURL('my  URL'));
		$this->assertEquals('my-urls', StringFormat::pluralURL('my %^&*  URL'));
		$this->assertEquals('my-urls', StringFormat::pluralURL('my____ URL--'));
		$this->assertEquals('my-urls', StringFormat::pluralURL('my/ \URL'));
	}

	public function testVariable() {
		$this->assertEquals('my_var', StringFormat::variable('myVar'));
		$this->assertEquals('my_var', StringFormat::variable('my--var'));
		$this->assertEquals('my_var', StringFormat::variable('my  var'));
		$this->assertEquals('my_var', StringFormat::variable('my %^&*  var'));
		$this->assertEquals('my_var', StringFormat::variable('my____ var--'));
		$this->assertEquals('my_var', StringFormat::variable('my/ \var'));
	}

	public function testConstant() {
		$this->assertEquals('MY_CONSTANT', StringFormat::constant('myConstant'));
		$this->assertEquals('MY_CONSTANT', StringFormat::constant('my--constant'));
		$this->assertEquals('MY_CONSTANT', StringFormat::constant('my  constant'));
		$this->assertEquals('MY_CONSTANT', StringFormat::constant('my %^&*  constant'));
		$this->assertEquals('MY_CONSTANT', StringFormat::constant('my____ constant--'));
		$this->assertEquals('MY_CONSTANT', StringFormat::constant('my/ \constant'));
	}

	public function testPluralVariable() {
		$this->assertEquals('my_vars', StringFormat::pluralVariable('myVar'));
		$this->assertEquals('my_vars', StringFormat::pluralVariable('my--var'));
		$this->assertEquals('my_vars', StringFormat::pluralVariable('my  var'));
		$this->assertEquals('my_vars', StringFormat::pluralVariable('my %^&*  var'));
		$this->assertEquals('my_vars', StringFormat::pluralVariable('my____ var--'));
		$this->assertEquals('my_vars', StringFormat::pluralVariable('my/ \var'));
	}

	public function testTitleCase() {
		$this->assertEquals('TitleCase', StringFormat::titleCase('title case'));
		$this->assertEquals('TitleCase', StringFormat::titleCase('Title_case'));
		$this->assertEquals('TitleCase', StringFormat::titleCase('TitleCase'));
		$this->assertEquals('Title-Case', StringFormat::titleCase('Title\case', '-'));
		$this->assertEquals('Title_Case', StringFormat::titleCase('TitleCase', '_'));
		$this->assertEquals('Tt_Case', StringFormat::titleCase('TTCase', '_'));
		$this->assertEquals('PersonId', StringFormat::titleCase('PersonID'));
	}

	public function testGetWords() {
		$this->assertEquals(array('test', 'case'), StringFormat::getWords('test case'));
		$this->assertEquals(array('test', 'Case'), StringFormat::getWords('testCase'));
		$this->assertEquals(array('test', 'case'), StringFormat::getWords('test_case'));
		$this->assertEquals(array('test', 'case'), StringFormat::getWords('test-case'));
		$this->assertEquals(array('my', 'ID'), StringFormat::getWords('myID'));
		$this->assertEquals(array('RU', '4', 'real'), StringFormat::getWords('RU4real'));
		$this->assertEquals(array('5', '14', '1985'), StringFormat::getWords("5'14'1985"));
		$this->assertEquals(array('who', 'me', 'yes', 'you', 'couldnt', 'be'), StringFormat::getWords(" Who me?  Yes you! couldn't be.", true));
	}

	public function testPlural() {
		$this->assertEquals('test-cases', StringFormat::plural('test-case'));
		$this->assertEquals('x-men', StringFormat::plural('x-man'));
		$this->assertEquals('test-quizzes', StringFormat::plural('test-quiz'));
		$this->assertEquals('test-oxen', StringFormat::plural('test-ox'));
		$this->assertEquals('test-mice', StringFormat::plural('test-mouse'));
		$this->assertEquals('test-vertices', StringFormat::plural('test-vertex'));
		$this->assertEquals('test-messes', StringFormat::plural('test-mess'));
		$this->assertEquals('test-maxes', StringFormat::plural('test-max'));
		$this->assertEquals('test-marties', StringFormat::plural('test-marty'));
		$this->assertEquals('test hives', StringFormat::plural('test hive'));
		$this->assertEquals('test halves', StringFormat::plural('test half'));
		$this->assertEquals('test calves', StringFormat::plural('test calf'));
		$this->assertEquals('test crises', StringFormat::plural('test crisis'));
		$this->assertEquals('test titania', StringFormat::plural('test titanium'));
		$this->assertEquals('test tomotatos', StringFormat::plural('test tomotato'));
//			array('/(bu)s$/i', "$1ses"),
//			array('/(alias|status|campus)$/i', "$1es"),
//			array('/(octop|cact|vir)us$/i', "$1i"),
//			array('/(ax|test)is$/i', "$1es"),
//			array('/^(m|wom)an$/i', "$1en"),
//			array('/(child)$/i', "$1ren"),
//			array('/(p)erson$/i', "$1eople"),
//			array('/s$/i', "s"),
//			array('/$/', "s")
	}

	/**
	 * @todo Implement testRemoveAccents().
	 */
	public function testRemoveAccents() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testClean().
	 */
	public function testClean() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

}