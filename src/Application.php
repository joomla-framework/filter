<?php
/**
 * Part of the Joomla Framework Console Package
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Console;

use Joomla\Application\AbstractApplication;
use Joomla\Console\Exception\CommandNotFoundException;
use Joomla\Console\Input\JoomlaInput;
use Joomla\Input\Cli;
use Joomla\Registry\Registry;
use Symfony\Component\Console\Helper\DebugFormatterHelper;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\ProcessHelper;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputAwareInterface;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Base application class for a Joomla! command line application.
 *
 * Portions of this class are based heavily on the Symfony\Component\Console\Application class.
 *
 * @since  __DEPLOY_VERSION__
 */
class Application extends AbstractApplication
{
	/**
	 * Flag indicating the application should automatically exit after the command is run.
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	private $autoExit = false;

	/**
	 * The available commands.
	 *
	 * @var    CommandInterface[]
	 * @since  __DEPLOY_VERSION__
	 */
	private $commands = [];

	/**
	 * The command loader.
	 *
	 * @var    Loader\LoaderInterface
	 * @since  __DEPLOY_VERSION__
	 */
	private $commandLoader;

	/**
	 * Console input handler.
	 *
	 * @var    InputInterface
	 * @since  __DEPLOY_VERSION__
	 */
	private $consoleInput;

	/**
	 * Console output handler.
	 *
	 * @var    OutputInterface
	 * @since  __DEPLOY_VERSION__
	 */
	private $consoleOutput;

	/**
	 * The default command for the application.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	private $defaultCommand = 'list';

	/**
	 * The base application input definition.
	 *
	 * @var    InputDefinition
	 * @since  __DEPLOY_VERSION__
	 */
	private $definition;

	/**
	 * The exit code from the command.
	 *
	 * @var    integer
	 * @since  __DEPLOY_VERSION__
	 */
	private $exitCode = 0;

	/**
	 * The application helper set.
	 *
	 * @var    HelperSet
	 * @since  __DEPLOY_VERSION__
	 */
	private $helperSet;

	/**
	 * Class constructor.
	 *
	 * @param   Cli       $input   An optional argument to provide dependency injection for the application's input object.  If the argument is an
	 *                             Input object that object will become the application's input object, otherwise a default input object is created.
	 * @param   Registry  $config  An optional argument to provide dependency injection for the application's config object.  If the argument
	 *                             is a Registry object that object will become the application's config object, otherwise a default config
	 *                             object is created.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(Cli $input = null, Registry $config = null)
	{
		// Close the application if we are not executed from the command line.
		if (!defined('STDOUT') || !defined('STDIN') || !isset($_SERVER['argv']))
		{
			$this->close();
		}

		// Call the constructor as late as possible (it runs `initialise`).
		parent::__construct($input ?: new Cli, $config);
	}

	/**
	 * Add a command to the application.
	 *
	 * @param   CommandInterface  $command  The command to add
	 *
	 * @return  CommandInterface|void  The registered command or null if the command is not enabled
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function addCommand(CommandInterface $command)
	{
		if (!$command->isEnabled())
		{
			return;
		}

		$command->setApplication($this);
		$command->setHelperSet($this->helperSet);

		if (!$command->getName())
		{
			throw new \LogicException(sprintf('The command class %s does not have a name.', get_class($command)));
		}

		$this->commands[$command->getName()] = $command;

		foreach ($command->getAliases() as $alias)
		{
			$this->commands[$alias] = $command;
		}

		return $command;
	}

	/**
	 * Configures the console input and output instances for the process.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function configureIO()
	{
		$input  = $this->getConsoleInput();
		$output = $this->getConsoleOutput();

		if ($input->hasParameterOption(['--ansi'], true))
		{
			$output->setDecorated(true);
		}
		elseif ($input->hasParameterOption(['--no-ansi'], true))
		{
			$output->setDecorated(false);
		}

		if ($input->hasParameterOption(['--no-interaction', '-n'], true))
		{
			$input->setInteractive(false);
		}

		if ($input->hasParameterOption(['--quiet', '-q'], true))
		{
			$output->setVerbosity(OutputInterface::VERBOSITY_QUIET);
			$input->setInteractive(false);
		}
		else
		{
			if ($input->hasParameterOption('-vvv', true)
				|| $input->hasParameterOption('--verbose=3', true)
				|| $input->getParameterOption('--verbose', false, true) === 3
			)
			{
				$output->setVerbosity(OutputInterface::VERBOSITY_DEBUG);
			}
			elseif ($input->hasParameterOption('-vv', true)
				|| $input->hasParameterOption('--verbose=2', true)
				|| $input->getParameterOption('--verbose', false, true) === 2
			)
			{
				$output->setVerbosity(OutputInterface::VERBOSITY_VERY_VERBOSE);
			}
			elseif ($input->hasParameterOption('-v', true)
				|| $input->hasParameterOption('--verbose=1', true)
				|| $input->hasParameterOption('--verbose', true)
				|| $input->getParameterOption('--verbose', false, true)
			)
			{
				$output->setVerbosity(OutputInterface::VERBOSITY_VERBOSE);
			}
		}
	}

	/**
	 * Method to run the application routines.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function doExecute()
	{
		$helpCommand = false;

		$commandName = $this->getCommandName();

		if (!$commandName)
		{
			$this->out('<comment>Command name not given.</comment>');

			$this->close(1);
		}

		if ($this->getConsoleInput()->hasParameterOption(['--help', '-h'], true))
		{
			$helpCommand = $commandName;
			$commandName = 'help';
		}

		$command = $this->getCommand($commandName);

		// Create the command synopsis before merging the application input definition
		$command->getSynopsis(true);
		$command->getSynopsis(false);

		$command->mergeApplicationDefinition($this->definition);

		$this->getConsoleInput()->bind($command->getDefinition());

		// If the user is looking for help, set the command name for use
		if ($helpCommand !== false)
		{
			$this->getConsoleInput()->setArgument('command_name', $helpCommand);
			$this->input->set('command_name', $helpCommand);
		}

		// Make sure the command argument is defined to both inputs, validation may fail otherwise
		if ($this->getConsoleInput()->hasArgument('command') && $this->getConsoleInput()->getArgument('command') === null)
		{
			$this->getConsoleInput()->setArgument('command', $command->getName());
		}

		$this->input->def('command', $command->getName());

		$this->getConsoleInput()->validate();

		// Push the console input into any helpers which are input aware
		foreach ($command->getHelperSet() as $helper)
		{
			if ($helper instanceof InputAwareInterface)
			{
				$helper->setInput($this->getConsoleInput());
			}
		}

		$exitCode = $command->execute();

		$this->exitCode = is_numeric($exitCode) ? (int) $exitCode : 0;
	}

	/**
	 * Execute the application.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function execute()
	{
		$this->configureIO();

		$this->doExecute();

		if ($this->autoExit)
		{
			$exitCode = $this->exitCode > 255 ? 255 : $this->exitCode;

			$this->close($exitCode);
		}
	}

	/**
	 * Finds a registered namespace by a name or an abbreviation.
	 *
	 * @param string $namespace A namespace or abbreviation to search for
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  CommandNotFoundException When namespace is incorrect or ambiguous
	 */
	public function findNamespace($namespace)
	{
		$allNamespaces = $this->getNamespaces();
		$expr          = preg_replace_callback(
			'{([^:]+|)}',
			function ($matches)
			{
				return preg_quote($matches[1]) . '[^:]*';
			},
			$namespace
		);
		$namespaces    = preg_grep('{^' . $expr . '}', $allNamespaces);

		if (empty($namespaces))
		{
			$message = sprintf('There are no commands defined in the "%s" namespace.', $namespace);

			throw new CommandNotFoundException($message);
		}

		$exact = in_array($namespace, $namespaces, true);

		if (count($namespaces) > 1 && !$exact)
		{
			throw new CommandNotFoundException(sprintf('The namespace "%s" is ambiguous.', $namespace));
		}

		return $exact ? $namespace : reset($namespaces);
	}

	/**
	 * Gets all commands, including those available through a command loader, optionally filtered on a command namespace.
	 *
	 * @param   string  $namespace  An optional command namespace to filter by.
	 *
	 * @return  CommandInterface[]
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getAllCommands(string $namespace = ''): array
	{
		if ($namespace === '')
		{
			$commands = $this->commands;

			if (!$this->commandLoader)
			{
				return $commands;
			}

			foreach ($this->commandLoader->getNames() as $name)
			{
				if (!isset($commands[$name]))
				{
					$commands[$name] = $this->getCommand($name);
				}
			}

			return $commands;
		}

		$commands = [];

		foreach ($this->commands as $name => $command)
		{
			if ($namespace === $this->extractNamespace($name, substr_count($namespace, ':') + 1))
			{
				$commands[$name] = $command;
			}
		}

		if ($this->commandLoader)
		{
			foreach ($this->commandLoader->getNames() as $name)
			{
				if (!isset($commands[$name]) && $namespace === $this->extractNamespace($name, substr_count($namespace, ':') + 1))
				{
					$commands[$name] = $this->get($name);
				}
			}
		}

		return $commands;
	}

	/**
	 * Gets the base helper set.
	 *
	 * @return  HelperSet
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getBaseHelperSet()
	{
		return new HelperSet(
			[
				new FormatterHelper,
				new DebugFormatterHelper,
				new ProcessHelper,
				new QuestionHelper,
			]
		);
	}

	/**
	 * Gets the base input definition.
	 *
	 * @return  InputDefinition
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getBaseInputDefinition(): InputDefinition
	{
		return new InputDefinition(
			[
				new InputArgument('command', InputArgument::REQUIRED, 'The command to execute'),
				new InputOption('--help', '-h', InputOption::VALUE_NONE, 'Display the help information'),
				new InputOption('--quiet', '-q', InputOption::VALUE_NONE, 'Flag indicating that all output should be silenced'),
				new InputOption(
					'--verbose',
					'-v|vv|vvv',
					InputOption::VALUE_NONE,
					'Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug'
				),
				new InputOption('--ansi', '', InputOption::VALUE_NONE, 'Force ANSI output'),
				new InputOption('--no-ansi', '', InputOption::VALUE_NONE, 'Disable ANSI output'),
				new InputOption('--no-interaction', '-n', InputOption::VALUE_NONE, 'Flag to disable interacting with the user'),
			]
		);
	}

	/**
	 * Get a command by name.
	 *
	 * @param   string  $name  The name of the command to retrieve.
	 *
	 * @return  CommandInterface
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  CommandNotFoundException
	 */
	public function getCommand(string $name): CommandInterface
	{
		if (isset($this->commands[$name]))
		{
			return $this->commands[$name];
		}

		if ($this->commandLoader && $this->commandLoader->has($name))
		{
			$command = $this->commandLoader->get($name);

			$this->addCommand($command);

			return $command;
		}

		throw new CommandNotFoundException("There is not a command with the name '$name'.");
	}

	/**
	 * Get the name of the command to run.
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getCommandName(): string
	{
		$args = $this->input->args;

		return !empty($args[0]) ? $args[0] : $this->defaultCommand;
	}

	/**
	 * Get the registered commands.
	 *
	 * This method only retrieves commands which have been explicitly registered.  To get all commands including those from a
	 * command loader, use the `getAllCommands()` method.
	 *
	 * @return  CommandInterface[]
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getCommands(): array
	{
		return $this->commands;
	}

	/**
	 * Get the console input handler.
	 *
	 * @return  InputInterface
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getConsoleInput(): InputInterface
	{
		return $this->consoleInput;
	}

	/**
	 * Get the console output handler.
	 *
	 * @return  OutputInterface
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getConsoleOutput(): OutputInterface
	{
		return $this->consoleOutput;
	}

	/**
	 * Get the commands which should be registered by default to the application.
	 *
	 * @return  CommandInterface[]
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getDefaultCommands(): array
	{
		return [
			new Command\ListCommand,
			new Command\HelpCommand,
		];
	}

	/**
	 * Get the application definition.
	 *
	 * @return  InputDefinition
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getDefinition(): InputDefinition
	{
		return $this->definition;
	}

	/**
	 * Get the command's exit code.
	 *
	 * @return  integer
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getExitCode(): int
	{
		return $this->exitCode;
	}

	/**
	 * Get the application helper set.
	 *
	 * @return  HelperSet
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getHelperSet(): HelperSet
	{
		return $this->helperSet;
	}

	/**
	 * Returns an array of all unique namespaces used by currently registered commands.
	 *
	 * Note that this does not include the global namespace which always exists.
	 *
	 * @return  string[]
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getNamespaces(): array
	{
		$namespaces = [];

		foreach ($this->getAllCommands() as $command)
		{
			$namespaces = array_merge($namespaces, $this->extractAllNamespaces($command->getName()));

			foreach ($command->getAliases() as $alias)
			{
				$namespaces = array_merge($namespaces, $this->extractAllNamespaces($alias));
			}
		}

		return array_values(array_unique(array_filter($namespaces)));
	}

	/**
	 * Check if the application has a command with the given name.
	 *
	 * @param   string  $name  The name of the command to check for existence.
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function hasCommand(string $name): bool
	{
		return isset($this->commands[$name]) || ($this->commandLoader && $this->commandLoader->has($name));
	}

	/**
	 * Custom initialisation method.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function initialise()
	{
		// Set the current directory.
		$this->set('cwd', getcwd());

		$this->consoleInput  = new JoomlaInput($this->input);
		$this->consoleOutput = new ConsoleOutput;

		$this->definition = $this->getBaseInputDefinition();
		$this->helperSet  = $this->getBaseHelperSet();

		// Register default commands
		foreach ($this->getDefaultCommands() as $command)
		{
			$this->addCommand($command);
		}
	}

	/**
	 * Set whether the application should auto exit.
	 *
	 * @param   boolean  $autoExit  The auto exit state.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setAutoExit(bool $autoExit)
	{
		$this->autoExit = $autoExit;
	}

	/**
	 * Set the command loader.
	 *
	 * @param   Loader\LoaderInterface  $loader  The new command loader.
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setCommandLoader(Loader\LoaderInterface $loader)
	{
		$this->commandLoader = $loader;

		return $this;
	}

	/**
	 * Set the console input handler.
	 *
	 * @param   InputInterface  $input  The new console input handler.
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setConsoleInput(InputInterface $input)
	{
		$this->consoleInput = $input;

		return $this;
	}

	/**
	 * Set the console output handler.
	 *
	 * @param   OutputInterface  $output  The new console output handler.
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setConsoleOutput(OutputInterface $output)
	{
		$this->consoleOutput = $output;

		return $this;
	}

	/**
	 * Get the application's auto exit state.
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function shouldAutoExit(): bool
	{
		return $this->autoExit;
	}

	/**
	 * Returns all namespaces of the command name.
	 *
	 * @param   string  $name  The name of the command
	 *
	 * @return  string[] The command's namespaces
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function extractAllNamespaces(string $name): array
	{
		// -1 as third argument is needed to skip the command short name when exploding
		$parts      = explode(':', $name, -1);
		$namespaces = [];

		foreach ($parts as $part)
		{
			if (count($namespaces))
			{
				$namespaces[] = end($namespaces) . ':' . $part;
			}
			else
			{
				$namespaces[] = $part;
			}
		}

		return $namespaces;
	}

	/**
	 * Returns the namespace part of the command name.
	 *
	 * @param   string   $name   The command name to process
	 * @param   integer  $limit  The maximum number of parts of the namespace
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function extractNamespace(string $name, $limit = null)
	{
		$parts = explode(':', $name);
		array_pop($parts);

		return implode(':', $limit === null ? $parts : array_slice($parts, 0, $limit));
	}
}
