<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;
use PhpCsFixer\Fixer\ClassNotation\FinalClassFixer;
use PhpCsFixer\Fixer\ClassNotation\NoBlankLinesAfterClassOpeningFixer;
use PhpCsFixer\Fixer\ClassNotation\VisibilityRequiredFixer;
use PhpCsFixer\Fixer\ControlStructure\TrailingCommaInMultilineFixer;
use PhpCsFixer\Fixer\FunctionNotation\NativeFunctionInvocationFixer;
use PhpCsFixer\Fixer\FunctionNotation\UseArrowFunctionsFixer;
use PhpCsFixer\Fixer\FunctionNotation\VoidReturnFixer;
use PhpCsFixer\Fixer\Import\NoUnusedImportsFixer;
use PhpCsFixer\Fixer\Import\OrderedImportsFixer;
use PhpCsFixer\Fixer\NamespaceNotation\SingleBlankLineBeforeNamespaceFixer;
use PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer;
use PhpParser\Node\Name\FullyQualified;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\CodingStandard\Fixer\Commenting\RemoveUselessDefaultCommentFixer;
use Symplify\CodingStandard\Fixer\LineLength\LineLengthFixer;
use Symplify\EasyCodingStandard\ValueObject\Option;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(SetList::PSR_12);
    $containerConfigurator->import(SetList::CLEAN_CODE);
    $containerConfigurator->import(SetList::STRICT);
    $containerConfigurator->import(SetList::ARRAY);
    $containerConfigurator->import(SetList::DOCTRINE_ANNOTATIONS);

    $parameters = $containerConfigurator->parameters();
    $parameters
        ->set(Option::CACHE_DIRECTORY, '.ecs_cache')
        ->set(Option::INDENTATION, '    ')
        ->set(Option::LINE_ENDING, "\n")
        ->set(Option::PARALLEL, true)
        ->set(Option::PATHS, [__DIR__ . '/src', __DIR__ . '/tests', __DIR__ . '/ecs.php'])
        ->set(Option::SKIP, [
            FinalClassFixer::class => [__DIR__ . '/src/Entity/'],
        ])
    ;

    $services = $containerConfigurator->services();
    $services
        ->set(ArraySyntaxFixer::class)
        ->call('configure', [[
            'syntax' => 'short',
        ]])
        ->set(FinalClassFixer::class)
        ->set(LineLengthFixer::class)
        ->call('configure', [[
            LineLengthFixer::BREAK_LONG_LINES => true,
            LineLengthFixer::INLINE_SHORT_LINES => true,
            LineLengthFixer::LINE_LENGTH => 120,
        ]])
        ->set(FullyQualified::class)
        ->set(NativeFunctionInvocationFixer::class)
        ->set(NoBlankLinesAfterClassOpeningFixer::class)
        ->set(NoUnusedImportsFixer::class)
        ->set(OrderedImportsFixer::class)
        ->set(RemoveUselessDefaultCommentFixer::class)
        ->set(SingleBlankLineBeforeNamespaceFixer::class)
        ->set(TrailingCommaInMultilineFixer::class)
        ->set(UseArrowFunctionsFixer::class)
        ->set(VisibilityRequiredFixer::class)
        ->set(VoidReturnFixer::class)
        ->set(DeclareStrictTypesFixer::class)
    ;
};
