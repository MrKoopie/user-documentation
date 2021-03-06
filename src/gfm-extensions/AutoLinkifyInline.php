<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the BSD-style license found in the
 *  LICENSE file in the root directory of this source tree. An additional grant
 *  of patent rights can be found in the PATENTS file in the same directory.
 *
 */

namespace HHVM\UserDocumentation\GFM;

use type HHVM\UserDocumentation\{BuildPaths, YAMLMeta};

use namespace Facebook\GFM\Inlines;
use namespace Facebook\TypeAssert;
use namespace HHVM\UserDocumentation\JSON;
use namespace HH\Lib\{C, Dict, Str, Vec};

/**
 * Given something like `Vec\map()`, automatically make it a link
 */
final class AutoLinkifyInline extends Inlines\Link {
  const string PATTERN =
    '/^@@ (?<dir>[^@ ]+)'.
    '(?<file>[^@ \\/]+\\.php'.
      '(?:.type-errors)?'.
      '(?:.(hhvm|typechecker).expect[f]?)?'.
    ') @@$/';

  public static function consume(
    Inlines\Context $context,
    string $previous,
    string $rest,
  ): ?(Inlines\Link, string, string) {
    $result = Inlines\CodeSpan::consume($context, $previous, $rest);
    if ($result === null) {
      return null;
    }
    list($quoted, $last, $rest) = $result;

    $content = $quoted->getCode();
    if ($content === null) {
      return null;
    }
    if (Str\contains($content, ' ')) {
      return null;
    }
    if (Str\starts_with($content, '$')) {
      return null;
    }

    $matches = [];
    if (\preg_match('/^[^(<]+/', $content, $matches) !== 1) {
      return null;
    }
    $definition = (string) $matches[0];

    $block_context = $context->getBlockContext();
    invariant(
      $block_context instanceof BlockContext,
      'Expected block context to be a %s',
      BlockContext::class,
    );
    $meta = $block_context->getYamlMeta();

    $name = ($meta['name'] ?? null);
    if ($name !== null && Str\ends_with($name, $definition)) {
      return null;
    }

    $method = self::getMethodTarget($meta, $definition);

    $to_try = Vec\filter_nulls(vec[
      $method,
      $definition,
      "HH\\".$definition,
      "HH\\".$method,
      "HH\\Lib\\".$definition,
      "HH\\Lib\\".$method,
    ]);


    $index = self::getIndex();


    foreach ($to_try as $def) {
      $target = $index[$def] ?? null;
      if ($target !== null) {
        return tuple(
          self::makeAutoLink($quoted, $target),
          $last,
          $rest,
        );
      }
    }

    return null;
  }

  private static function getMethodTarget(
    ?YAMLMeta $yaml,
    string $def,
  ): ?string {
    if ($yaml === null) {
      return null;
    }
    if (Str\contains($def, '::')) {
      return null;
    }
    $class = $yaml['class'] ?? null;
    if ($class === null) {
      return null;
    }

    return $class.'::'.Str\strip_prefix($def, '::');
  }

  private static function makeAutoLink(
    Inlines\CodeSpan $code,
    string $target,
  ): Inlines\Link {
    return new Inlines\Link(
      vec[$code],
      $target,
      null,
    );
  }

  <<__Memoize>>
  private static function getIndex(): dict<string, string> {
    return \file_get_contents(BuildPaths::UNIFIED_INDEX_JSON)
      |> JSON\decode_as_dict($$)
      |> Dict\map($$, $v ==> TypeAssert\string($v));
  }
}
