<?hh

namespace Hack\UserDocumentation\Attributes\Special\Examples\ConsistentConstr;


<<__ConsistentConstruct>>
abstract class A {
}

class B extends A {
}

class C extends A {
  // __ConsistentConstruct applied to A will cause the typechecker to throw
  // an error when you have constructor with a different signature.
  /*private int $x;
  public function __construct(int $x) {
    $this->x = x;
  }*/
}

<<__ConsistentConstruct>>
abstract class Y {
  // Without __ConsistentConstruct, the typechecker cannot guarantee
  // consistency in child classes of Y. If we didn't have the attribute
  // the typechecker would throw an error about this lack of guarantee
  public function create(): this {
    return new static();
  }
}

class Z extends Y {}
