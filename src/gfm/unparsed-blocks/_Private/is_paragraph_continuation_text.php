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

namespace Facebook\GFM\UnparsedBlocks\_Private;;

use type Facebook\GFM\UnparsedBlocks\{Block, Context};
use namespace HH\Lib\C;

function is_paragraph_continuation_text(
  Context $context,
  vec<string> $lines,
): bool {
  return !C\any(
    $context->getBlockTypes(),
    (classname<Block> $block) ==> !C\contains_key(
      $context->getIgnoredBlockTypesForParagraphContinuation(),
      $block,
    ) &&
      $block::consume($context, $lines) !== null,
  );
}
