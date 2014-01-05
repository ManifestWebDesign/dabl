<?php

class SiteURLTest extends PHPUnit_Framework_TestCase {

	/**
	 * @group site_url
	 */
	function testSlashUnmodified() {
		$this->assertEquals(BASE_URL, site_url('/'));
	}

	/**
	 * @group site_url
	 */
	function testEmptyStringReturnsSlash() {
		$this->assertEquals(BASE_URL, site_url(''));
	}

	/**
	 * @group site_url
	 */
	function testSlashFooUnmodified() {
		$this->assertEquals(BASE_URL . 'foo', site_url('/foo'));
	}

	/**
	 * @group site_url
	 */
	function testFooNeedsSlash() {
		$this->assertEquals(BASE_URL . 'foo', site_url('foo'));
	}

	/**
	 * @group site_url
	 */
	function testHTTPReturnsUnmodified() {
		$this->assertEquals('http://example.com', site_url('http://example.com'));
	}

	/**
	 * @group site_url
	 */
	function testHTTPSReturnsUnmodified() {
		$this->assertEquals('https://example.com', site_url('https://example.com'));
	}

	/**
	 * @group site_url
	 */
	function testBogusProtocolSpec() {
		$this->assertEquals('nraog://foo', site_url('nraog://foo'));
	}

	/**
	 * @group site_url
	 */
	function testBrokenProtocolSpecReturnsUnmodified() {
		$this->assertEquals('http:/ /foo', site_url('http:/ /foo'));
	}

	/**
	 * @group site_url
	 */
	function testJavascriptReturnsUnmodified() {
		$this->assertEquals("javascript:alert('foo')", site_url("javascript:alert('foo')"));
	}

	/**
	 * @group site_url
	 */
	function testHashReturnsUnmodified() {
		$this->assertEquals('#', site_url('#'));
	}

	/**
	 * @group site_url
	 */
	function testDoubleLeadingSlashReturnsUnmodified() {
		$this->assertEquals('//foo.com/bar.html', site_url('//foo.com/bar.html'));
		$this->assertNotEquals('dir//bar.html', site_url('dir//bar.html'));
	}

}