<?php

namespace SMW\Tests\MediaWiki\Specials;

use MediaWiki\MediaWikiServices;
use SMW\MediaWiki\Specials\SpecialURIResolver;
use SMW\Tests\PHPUnitCompat;
use SMW\Tests\TestEnvironment;
use Wikimedia\TestingAccessWrapper;

/**
 * @covers \SMW\MediaWiki\Specials\SpecialURIResolver
 * @group semantic-mediawiki
 *
 * @license GPL-2.0-or-later
 * @since 3.0
 *
 * @author mwjames
 */
class SpecialURIResolverTest extends \PHPUnit\Framework\TestCase {

	use PHPUnitCompat;

	private $testEnvironment;
	private $stringValidator;

	protected function setUp(): void {
		parent::setUp();

		$this->testEnvironment = new TestEnvironment();
	}

	protected function tearDown(): void {
		$this->testEnvironment->tearDown();
		parent::tearDown();
	}

	public function testExecuteOnEmptyContext() {
		$instance = new SpecialURIResolver();

		$instance->getContext()->setTitle(
			MediaWikiServices::getInstance()->getTitleFactory()->newFromText( 'SpecialURIResolver' )
		);

		$instance->execute( '' );

		$this->assertContains(
			'https://www.w3.org/2001/tag/issues.html#httpRange-14',
			$instance->getOutput()->getHTML()
		);
	}

	private function serverHost(): string {
		$urlUtils = MediaWikiServices::getInstance()->getUrlUtils();

		return $urlUtils->parse( (string)$urlUtils->expand( '/', PROTO_CURRENT ) )['host'] ?? '';
	}

	public function testIsLocalRedirectTargetAllowsSameHost(): void {
		$instance = TestingAccessWrapper::newFromObject( new SpecialURIResolver() );

		$this->assertTrue(
			$instance->isLocalRedirectTarget( 'http://' . $this->serverHost() . '/index.php/Foo' )
		);
	}

	public function testIsLocalRedirectTargetRejectsUserInfo(): void {
		$instance = TestingAccessWrapper::newFromObject( new SpecialURIResolver() );

		$this->assertFalse(
			$instance->isLocalRedirectTarget( 'https://user:pass@' . $this->serverHost() . '/Foo' )
		);
	}

	public function testIsLocalRedirectTargetRejectsDifferentHost(): void {
		$instance = TestingAccessWrapper::newFromObject( new SpecialURIResolver() );

		$this->assertFalse(
			$instance->isLocalRedirectTarget( 'https://evil.example/index.php/Foo' )
		);
	}

	public function testIsLocalRedirectTargetRejectsProtocolRelativeOffHost(): void {
		$instance = TestingAccessWrapper::newFromObject( new SpecialURIResolver() );

		$this->assertFalse(
			$instance->isLocalRedirectTarget( '//evil.example/index.php/Foo' )
		);
	}

	public function testIsLocalRedirectTargetRejectsUnparsableUrl(): void {
		$instance = TestingAccessWrapper::newFromObject( new SpecialURIResolver() );

		$this->assertFalse(
			$instance->isLocalRedirectTarget( 'http://' )
		);
	}

	public function testIsLocalRedirectTargetAllowsMixedCaseHost(): void {
		$instance = TestingAccessWrapper::newFromObject( new SpecialURIResolver() );

		$this->assertTrue(
			$instance->isLocalRedirectTarget( 'http://' . strtoupper( $this->serverHost() ) . '/x' )
		);
	}

}
