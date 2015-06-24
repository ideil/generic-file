<?php namespace Ideil\GenericFile\Interpolator;

use ReflectionClass;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Ideil\GenericFile\Resources\File;

class InterpolatorTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var Ideil\GenericFile\Interpolator\Interpolator
	 */
	protected $interpolator;

	/**
	 *
	 */
	public function getPrivateMethod($method_name)
	{
		$reflector = new ReflectionClass($this->interpolator);

		$method = $reflector->getMethod($method_name);

		$method->setAccessible(true);

		return $method;
	}

	/**
	 *
	 */
	public function invokePrivateMethod($method_name, array $args)
	{
		return $this->getPrivateMethod($method_name)->invokeArgs($this->interpolator, $args);
	}

	/**
	 *
	 */
	public function getMockedInterpolator($mocked_methods = null)
	{

		$base_handlers = [

			'contenthash' => function ($file) {
				return 'somefilehashstring';
			},

			'ext' => function ($file) {
				return 'ext';
			}

		];

		$filters_handlers = [

			'filter1' => function ($str) {
				return $str . '-filtered1';
			},

			'filter2' => function ($str) {
				return $str . '-filtered2';
			}

		];

		return $this->getMock('Ideil\GenericFile\Interpolator\Interpolator', $mocked_methods, [
			new HandlersBase($base_handlers),
			new HandlersFilters($filters_handlers),
		]);
	}

	/**
	 *
	 */
	public function getUploadedFile()
	{
		$file = new UploadedFile(
			__DIR__.'/Fixtures/test.gif',
			'original.gif',
			null
		);

		return new File($file, 'original.gif');
	}

	/**
	 *
	 */
	public function setUp()
	{
		$this->interpolator = $this->getMockedInterpolator();
	}

	/**
	 * @dataProvider provideParseInterpolationString
	 */
	public function testParseInterpolationString($pattern, $match)
	{
		$result = $this->invokePrivateMethod('parseInterpolationString', [$pattern]);

		$this->assertEquals($result, $match);
	}

	/**
	 *
	 */
	public function provideParseInterpolationString()
	{
		return [
			[
				// pattern
				'/some/path/{base-handler1|f1|f2}/next/part/{base-handler2|f3|f4}/ending',

				// match
				[
					'{base-handler1|f1|f2}' => ['base-handler1', 'f1', 'f2'],
					'{base-handler2|f3|f4}' => ['base-handler2', 'f3', 'f4'],
				],
			],

			[
				// pattern
				'/some/path/{base-handler1|f1|f2}/next/part/{base-handler1|f1|f2}/ending',

				// match
				[
					'{base-handler1|f1|f2}' => ['base-handler1', 'f1', 'f2'],
				],
			],

			[
				// pattern
				'/some/path/{base-handler1/ending',

				// match
				[
				],
			],

			[
				// pattern
				'/some/path/',

				// match
				[
				],
			],
		];
	}

	/**
	 *
	 */
	public function testHandleDirective()
	{
		$result = $this->invokePrivateMethod('handleDirective', [
			'contenthash',
			$this->getUploadedFile(),
		]);

		$this->assertEquals($result, 'somefilehashstring');
	}

	/**
	 *
	 */
	public function testHandleSubdirectives()
	{
		$parsed_string = $this->invokePrivateMethod('parseInterpolationString',
			['/some/path/{contenthash|filter1|filter2}/ending']);

		$data = reset($parsed_string);

		$result = $this->invokePrivateMethod('handleSubdirectives', [$data, 'sometext']);

		$this->assertEquals($result, 'sometext-filtered1-filtered2');
	}

	/**
	 * @depends testParseInterpolationString
	 * @depends testHandleDirective
	 * @depends testHandleSubdirectives
	 *
	 * @dataProvider providerCorrectResolveStorePath
	 */
	public function testCorrectResolveStorePath($pattern, $match)
	{
		$interpolated = $this->interpolator->resolveStorePath($pattern, $this->getUploadedFile());

		$this->assertEquals($interpolated->getResult(), $match);
	}

	/**
	 *
	 */
	public function providerCorrectResolveStorePath()
	{
		return [

			[
				// pattern
				'/content/files/{contentHash|filter1}/{contentHash}.{ext}',

				// match
				'/content/files/somefilehashstring-filtered1/somefilehashstring.ext'
			],

			[
				// pattern
				'/content/files/{contentHash#1|filter1}/{contentHash#2}.{ext}',

				// match
				'/content/files/somefilehashstring-filtered1/somefilehashstring.ext'
			],

			[
				// pattern
				'/content/original/{contentHash}.{ext}',

				// match
				'/content/original/somefilehashstring.ext'
			],

		];
	}

	/**
	 * @depends testCorrectResolveStorePath
	 *
	 * @dataProvider providerIncorrectResolveStorePath
	 */
	public function testIncorrectResolveStorePath($pattern)
	{
		$this->setExpectedException('UnexpectedValueException');

		$this->interpolator->resolveStorePath($pattern, $this->getUploadedFile());
	}

	/**
	 *
	 */
	public function providerIncorrectResolveStorePath()
	{
		return [

			[
				// pattern
				'/content/files/{unknownHandler|filter1}/{contentHash}.{ext}',
			],

			[
				// pattern
				'/content/files/{contentHash|unknownFilterHandler}/{contentHash}.{ext}',
			],
		];
	}

	/**
	 * @dataProvider provideCorrectResolvePath
	 */
	public function testCorrectResolvePath($pattern, array $model, array $model_map, $match)
	{
		$result = $this->interpolator->resolvePath($pattern, $model, $model_map);

		$this->assertEquals($result, $match);
	}

	/**
	 *
	 */
	public function provideCorrectResolvePath()
	{
		return [
			[
				// pattern
				'/content/files/{contentHash|filter1}/{contentHash}.{ext}',

				// model
				[
					'contentHash' => 'somefilehashstring',
					'ext' => 'ext',
				],

				// model_map
				[],

				// match
				'/content/files/somefilehashstring-filtered1/somefilehashstring.ext',
			],

			[
				// pattern
				'/content/files/{contentHash|filter1}/{contentHash}.{ext}',

				// model
				[
					'hash' => 'somefilehashstring',
					'extension' => 'ext',
				],

				// model_map
				[
					'contentHash' => 'hash',
					'ext' => 'extension',
				],

				// match
				'/content/files/somefilehashstring-filtered1/somefilehashstring.ext',
			],
		];
	}
}

