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

use type Facebook\HackRouter\StringRequestParameter;
use type Facebook\HackRouter\StringRequestParameterSlashes;
use type HHVM\UserDocumentation\{
  APIIndex,
  GuidesIndex,
  GuidesProduct,
  GuidePageSearchResult,
  PHPAPIIndex,
  SearchResult,
  SearchScores,
};

use type Psr\Http\Message\ServerRequestInterface;

use namespace HH\Lib\{C, Str, Vec};

final class SearchController extends WebPageController {
  use SearchControllerParametersTrait;

  public static function getUriPattern(): UriPattern {
    return (new UriPattern())->literal('/search');
  }

  <<__Override>>
  protected static function getExtraParametersSpec(
  ): self::TParameterDefinitions {
    return shape(
      'required' => ImmVector {
        new StringRequestParameter(
          StringRequestParameterSlashes::ALLOW_SLASHES,
          'term',
        ),
      },
      'optional' => ImmVector { },
    );
  }

  public async function getTitle(): Awaitable<string> {
    return "Search results for '{$this->getSearchTerm()}':";
  }

  protected async function getBody(): Awaitable<XHPRoot> {
    $results = Vec\map($this->getSearchResults(), $result ==>
      <li data-search-score={sprintf('%.2f', $result->getScore())}>
        <a href={$result->getHref()}>{$result->getTitle()}</a>
        <span class="searchResultType">{$result->getResultTypeText()}</span>
      </li>
    );

    return(
      <div class="innerContent">
        <ul class="searchResults">{$results}</ul>
      </div>
    );
  }

  <<__Memoize>>
  private function getSearchTerm(): string {
    return $this->getParameters()['term'];
  }

  private function getSearchResults(): vec<SearchResult> {
    $term = $this->getSearchTerm();
    $results = vec[
      $this->getHardcodedResults(),
      GuidesIndex::search($term),
      PHPAPIIndex::search($term),
      APIIndex::searchAllProducts($term),
    ]
      |> Vec\flatten($$)
      |> Vec\sort_by($$, $result ==> -($result->getScore()));

    if (C\count($results) < 5) {
      return $results;
    }

    $max = $results[0]->getScore();
    return Vec\filter($results, $r ==> $r->getScore() >= 0.3 * $max);
  }

  private function getHardcodedResults(): vec<SearchResult> {
    $term = Str\lowercase($this->getSearchTerm());

    $hack_array_keywords = keyset[
      'vec',
      'dict',
      'keyset',
      'vector', 'immvector', 'constvector',
      'map', 'immmap', 'constmap',
      'set', 'immset', 'constset',
    ];
    if (!C\contains_key($hack_array_keywords, $term)) {
      return vec[];
    }

    return vec[
      new GuidePageSearchResult(
        GuidesProduct::HACK,
        'collections',
        'hack-arrays',
        SearchScores::HARDCODED_RESULT_SCORE,
      ),
    ];
  }
}
