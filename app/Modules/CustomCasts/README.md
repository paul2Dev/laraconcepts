# Custom Casts & value objects

## What it demonstrates

`GET /concepts/custom-casts/demo` creates a `Product` with a `Money` value object as its
`price`, saves it, re-fetches it from the database, and returns the round-tripped value —
proving `MoneyCast` correctly converts between the domain object and the two raw database
columns backing it, not just holding the object in memory.

## How it works

**Multi-column value objects need `CastsAttributes`, not `Attribute::make()`.** Laravel's
newer `Attribute::make(get: ..., set: ...)` accessor/mutator API only sees the single
underlying column it's declared on. `Money` is backed by two columns (`price_amount`,
`price_currency`), so the cast needs the older `CastsAttributes` interface — its
`get()`/`set()` methods receive (and return) the model's whole raw attribute array, not
just one column's value.

**`set()` returns a column map, not a scalar.** Returning `$value` from `set()` (as a
scalar cast would) only works for a single column. For a value object spanning several
columns, `set()` must return an associative array of `column => value` — that array is
merged into the model's attributes, replacing the virtual `price` key with the real
`price_amount`/`price_currency` pair Eloquent actually persists.

**Proving the round-trip requires a fresh model instance.** Setting
`$product->price = new Money(...)` and reading `$product->price` straight back only
proves the value survived in memory — `get()` never ran, because Eloquent doesn't
re-derive an attribute it already has cached from the setter. The demo route deliberately
re-fetches the model (`Product::findOrFail($product->id)`) after saving, so `get()` is
forced to rebuild the `Money` object from what's actually in the database.

**The value object doesn't serialize itself.** `Money` isn't `Arrayable`/`JsonSerializable`,
so returning it directly in a JSON response would fail. The controller calls `->format()`
and reads its public properties explicitly rather than teaching the value object about
HTTP response shapes — keeps `Money` a plain domain object instead of coupling it to a
serialization format it may not always need.

## Testing

- `sail artisan test --filter=CustomCastsDemoTest` — flag on/off gating, and the round-trip
  asserting both the JSON response and the raw `custom_casts_products` row.
- Manually: toggle "Custom Casts & value objects" on from `/concepts`, then hit
  `/concepts/custom-casts/demo` — the response's `price.formatted` should read
  `"USD 19.99"`.

## Notes

Mass assignment is only fillable on the virtual key: `$fillable` lists `price`, not
`price_amount`/`price_currency` — those columns only exist as the cast's storage detail
and should never be settable directly through `Product::create()`.
