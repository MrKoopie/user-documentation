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

use type Facebook\GFM\Blocks\ThematicBreak as ASTNode;
use namespace Facebook\GFM\Inlines;
use namespace HH\Lib\{C, Vec};

final class ThematicBreak extends LeafBlock {

  public static function consume(
    Context $_,
    vec<string> $lines,
  ): ?(Block, vec<string>) {
    $first = C\firstx($lines);
    if (\preg_match('/^ {0,3}([-_*] *){3,}$/', $first) !== 1) {
      return null;
    }
    return tuple(new self(), Vec\drop($lines, 1));
  }

  public function withParsedInlines(Inlines\Context $context): ASTNode {
    return new ASTNode();
  }
}
