<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__) . '/../../../../../config.php';

class SiteURLTest extends PHPUnit_Framework_TestCase {

	/**
	 * @group site_url
	 */
	function testSlashUnmodified() {
		$result = site_url('/');
		$expResult = '/';
		$this->assertEquals($expResult, $result);
	}

	/**
	 * @group site_url
	 */
	function testEmptyStringReturnsSlash() {
		$result = site_url('');
		$expResult = '/';
		$this->assertEquals($expResult, $result);
	}

	/**
	 * @group site_url
	 */
	function testSlashFooUnmodified() {
		$result = site_url('/foo');
		$expResult = '/foo';
		$this->assertEquals($expResult, $result);
	}

	/**
	 * @group site_url
	 */
	function testFooNeedsSlash() {
		$result = site_url('foo');
		$expResult = '/foo';
		$this->assertEquals($expResult, $result);
	}

	/**
	 * @group site_url
	 */
	function testHTTPReturnsUnmodified() {
		$result = site_url('http://example.com');
		$expResult = 'http://example.com';
		$this->assertEquals($expResult, $result);
	}

	/**
	 * @group site_url
	 */
	function testHTTPSReturnsUnmodified() {
		$result = site_url('https://example.com');
		$expResult = 'https://example.com';
		$this->assertEquals($expResult, $result);
	}

	/**
	 * @group site_url
	 */
	function testBogusProtocolSpec() {
		$result = site_url('nraog://foo');
		$expResult = 'nraog://foo';
		$this->assertEquals($expResult, $result);
	}

	/**
	 * @group site_url
	 */
	function testBrokenProtocolSpecBroken() {
		$result = site_url('http:/ /foo');
		$expResult = '/http:/ /foo';
		$this->assertEquals($expResult, $result);
	}

	/**
	 * @group site_url
	 */
	function testJavascriptReturnsUnmodified() {
		$result = site_url("javascript:alert('foo')");
		$expResult = "javascript:alert('foo')";
		$this->assertEquals($expResult, $result);
	}

	/**
	 * @group site_url
	 */
	function testHashReturnsUnmodified() {
		$result = site_url('#');
		$expResult = '#';
		$this->assertEquals($expResult, $result);
	}

} // SiteURLTest
