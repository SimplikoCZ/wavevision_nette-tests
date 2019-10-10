<?php declare(strict_types = 1);

namespace Wavevision\NetteTests\Runners;

use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\Forms\Container;
use Nette\Forms\IControl;

class Forms
{

	use InjectPresenters;

	public function setup(SubmitFormRequest $submitFormRequest): SubmitFormRequest
	{
		$submitFormRequest->setSignal($submitFormRequest->getFormName() . '-submit');
		$this->presenters->setup($submitFormRequest);
		return $submitFormRequest;
	}

	public function submit(SubmitFormRequest $submitFormRequest): SubmitFormResponse
	{
		$presenterResponse = $this->presenters->run($submitFormRequest);
		return new SubmitFormResponse(
			$submitFormRequest,
			$presenterResponse,
			$this->findForm(
				$presenterResponse->getPresenterRequest()->getPresenter(),
				$submitFormRequest->getFormName()
			)
		);
	}

	/**
	 * @return array<mixed>
	 */
	public function formatFormErrors(Container $container): array
	{
		$values = [];
		foreach ($container->getComponents() as $component) {
			$name = $component->getName();
			if ($component instanceof Container) {
				$items = $this->formatFormErrors($component);
				if (count($items) > 0) {
					$values[$name] = $items;
				}
			} else {
				if ($component instanceof IControl && $component->getErrors()) {
					$values[$name] = $component->getErrors();
				}
			}
		}
		return $values;
	}

	private function findForm(Presenter $presenter, string $formName): Form
	{
		$nameParts = explode('-', $formName);
		$current = $presenter;
		foreach (array_values($nameParts) as $name) {
			$current = $current[$name];
		}
		return $current;
	}

}
