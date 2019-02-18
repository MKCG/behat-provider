<?php

/**
 * @author  Kévin Masseix <masseix.kevin@gmail.com>
 */
namespace MKCG\Behat\Provider;

use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\TableNode;

class Injector
{
    const TAG_NAME = 'provider';
    const TAG_COPY = 'provider-copy';

    private $providers = [];

    private $nbCopy;

    public function __construct(int $nbCopy = 0)
    {
        $this->nbCopy = $nbCopy;
    }

    public function addProvider(string $name, $provider)
    {
        $this->providers[$name] = $provider;
        return $this;
    }

    public function injectValues(FeatureNode $feature)
    {
        foreach ($feature->getScenarios() as $scenario) {
            $tags = $scenario->getTags();

            if (!in_array(self::TAG_NAME, $tags)) {
                continue;
            }

            if ($scenario instanceof \Behat\Gherkin\Node\OutlineNode) {
                $exampleTable = $scenario->getExampleTable();
                in_array(self::TAG_COPY, $tags) and $this->copyTableLines($exampleTable);
                $this->setTableValues($exampleTable);
                $this->updateLineLength($exampleTable);
            }
        }
    }

    private function updateLineLength(TableNode $tableNode)
    {
        $maxLineLength = [];

        foreach ($tableNode->getTable() as $line) {
            foreach ($line as $i => $value) {
                if (!isset($maxLineLength[$i])) {
                    $maxLineLength[$i] = mb_strlen($value);
                } else {
                    $maxLineLength[$i] = max($maxLineLength[$i], mb_strlen($value));
                }
            }
        }

        $this->alterProperty('\Behat\Gherkin\Node\TableNode', $tableNode, 'maxLineLength', $maxLineLength);
    }

    private function alterTable(TableNode $tableNode, callable $callback)
    {
        $table = $tableNode->getTable();
        reset($table);
        $firstLine = current($table);

        if (!$firstLine) {
            return;
        }

        $providerCol = array_search(self::TAG_NAME, $firstLine);

        if ($providerCol === false) {
            return;
        }

        array_shift($table);
        $table = array_merge([$firstLine], $callback($table, $providerCol));
        $this->alterProperty('\Behat\Gherkin\Node\TableNode', $tableNode, 'table', $table);
    }

    private function copyTableLines(TableNode $tableNode)
    {
        $this->alterTable($tableNode, function($table, $providerCol) {
            $newTable = [];

            foreach ($table as $line) {
                if (empty($line[$providerCol])) {
                    $newTable[] = $line;
                } else {
                    for ($i = 0; $i <= $this->nbCopy; $i++) {
                        $newTable[] = $line;
                    }
                }
            }

            return $newTable;
        });
    }

    private function setTableValues(TableNode $tableNode)
    {
        $this->alterTable($tableNode, function($table, $providerCol) {
            return array_map(function($line) use ($providerCol) {
                return $this->changeTableLine($line, $providerCol);
            }, $table);
        });
    }

    private function changeTableLine(array $line, int $providerCol) : array
    {
        if (empty($line[$providerCol])) {
            return $line;
        }

        $providerName = trim($line[$providerCol]);

        if (!isset($this->providers[$providerName])) {
            return $line;
        }

        $provider = $this->providers[$providerName];

        if (is_callable($provider)) {
            $generated = $provider();

            foreach ($generated as $key => $value) {
                $colPos = array_search('<' . $key . '>', $line);

                if ($colPos !== false) {
                    $line[$colPos] = $value;
                }
            }
        }

        return $line;
    }

    private function alterProperty($className, $object, $property, $value)
    {
        $tableReflection = new \ReflectionProperty($className, $property);
        $tableReflection->setAccessible(true);
        $tableReflection->setValue($object, $value);
        $tableReflection->setAccessible(false);
    }
}
