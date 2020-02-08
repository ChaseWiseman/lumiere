<?php

namespace SkyVerge\Lumiere\Command;

use Codeception\Command\Init;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Up extends Init {

	const NAME = 'up';

	protected function configure() {

		$this->setDefinition(
			[
				new InputArgument('template', InputArgument::OPTIONAL, 'Init template for the setup', null ),
				new InputOption('path', null, InputOption::VALUE_REQUIRED, 'Change current directory', null),
				new InputOption('namespace', null, InputOption::VALUE_OPTIONAL, 'Namespace to add for actor classes and helpers\'', null),
			]
		);
	}

	public function execute( InputInterface $input, OutputInterface $output ) {

		if ( ! $input->getArgument( 'template' ) ) {
			$input->setArgument( 'template', 'lumiere' );
		}

		parent::execute( $input, $output );
	}

}
