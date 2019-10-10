<?php declare(strict_types = 1);

namespace Wavevision\NetteTests\Setup;

use Nette\DI\Container;
use Nette\SmartObject;
use Nette\Utils\Strings;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

class AllowInjects
{

	use SmartObject;

	public function process(Container $container, TestCase $testCase): void
	{
		// dont judge me
		$ref = new ReflectionClass($testCase);
		$injectMethods = array_filter(
			$ref->getMethods(ReflectionMethod::IS_PUBLIC),
			function (ReflectionMethod $method) {
				return Strings::startsWith($method->getName(), 'inject');
			}
		);
		/** @var ReflectionMethod $method */
		foreach ($injectMethods as $method) {
			$parameterType = $method->getParameters()[0]->getClass();
			if ($parameterType === null) {
				continue;
			}
			$testCase->{$method->getName()}(
				$container->getByType($parameterType->getName())
			);
		}
	}
}
