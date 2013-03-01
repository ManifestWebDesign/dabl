<?php

if (!function_exists('global_pub_sub_test_callback')) {

	function global_pub_sub_test_callback() {
		PubSubTest::$callbackCalled = true;
	}

}

/**
 * Test class for PubSub.
 */
class PubSubTest extends PHPUnit_Framework_TestCase {

	static $callbackCalled = false;

	/**
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
		PubSub::unsubscribe();
	}

	/**
	 * @test
	 * @expectedException InvalidArgumentException
	 */
	public function it_should_throw_exception_when_invalid() {
		PubSub::subscribe('/add/user', 'lolnofunctionhere');
	}

	/**
	 * @test
	 */
	public function it_should_add_new_event() {
		PubSub::subscribe('/add/user', function() {

				});
		$events = PubSub::events();

		$this->assertArrayHasKey('/add/user', $events);
		$this->assertCount(1, $events['/add/user']);
	}

	/**
	 * @test
	 */
	public function it_should_call_the_event_callback() {
		$mock = $this->getMock('stdClass', array('foo'));
		$mock->expects($this->once())
				->method('foo')
				->with('foobar');

		PubSub::subscribe('/do/something', array($mock, 'foo'));
		PubSub::publish('/do/something', 'foobar');
	}

	/**
	 * @test
	 */
	public function it_should_call_multiple_event_callbacks() {
		$mock = $this->getMock('stdClass', array('foo', 'bar'));

		$mock->expects($this->once())
				->method('foo')
				->with('baz');

		$mock->expects($this->once())
				->method('bar')
				->with('baz');

		PubSub::subscribe('/a/thing', array($mock, 'foo'));
		PubSub::subscribe('/a/thing', array($mock, 'bar'));

		PubSub::publish('/a/thing', 'baz');
	}

	/**
	 * @test
	 */
	public function it_clears_events_when_unsubscribed() {
		$mock = $this->getMock('stdClass', array('foo'));
		PubSub::subscribe('/what/up', array($mock, 'foo'));

		$events = PubSub::events();
		$this->assertTrue(!empty($events['/what/up']));

		PubSub::unsubscribe('/what/up');
		$events = PubSub::events();
		$this->assertTrue(empty($events['/what/up']));
	}

	/**
	 * @test
	 */
	public function it_unsubscribes_all_get() {
		$mock = $this->getMock('stdClass', array('foo'));
		PubSub::subscribe('/it/here', array($mock, 'foo'));
		$this->assertGreaterThan(0, count(PubSub::events()));

		PubSub::unsubscribe();
		$this->assertEquals(0, count(PubSub::events()));
	}

	/**
	 * This method is called after a test is executed.
	 */
	protected function tearDown() {
		self::$callbackCalled = false;
		PubSub::unsubscribe();
	}

	static function staticCallback() {
		PubSubTest::$callbackCalled = true;
	}

	function instanceCallback() {
		PubSubTest::$callbackCalled = true;
	}

	/**
	 * @covers PubSub::add
	 * @covers PubSub::call
	 */
	public function testSupportsDifferentCallbackTypes() {
		$callbacks = array(
			function() {
				PubSubTest::$callbackCalled = true;
			},
			array('PubSubTest', 'staticCallback'),
			array($this, 'instanceCallback'),
			'global_pub_sub_test_callback'
		);

		foreach ($callbacks as $index => $callback) {
			$this->tearDown();
			PubSub::subscribe('my_event', $callback);
			PubSub::publish('my_event');
			$this->assertTrue(self::$callbackCalled, 'Callback ' . $index . ' should have been called.');
		}
	}

	/**
	 * @covers PubSub::call
	 */
	public function testCallbackCanReturnFalse() {
		$callbacks = array(
			function() {
				PubSubTest::$callbackCalled = true;
			},
			array('PubSubTest', 'staticCallback'),
			array($this, 'instanceCallback'),
			'global_pub_sub_test_callback'
		);

		foreach ($callbacks as $callback) {
			PubSub::subscribe('my_event', $callback, 100);
		}
		PubSub::subscribe('my_event', function() {
					return false;
				}, 99);
		PubSub::publish('my_event');
		$this->assertFalse(self::$callbackCalled, 'Returning false should have prevented the execution of the other events.');
	}

	/**
	 * @covers PubSub::add
	 */
	public function testGet() {
		PubSub::subscribe('my_event', 'global_pub_sub_test_callback', 100);

		// all events
		$this->assertNotEmpty(PubSub::events());

		// same event, different priority
		$this->assertEmpty(PubSub::subscriptions('my_event', 50));

		// same priority, different event
		$this->assertEmpty(PubSub::subscriptions('some_other_event', 100));

		// diffent event, no priority distinction
		$this->assertEmpty(PubSub::subscriptions('some_other_event'));

		$callbacks = PubSub::subscriptions('my_event');
		$this->assertNotEmpty($callbacks);
		$this->assertEquals('global_pub_sub_test_callback', $callbacks[0]);
	}

	/**
	 * @covers PubSub::getCallbackHash
	 */
	public function testGetCallbackHash() {
		$callbacks = array(
			function() {
				PubSubTest::$callbackCalled = true;
			},
			array('PubSubTest', 'staticCallback'),
			array($this, 'instanceCallback'),
			'global_pub_sub_test_callback'
		);

		$ids = array();

		// make sure it always returns the same thing for a given input
		foreach ($callbacks as $callback) {
			$ids[] = $id = PubSub::getCallbackHash($callback);
			$this->assertEquals($id, PubSub::getCallbackHash($callback), 'getCallbackHash should always return the same value for the same callback.');
		}
		$this->assertEquals(count($ids), count(array_unique($ids)), 'getCallbackHash should return unique values for unique callbacks.');
	}

	/**
	 * @covers PubSub::unsubscribe
	 */
	public function testRemove() {
		$callbacks = array(
			function() {
				PubSubTest::$callbackCalled = true;
			},
			array('PubSubTest', 'staticCallback'),
			array($this, 'instanceCallback'),
			'global_pub_sub_test_callback'
		);
		foreach ($callbacks as $key => $callback) {
			PubSub::subscribe('my_event', $callback);
			$this->assertEquals(1, count(PubSub::subscriptions('my_event')), '1 callback was added, so 1 callback should be in array.');
			PubSub::unsubscribe('my_event', $callback);
			$this->assertEquals(0, count(PubSub::subscriptions('my_event')), '1 callback was unsubscribed, so 0 callbacks should be in array.');
		}
	}

}