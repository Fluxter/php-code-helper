<?php

namespace Fluxter\PhpCodeHelper\Model;

use Microsoft\PhpParser\DiagnosticsProvider;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Parser;
use Microsoft\PhpParser\PositionUtilities;
use Microsoft\PhpParser\Token;
use Microsoft\PhpParser\Node\SourceFileNode;

class PhpFileInformation
{
    private string $file;
    private ?string $content = null;

    private Parser $parser;
    private SourceFileNode $node;

    public function __construct(string $file)
    {
        $this->file = $file;

        $this->parser = new Parser();
        $this->node = $this->parser->parseSourceFile($this->getContent());
    }

    public function getNamespace(): ?string
    {
        foreach ($this->node->statementList as $statement) {
            if ($statement instanceof Node\Statement\NamespaceDefinition) {
                return $statement->name->getText();
            }
        }
        return null;
    }

    public function getClasses(): \Iterator
    {
        foreach ($this->node->statementList as $statement) {
            if ($statement instanceof Node\Statement\ClassDeclaration) {
                // yield $statement->name->getText();
                yield substr($this->getContent(), $statement->name->getFullStart() + 1, $statement->name->getWidth());
            }
        }
    }

    public function getFqdns(): \Iterator
    {
        $namespace = $this->getNamespace();
        
        foreach ($this->getClasses() as $class) {
            yield "{$namespace}\\{$class}";
        }
    }

    public function getUsings(): \Iterator
    {
        foreach ($this->node->statementList as $statement) {
            if ($statement instanceof Node\Statement\NamespaceUseDeclaration) {
                yield $statement->useClauses->getText();
            }
        }
    }

    public function getContent()
    {
        if (null == $this->content) {
            $this->content = file_get_contents($this->file);
        }

        return $this->content;
    }

    /**
     * Get the value of file.
     */
    public function getFile()
    {
        return $this->file;
    }
}
