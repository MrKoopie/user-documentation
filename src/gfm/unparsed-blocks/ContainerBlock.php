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

namespace Facebook\GFM\UnparsedBlocks;

use namespace HH\Lib\C;


abstract class ContainerBlock extends Block {
  protected static function consumeChildren(
    Context $context,
    vec<string> $lines,
  ): vec<Block> {
    $children = vec[];
    while (!C\is_empty($lines)) {
      $match = null;
      foreach ($context->getBlockTypes() as $block) {
        $match = $block::consume($context, $lines);
        if ($match !== null) {
          break;
        }
      }
      invariant($match !== null, 'should *always* find a match');
      list($child, $new_lines) = $match;
      $children[] = $child;
      invariant(
        C\count($lines) > C\count($new_lines),
        'consuming failed to reduce line count with class "%s" on line "%s"',
        get_class($child),
        $lines[0],
      );
      $lines = $new_lines;
    }
    return $children;
  }
}
