<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SprykGui\Business\Model;

use Generated\Shared\Transfer\ArgumentCollectionTransfer;
use Generated\Shared\Transfer\ArgumentTransfer;
use Generated\Shared\Transfer\ModuleTransfer;
use Spryker\Zed\SprykGui\Business\Model\Graph\GraphBuilderInterface;
use Spryker\Zed\SprykGui\Dependency\Facade\SprykGuiToSprykFacadeInterface;
use Symfony\Component\Process\Process;
use Zend\Filter\FilterChain;
use Zend\Filter\Word\CamelCaseToSeparator;

class Spryk implements SprykInterface
{
    /**
     * @var \Spryker\Zed\SprykGui\Dependency\Facade\SprykGuiToSprykFacadeInterface
     */
    protected $sprykFacade;

    /**
     * @var \Spryker\Zed\SprykGui\Business\Model\Graph\GraphBuilderInterface
     */
    protected $graphBuilder;

    /**
     * @param \Spryker\Zed\SprykGui\Dependency\Facade\SprykGuiToSprykFacadeInterface $sprykFacade
     * @param \Spryker\Zed\SprykGui\Business\Model\Graph\GraphBuilderInterface $graphBuilder
     */
    public function __construct(SprykGuiToSprykFacadeInterface $sprykFacade, GraphBuilderInterface $graphBuilder)
    {
        $this->sprykFacade = $sprykFacade;
        $this->graphBuilder = $graphBuilder;
    }

    /**
     * @return array
     */
    public function getSprykDefinitions(): array
    {
        return $this->organizeSprykDefinitions(
            $this->sprykFacade->getSprykDefinitions()
        );
    }

    /**
     * @param string $spryk
     *
     * @return array
     */
    public function getSprykDefinitionByName(string $spryk): array
    {
        $sprykDefinitions = $this->sprykFacade->getSprykDefinitions();

        return $sprykDefinitions[$spryk];
    }

    /**
     * @param string $sprykName
     * @param array $formData
     *
     * @return array
     */
    public function buildSprykView(string $sprykName, array $formData): array
    {
        $formData = $this->normalizeFormData($formData);
        $commandLine = $this->getCommandLine($sprykName, $formData);
        $jiraTemplate = $this->getJiraTemplate($sprykName, $commandLine, $formData);

        return [
            'commandLine' => $commandLine,
            'jiraTemplate' => $jiraTemplate,
        ];
    }

    /**
     * @param array $formData
     *
     * @return array
     */
    protected function normalizeFormData(array $formData): array
    {
        $normalizedFormData = [];
        foreach ($formData as $key => $value) {
            if ($key === 'spryk') {
                continue;
            }
            if ($value instanceof ModuleTransfer) {
                $normalizedFormData['module'] = $value->getName();
                $normalizedFormData['organization'] = $value->getOrganization()->getName();
                $normalizedFormData['rootPath'] = $value->getOrganization()->getRootPath();

                continue;
            }

            if (isset($formData['constructorArguments'])) {
            }

            if ($key === 'sprykDetails') {
                foreach ($value as $sprykDetailKey => $sprykDetailValue) {
                    if (isset($normalizedFormData[$sprykDetailKey])) {
                        continue;
                    }
                    $normalizedFormData[$sprykDetailKey] = $sprykDetailValue;
                }
                continue;
            }

            $normalizedFormData[$key] = $value;
        }

        return $normalizedFormData;
    }

    /**
     * @param string $sprykName
     * @param array $formData
     *
     * @return string
     */
    public function runSpryk(string $sprykName, array $formData): string
    {
        $formData = $this->normalizeFormData($formData);
        $commandLine = $this->getCommandLine($sprykName, $formData);
        $process = new Process($commandLine, APPLICATION_ROOT_DIR);
        $process->run();

        if ($process->isSuccessful()) {
            return $process->getOutput();
        }

        return $process->getErrorOutput();
    }

    /**
     * @param string $sprykName
     *
     * @return string
     */
    public function drawSpryk(string $sprykName): string
    {
        return $this->graphBuilder->drawSpryk($sprykName);
    }

    /**
     * @param array $sprykDefinitions
     *
     * @return array
     */
    protected function organizeSprykDefinitions(array $sprykDefinitions): array
    {
        $organized = [];

        foreach ($sprykDefinitions as $sprykName => $sprykDefinition) {
            $application = $this->getApplicationBySprykName($sprykName);
            if (!isset($organized[$application])) {
                $organized[$application] = [];
            }
            $organized[$application][$sprykName] = [
                'humanized' => $this->createHumanizeFilter()->filter($sprykName),
                'description' => $sprykDefinition['description'],
                'priority' => isset($sprykDefinition['priority'])?$sprykDefinition['priority']:'',
            ];

            ksort($organized[$application]);
        }

        return $organized;
    }

    /**
     * @param string $sprykName
     *
     * @return string
     */
    protected function getApplicationBySprykName(string $sprykName): string
    {
        $humanizedSprykName = $this->createHumanizeFilter()->filter($sprykName);
        $humanizedSprykNameFragments = explode(' ', $humanizedSprykName);
        $applications = ['Client', 'Shared', 'Yves', 'Zed'];

        if (in_array($humanizedSprykNameFragments[1], $applications)) {
            return $humanizedSprykNameFragments[1];
        }

        return 'Common';
    }

    /**
     * @return \Zend\Filter\FilterChain
     */
    protected function createHumanizeFilter(): FilterChain
    {
        $filterChain = new FilterChain();
        $filterChain->attach(new CamelCaseToSeparator(' '));

        return $filterChain;
    }

    /**
     * @param string $sprykName
     * @param array $formData
     *
     * @return string
     */
    protected function getCommandLine(string $sprykName, array $formData): string
    {
        $commandLineArguments = $this->getSprykArguments($sprykName, $formData);

        $commandLine = '';
        foreach ($commandLineArguments as $argumentKey => $argumentValue) {
            $argumentValues = (array)$argumentValue;
            foreach ($argumentValues as $argumentValue) {
                $commandLine .= sprintf(' --%s=%s', $argumentKey, escapeshellarg($argumentValue));
            }
        }

        $commandLine = sprintf('vendor/bin/console spryk:run %s %s -n', $sprykName, $commandLine);

        return $commandLine;
    }

    /**
     * @param string $sprykName
     * @param array $formData
     *
     * @return string
     */
    protected function getSprykArguments(string $sprykName, array $formData)
    {
        $commandLineArguments = [];

        $sprykDefinition = $this->getSprykDefinitionByName($sprykName);

        $filteredSprykArguments = $this->filterSprykArguments($sprykDefinition, $formData);

        foreach ($filteredSprykArguments as $argumentName => $argumentDefinition) {
            $userInput = $this->getUserInputForArgument($argumentName, $formData);
            if (isset($argumentDefinition['multiline'])) {
                $userInput = $this->getMultilineConsoleArgument($userInput);
            }
            if (isset($argumentDefinition['isMultiple'])) {
                if ($argumentName === 'constructorArguments') {
                    $argumentCollectionTransfer = $userInput['arguments'];
                    $userInput = [];
                    $dependencyMethods = [];
                    if ($argumentCollectionTransfer instanceof ArgumentCollectionTransfer) {
                        foreach ($argumentCollectionTransfer->getArguments() as $argumentTransfer) {
                            $userInput[] = $this->buildFromArgument($argumentTransfer);
                            if ($argumentTransfer->getArgumentMeta() && $argumentTransfer->getArgumentMeta()->getMethod()) {
                                $dependencyMethods[] = $argumentTransfer->getArgumentMeta()->getMethod();
                            }
                        }
                    }
                    $commandLineArguments[$argumentName] = $userInput;
                    if (count($dependencyMethods) > 0) {
                        $commandLineArguments['dependencyMethods'] = $dependencyMethods;
                    }
                    continue;
                }
            }

            $commandLineArguments[$argumentName] = $userInput;
        }

        return $commandLineArguments;

//        foreach ($formData as $key => $userInput) {
//            foreach ($sprykDefinition['arguments'] as $argumentName => $argumentDefinition) {
//                if (isset($argumentDefinition['value']) || isset($argumentDefinition['callbackOnly'])) {
//                    continue;
//                }
//
//                if ($argumentName === 'constructorArguments') {
//                    if (!isset($userInput['arguments'])) {
//                        continue;
//                    }
//
//                    $argumentString .= sprintf(' --%s=%s', $argumentName, escapeshellarg($this->buildFromArguments($userInput)));
//
//                    foreach ($userInput['arguments'] as $userArgumentDefinition) {
//                        $argumentTransfer = $this->getArgumentTransferFromDefinition($userArgumentDefinition);
//                        $argumentMetaTransfer = $argumentTransfer->getArgumentMeta();
//
//                        $argumentString .= sprintf(' --dependencyMethods=%s', escapeshellarg($argumentMetaTransfer->getMethod()));
//                    }
//
//                    continue;
//                }
//
//                if (isset($addedArguments[$argumentName]) && ($userInput !== $addedArguments[$argumentName])) {
//                    $argumentName = sprintf('%s.%s', $sprykName, $argumentName);
//                }
//
//                if (isset($addedArguments[$argumentName])) {
//                    continue;
//                }
//
//                if ($argumentName === $key && $userInput instanceof ModuleTransfer) {
//                    $userInput = $userInput->getName();
//                }
//
//                if ($argumentName === 'moduleOrganization' && $userInput instanceof ModuleTransfer) {
//                    $userInput = $userInput->getOrganization()->getName();
//                }
//
//                if ((!isset($argumentDefinition['default'])) || (isset($argumentDefinition['default']) && $argumentDefinition['default'] !== $userInput)) {
//                    if (!isset($argumentDefinition['multiline'])) {
//                        $argumentString .= sprintf(' --%s=%s', $argumentName, escapeshellarg($userInput));
//                        $addedArguments[$argumentName] = $userInput;
//
//                        continue;
//                    }
//
//                    $lines = explode(PHP_EOL, $userInput);
//                    foreach ($lines as $line) {
//                        $line = preg_replace('/[[:cntrl:]]/', '', $line);
//                        $argumentString .= sprintf(' --%s=%s', $argumentName, escapeshellarg($line));
//                    }
//
//                    $addedArguments[$argumentName] = $userInput;
//                }
//            }
//        }
//
//        foreach ($includeOptionalSpryks as $includeOptionalSpryk) {
//            $argumentString .= sprintf(' --include-optional=%s', $includeOptionalSpryk);
//        }
//
//        return $argumentString;
    }

    /**
     * @param array $userInput
     *
     * @return array
     */
    protected function getMultilineConsoleArgument(array $userInput)
    {
        $lines = explode(PHP_EOL, $userInput);
        $userInput = [];
        foreach ($lines as $line) {
            $line = preg_replace('/[[:cntrl:]]/', '', $line);
            $userInput[] = $line;
        }

        return $userInput;
    }

    /**
     * @param string $argumentName
     * @param array $formData
     *
     * @return mixed
     */
    protected function getUserInputForArgument(string $argumentName, array $formData)
    {
        return $formData[$argumentName];
    }

    /**
     * @param array $sprykDefinition
     * @param array $formData
     *
     * @return array
     */
    protected function filterSprykArguments(array $sprykDefinition, array $formData)
    {
        $sprykArguments = [];

        foreach ($sprykDefinition['arguments'] as $argumentName => $argumentDefinition) {
            if (isset($argumentDefinition['value']) || isset($argumentDefinition['callbackOnly'])) {
                continue;
            }

            $userInput = $this->getUserInputForArgument($argumentName, $formData);
            if (isset($argumentDefinition['default']) && $argumentDefinition['default'] === $userInput) {
                continue;
            }

            $sprykArguments[$argumentName] = $argumentDefinition;
        }

        return $sprykArguments;
    }

    /**
     * @param array $argumentDefinition
     *
     * @return \Generated\Shared\Transfer\ArgumentTransfer
     */
    protected function getArgumentTransferFromDefinition(array $argumentDefinition): ArgumentTransfer
    {
        return $argumentDefinition['argument'];
    }

    /**
     * @param \Generated\Shared\Transfer\ArgumentTransfer $argumentTransfer
     *
     * @return string
     */
    protected function buildFromArgument(ArgumentTransfer $argumentTransfer)
    {
        $pattern = '%s %s';
        if ($argumentTransfer->getIsOptional()) {
            $pattern = '?%s %s = null';
        }

        return sprintf($pattern, $argumentTransfer->getType(), $argumentTransfer->getVariable());
    }

    /**
     * @param string $sprykName
     * @param string $commandLine
     * @param array $formData
     *
     * @return string
     */
    protected function getJiraTemplate(string $sprykName, string $commandLine, array $formData): string
    {
        $jiraTemplate = PHP_EOL . sprintf('{code:title=%s|theme=Midnight|linenumbers=true|collapse=true}', $sprykName) . PHP_EOL;
        $jiraTemplate .= $commandLine . PHP_EOL . PHP_EOL;

        $sprykArguments = $this->getSprykArguments($sprykName, $formData);

        foreach ($sprykArguments as $argumentName => $argumentValue) {
            $jiraTemplate .= sprintf('"%s"', $argumentName) . PHP_EOL;
            $argumentValues = (array)$argumentValue;
            foreach ($argumentValues as $argumentValue) {
                $jiraTemplate .= sprintf('// %s', $argumentValue) . PHP_EOL;
            }
            $jiraTemplate .= PHP_EOL;
        }
//        $sprykDefinitions = $this->sprykFacade->getSprykDefinitions();
//        foreach ($sprykArguments as $sprykName => $userArguments) {
//            $sprykDefinition = $sprykDefinitions[$sprykName];
//            foreach ($sprykDefinition['arguments'] as $argumentName => $argumentDefinition) {
//                if (isset($argumentDefinition['value']) || isset($argumentDefinition['callbackOnly'])) {
//                    continue;
//                }
//
//                $userInput = $userArguments[$argumentName];
//                if ($argumentName === 'constructorArguments') {
//                    if (!isset($userInput['arguments'])) {
//                        continue;
//                    }
//
//                    $jiraTemplate .= sprintf('"%s"', $argumentName) . PHP_EOL;
//                    $jiraTemplate .= sprintf('// %s', $this->buildFromArguments($userInput)) . PHP_EOL . PHP_EOL;
//
//                    foreach ($userInput['arguments'] as $userArgumentDefinition) {
//                        $argumentTransfer = $this->getArgumentTransferFromDefinition($userArgumentDefinition);
//                        $argumentMetaTransfer = $argumentTransfer->getArgumentMeta();
//
//                        $jiraTemplate .= '"factoryDependencyMethod"' . PHP_EOL;
//                        $jiraTemplate .= sprintf('// %s', $argumentMetaTransfer->getMethod()) . PHP_EOL . PHP_EOL;
//                    }
//
//                    continue;
//                }
//                if (isset($addedArguments[$argumentName]) && ($userInput !== $addedArguments[$argumentName])) {
//                    $argumentName = sprintf('%s.%s', $sprykName, $argumentName);
//                }
//
//                if (isset($addedArguments[$argumentName])) {
//                    continue;
//                }
//
//                if ((!isset($argumentDefinition['default'])) || (isset($argumentDefinition['default']) && $argumentDefinition['default'] !== $userInput)) {
//                    $jiraTemplate .= sprintf('"%s"', $argumentName) . PHP_EOL;
//
//                    if (!isset($argumentDefinition['multiline'])) {
//                        $jiraTemplate .= sprintf('// %s', $userInput) . PHP_EOL . PHP_EOL;
//
//                        $addedArguments[$argumentName] = $userInput;
//
//                        continue;
//                    }
//
//                    $lines = explode(PHP_EOL, $userInput);
//                    foreach ($lines as $line) {
//                        $line = preg_replace('/[[:cntrl:]]/', '', $line);
//                        $jiraTemplate .= sprintf('// %s', $line) . PHP_EOL;
//                    }
//
//                    $jiraTemplate .= PHP_EOL;
//
//                    $addedArguments[$argumentName] = $userInput;
//                }
//            }
//        }

        $jiraTemplate .= '{code}';

        return $jiraTemplate;
    }
}
